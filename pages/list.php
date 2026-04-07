<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

function listPageNormalizeMonth($value, $fallback)
{
    $value = is_string($value) ? trim($value) : '';
    if (!preg_match('/^\d{4}-\d{2}$/', $value)) {
        return $fallback;
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d', $value . '-01');

    return $date ? $date->format('Y-m') : $fallback;
}

function listPageFormatRupiah($value)
{
    if ($value === null || $value === '') {
        return '-';
    }

    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}

function listPageFormatNumber($value, $decimals = 2, $suffix = '')
{
    if ($value === null || $value === '') {
        return '-';
    }

    return number_format((float) $value, $decimals, ',', '.') . $suffix;
}

function listPageFormatMetricValue($metricKey, $value)
{
    if ($value === null || $value === '') {
        return '-';
    }

    if ($metricKey === 'persen_naik_turun') {
        $numericValue = (float) $value;
        if ($numericValue > 0) {
            return '+' . listPageFormatNumber(abs($numericValue), 2, ' %');
        }

        if ($numericValue < 0) {
            return '-' . listPageFormatNumber(abs($numericValue), 2, ' %');
        }

        return '+/- ' . listPageFormatNumber(0, 2, ' %');
    }

    if ($metricKey === 'naik_turun_rp') {
        $numericValue = (float) $value;
        if ($numericValue > 0) {
            return '+ ' . listPageFormatRupiah(abs($numericValue));
        }

        if ($numericValue < 0) {
            return '- ' . listPageFormatRupiah(abs($numericValue));
        }

        return '+/- ' . listPageFormatRupiah(0);
    }

    return listPageFormatRupiah($value);
}

function listPageMetricValueClass($metricKey, $value)
{
    if ($value === null || $value === '') {
        return 'is-empty';
    }

    if (in_array($metricKey, ['persen_naik_turun', 'naik_turun_rp'], true)) {
        if ((float) $value > 0) {
            return 'is-up';
        }

        if ((float) $value < 0) {
            return 'is-down';
        }

        return 'is-stable';
    }

    return '';
}

function listPageApplyDailyComparisons(array $commodity, array $dateColumns)
{
    $previousPrice = null;

    foreach ($dateColumns as $column) {
        $dateKey = $column['key'];
        if (!isset($commodity['metrics'][$dateKey])) {
            continue;
        }

        $currentPrice = $commodity['metrics'][$dateKey]['harga'] ?? null;
        if ($currentPrice === null || $currentPrice === '') {
            continue;
        }

        $commodity['metrics'][$dateKey]['persen_naik_turun'] = null;
        $commodity['metrics'][$dateKey]['naik_turun_rp'] = null;

        if ($previousPrice !== null) {
            $delta = (float) $currentPrice - (float) $previousPrice;
            $commodity['metrics'][$dateKey]['naik_turun_rp'] = $delta;

            if ((float) $previousPrice != 0.0) {
                $commodity['metrics'][$dateKey]['persen_naik_turun'] = round(($delta / (float) $previousPrice) * 100, 2);
            } elseif ((float) $delta === 0.0) {
                $commodity['metrics'][$dateKey]['persen_naik_turun'] = 0.0;
            }
        }

        $previousPrice = (float) $currentPrice;
    }

    return $commodity;
}

function listPageMetricRows()
{
    static $rows = null;

    if ($rows === null) {
        $rows = [
            ['key' => 'harga', 'label' => 'Harga'],
            ['key' => 'het_hap', 'label' => 'HET / HAP'],
            ['key' => 'persen_naik_turun', 'label' => 'Naik / Turun (%)'],
            ['key' => 'naik_turun_rp', 'label' => 'Naik / Turun (Rp)'],
        ];
    }

    return $rows;
}

function listPageCommodityInitials($name)
{
    $words = preg_split('/[\s\-,()\/]+/', trim((string) $name)) ?: [];
    $initials = '';

    foreach ($words as $word) {
        $word = trim($word);
        if ($word === '') {
            continue;
        }

        $initials .= strtoupper(substr($word, 0, 1));
        if (strlen($initials) >= 2) {
            break;
        }
    }

    if ($initials === '') {
        return 'KP';
    }

    return $initials;
}

function listPageCommodityTone($name)
{
    $tones = ['lime', 'sky', 'amber', 'rose', 'emerald', 'violet'];

    return $tones[abs(crc32((string) $name)) % count($tones)];
}

function listPageBuildUrl(array $overrides = [])
{
    $params = $_GET;

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
            continue;
        }

        $params[$key] = $value;
    }

    $query = http_build_query($params);

    return $query !== '' ? ('?' . $query) : '?';
}

function listPageCommodityCatalog()
{
    static $catalog = null;

    if ($catalog === null) {
        $catalog = [
            'Beras Premium',
            'Beras Medium',
            'Beras Termurah',
            'Beras ketan Putih',
            'Bawang Merah',
            'Bawang Putih Honan',
            'Bawang Putih Kating',
            'Bawang Putih Bombai',
            'Buah Jeruk',
            'Buah Pisang',
            'Cabai Keriting',
            'Cabai Merah Biasa',
            'Cabai Hijau Biasa',
            'Cabe Rawit Merah',
            'Cabe Rawit Hijau',
            'Daging Sapi murni Paha Depan',
            'Daging Sapi murni Paha Belakang',
            'Daging Sapi Tetelan',
            'Daging Sapi Sirloin (Has Luar)',
            'Daging Sapi Tanderloin (Has Dalam)',
            'Daging Sapi Sandung Lemur',
            'Daging Sapi Beku',
            'Daging Ayam Broiler',
            'Daging Ayam Kampung',
            'Gula Pasir Kristal (Curah)',
            'Gula Kemasan',
            'Gula Merah',
            'Garam Bata',
            'Garam Halus',
            'Ikan Asin Medan',
            'Ikan Asin Kering',
            'Ikan Bandeng Besar',
            'Ikan kembung Besar',
            'Ikan Tongkol',
            'Ikan Tuna Sedang',
            'Ikan Cakalang',
            'Ikan Mas',
            'Ikan Udang',
            'Jagung Pipilan',
            'Jagung Manis',
            'Kacang Kedelai Eks Import',
            'Kacang Kedelai Lokal',
            'Kacang Hijau',
            'Kacang Tanah',
            'Kangkung',
            'Ketela Pohon',
            'Kentang',
            'Kol/Kubis',
            'Mie Instan',
            'Minyak Goreng Curah',
            'Minyak Goreng Minyakita',
            'Minyak Goreng Sanco',
            'Sawi Hijau',
            'Susu Bubuk Dencow',
            'Susu Bubuk SGM',
            'Susu Kental Manis Bendera',
            'Susu Kental Manis Indomilk',
            'Telur Ayam Ras',
            'Telur Ayam Kampung',
            'Tepung Terigu Curah',
            'Tepung Terigu Segitiga Biru',
            'Tepung Terigu Cakra Kembar',
            'Tepung Terigu Kunci',
            'Tahu',
            'Tempe',
            'Timun',
            'Tomat Merah',
            'Tomat Hijau',
            'Ubi Jalar Putih',
        ];
    }

    return $catalog;
}

function listPageCommodityAliases()
{
    static $aliases = null;

    if ($aliases === null) {
        $aliases = [
            'Beras Ketan Putih' => 'Beras ketan Putih',
            'Buah-Buahan Jeruk' => 'Buah Jeruk',
            'Buah-Buahan Pisang' => 'Buah Pisang',
            'Cabai Biasa Merah' => 'Cabai Merah Biasa',
            'Cabai Biasa Hijau' => 'Cabai Hijau Biasa',
            'Daging Ayam Ayam Broiler' => 'Daging Ayam Broiler',
            'Daging Ayam Ayam Kampung' => 'Daging Ayam Kampung',
            'Daging Sapi Sapi murni Paha Depan' => 'Daging Sapi murni Paha Depan',
            'Daging Sapi Sapi murni Paha Belakang' => 'Daging Sapi murni Paha Belakang',
            'Daging Sapi Sapi Tetelan' => 'Daging Sapi Tetelan',
            'Daging Sapi Sapi Sirloin (Has Luar)' => 'Daging Sapi Sirloin (Has Luar)',
            'Daging Sapi Sapi Tanderloin (Has Dalam)' => 'Daging Sapi Tanderloin (Has Dalam)',
            'Daging Sapi Sapi Sandung Lemur' => 'Daging Sapi Sandung Lemur',
            'Garam Beriodium Bata' => 'Garam Bata',
            'Garam Beriodium Halus (250gr)' => 'Garam Halus',
            'Ikan Teri Asin Medan' => 'Ikan Asin Medan',
            'Ikan Teri Asin Kering' => 'Ikan Asin Kering',
            'Ikan Kembung Besar' => 'Ikan kembung Besar',
            'Ikan Ikan Mas' => 'Ikan Mas',
            'Ikan Udang Sedang' => 'Ikan Udang',
            'Jagung Jagung Manis' => 'Jagung Manis',
            'Jagung Pipilan Kering (bknu/ unggas' => 'Jagung Pipilan',
            'Kacang Kedelai' => 'Kacang Kedelai Lokal',
            'Minyak Goreng Tanpa merk (Curah)' => 'Minyak Goreng Curah',
            'Minyak Goreng Sanco botol 1ltr' => 'Minyak Goreng Sanco',
            'Susu Bubuk Balita SGM' => 'Susu Bubuk SGM',
            'Susu Bubuk Merk Dencow' => 'Susu Bubuk Dencow',
            'Susu Kental Manis Merk Bendera' => 'Susu Kental Manis Bendera',
            'Susu Kental Manis Merk Indomilk' => 'Susu Kental Manis Indomilk',
            'Timun Sedang' => 'Timun',
        ];
    }

    return $aliases;
}

function listPageCommodityTemplateName($name)
{
    $name = trim((string) $name);
    if ($name === '') {
        return '';
    }

    static $catalogIndex = null;
    if ($catalogIndex === null) {
        $catalogIndex = array_fill_keys(listPageCommodityCatalog(), true);
    }

    if (isset($catalogIndex[$name])) {
        return $name;
    }

    $aliases = listPageCommodityAliases();

    return $aliases[$name] ?? $name;
}

function listPageCommodityLookupCandidates($templateName)
{
    $candidates = [$templateName];

    foreach (listPageCommodityAliases() as $dbName => $mappedTemplateName) {
        if ($mappedTemplateName === $templateName) {
            $candidates[] = $dbName;
        }
    }

    return array_values(array_unique($candidates));
}

function listPageCommodityOrder($name)
{
    static $orderMap = null;

    if ($orderMap === null) {
        $orderMap = [];
        foreach (listPageCommodityCatalog() as $index => $label) {
            $orderMap[$label] = $index;
        }
    }

    return $orderMap[$name] ?? 9999;
}

$keyword = trim((string) ($_GET['search'] ?? ''));

$maxTanggal = date('Y-m-d');
$maxDateResult = mysqli_query($conn, "SELECT MAX(tanggal) AS max_tanggal FROM harga");
if ($maxDateResult) {
    $maxDateRow = mysqli_fetch_assoc($maxDateResult);
    if (!empty($maxDateRow['max_tanggal'])) {
        $maxTanggal = $maxDateRow['max_tanggal'];
    }
}

$defaultMonth = substr($maxTanggal, 0, 7);
if (!preg_match('/^\d{4}-\d{2}$/', $defaultMonth)) {
    $defaultMonth = date('Y-m');
}

$startMonth = listPageNormalizeMonth($_GET['start_month'] ?? '', $defaultMonth);
$endMonth = listPageNormalizeMonth($_GET['end_month'] ?? '', $defaultMonth);

$startMonthDate = DateTimeImmutable::createFromFormat('Y-m-d', $startMonth . '-01') ?: new DateTimeImmutable(date('Y-m-01'));
$endMonthDate = DateTimeImmutable::createFromFormat('Y-m-d', $endMonth . '-01') ?: $startMonthDate;

if ($startMonthDate > $endMonthDate) {
    [$startMonthDate, $endMonthDate] = [$endMonthDate, $startMonthDate];
    $startMonth = $startMonthDate->format('Y-m');
    $endMonth = $endMonthDate->format('Y-m');
}

$startDate = $startMonthDate;
$endDate = $endMonthDate->modify('last day of this month');
$startDateSql = $startDate->format('Y-m-d');
$endDateSql = $endDate->format('Y-m-d');

$dateColumns = [];
$cursor = $startDate;
while ($cursor <= $endDate) {
    $dateColumns[] = [
        'key' => $cursor->format('Y-m-d'),
        'label' => $cursor->format('j'),
        'year' => $cursor->format('M Y'),
        'full' => $cursor->format('d M Y'),
    ];
    $cursor = $cursor->modify('+1 day');
}

$rowsPerPageOptions = [10, 25, 50];
$perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
if (!in_array($perPage, $rowsPerPageOptions, true)) {
    $perPage = 10;
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$importTemplateUrl = BASE_URL . 'assets/Format/' . rawurlencode('Format Import Data Bapok.xlsx');
$bahanForImport = mysqli_query($conn, "SELECT id, nama_bahan FROM bahan_pokok ORDER BY nama_bahan ASC");
$bahanList = mysqli_query($conn, "SELECT id, nama_bahan FROM bahan_pokok ORDER BY nama_bahan ASC");
$bahanLookupResult = mysqli_query($conn, "SELECT id, nama_bahan, satuan, het_hap FROM bahan_pokok ORDER BY nama_bahan ASC");

$bahanLookup = [];
if ($bahanLookupResult) {
    while ($bahanLookupRow = mysqli_fetch_assoc($bahanLookupResult)) {
        $bahanLookup[$bahanLookupRow['nama_bahan']] = [
            'id' => (int) $bahanLookupRow['id'],
            'satuan' => $bahanLookupRow['satuan'] ?? '',
            'het_hap' => (!empty($bahanLookupRow['het_hap']) && (float) $bahanLookupRow['het_hap'] > 0) ? (float) $bahanLookupRow['het_hap'] : null,
        ];
    }
}

$returnParams = $_GET;
unset($returnParams['success'], $returnParams['err']);
$returnQuery = http_build_query($returnParams);

$msg = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'tambah') {
        $msg = 'Data berhasil ditambah.';
    } elseif ($_GET['success'] === 'edit') {
        $msg = 'Data berhasil diubah.';
    } elseif ($_GET['success'] === 'hapus') {
        $msg = 'Data berhasil dihapus.';
    }
}
if (isset($_GET['err'])) {
    $msg = 'Terjadi kesalahan. Coba lagi.';
}

$importFlash = null;
if (isset($_SESSION['import_flash']) && is_array($_SESSION['import_flash'])) {
    $importFlash = $_SESSION['import_flash'];
    unset($_SESSION['import_flash']);
}

$cols = mysqli_query($conn, "SHOW COLUMNS FROM harga LIKE 'persen_penyimpangan'");
$pakaiStrukturBaru = ($cols && mysqli_num_rows($cols) > 0);

$commodityMap = [];
foreach (listPageCommodityCatalog() as $templateName) {
    $prefillBahanId = null;
    $prefillSatuan = '';
    $prefillHetHap = null;
    $prefillOriginalName = $templateName;

    foreach (listPageCommodityLookupCandidates($templateName) as $candidateName) {
        if (isset($bahanLookup[$candidateName])) {
            $prefillBahanId = (int) $bahanLookup[$candidateName]['id'];
            $prefillSatuan = $bahanLookup[$candidateName]['satuan'];
            $prefillHetHap = $bahanLookup[$candidateName]['het_hap'];
            $prefillOriginalName = $candidateName;
            break;
        }
    }

    $commodityMap[$templateName] = [
        'bahan_id' => $prefillBahanId,
        'nama_bahan' => $templateName,
        'nama_bahan_asli' => $prefillOriginalName,
        'satuan' => $prefillSatuan,
        'het_hap' => $prefillHetHap,
        'avatar' => listPageCommodityInitials($templateName),
        'tone' => listPageCommodityTone($templateName),
        'template_order' => listPageCommodityOrder($templateName),
        'prices' => [],
        'metrics' => [],
        'price_values' => [],
        'latest_record' => null,
    ];
}

$selectStatFields = $pakaiStrukturBaru
    ? "
        h.rata_rata,
        h.persen_penyimpangan,
        h.fluktuasi_persen,
        h.stabilitas_persen,
        h.persen_naik_turun,
        h.naik_turun_rp
    "
    : "
        h.rata_rata,
        h.rata_penyimpangan,
        h.fluktuasi_persen,
        h.stabilitas_persen,
        h.persen_kenaikan,
        h.persen_penurunan,
        h.kenaikan_rp,
        h.penurunan_rp
    ";

$dataQuery = mysqli_query(
    $conn,
    "
    SELECT
        h.id,
        h.bahan_id,
        h.tanggal,
        h.harga,
        b.nama_bahan,
        b.satuan,
        b.het_hap,
        $selectStatFields
    FROM harga h
    INNER JOIN (
        SELECT bahan_id, tanggal, MAX(id) AS latest_id
        FROM harga
        WHERE tanggal BETWEEN '$startDateSql' AND '$endDateSql'
        GROUP BY bahan_id, tanggal
    ) latest ON latest.latest_id = h.id
    INNER JOIN bahan_pokok b ON b.id = h.bahan_id
    ORDER BY b.id ASC, h.tanggal ASC, h.id ASC
    "
);

if ($dataQuery) {
    while ($row = mysqli_fetch_assoc($dataQuery)) {
        $displayName = listPageCommodityTemplateName($row['nama_bahan']);
        if (
            $keyword !== ''
            && stripos((string) $row['nama_bahan'], $keyword) === false
            && stripos($displayName, $keyword) === false
        ) {
            continue;
        }
        if ($pakaiStrukturBaru) {
            $persenPenyimpangan = $row['persen_penyimpangan'] !== null ? (float) $row['persen_penyimpangan'] : null;
            $persenNaikTurun = $row['persen_naik_turun'] !== null ? (float) $row['persen_naik_turun'] : null;
            $naikTurunRp = $row['naik_turun_rp'] !== null ? (float) $row['naik_turun_rp'] : null;
        } else {
            $persenPenyimpangan = null;
            if (!empty($row['rata_rata']) && (float) $row['rata_rata'] > 0 && isset($row['rata_penyimpangan']) && $row['rata_penyimpangan'] !== null) {
                $persenPenyimpangan = round(((float) $row['rata_penyimpangan'] / (float) $row['rata_rata']) * 100, 2);
            }

            $persenNaikTurun = null;
            $naikTurunRp = null;
            if (!empty($row['persen_kenaikan']) && (float) $row['persen_kenaikan'] > 0) {
                $persenNaikTurun = (float) $row['persen_kenaikan'];
                $naikTurunRp = !empty($row['kenaikan_rp']) ? (float) $row['kenaikan_rp'] : null;
            } elseif (!empty($row['persen_penurunan']) && (float) $row['persen_penurunan'] > 0) {
                $persenNaikTurun = -(float) $row['persen_penurunan'];
                $naikTurunRp = !empty($row['penurunan_rp']) ? -(float) $row['penurunan_rp'] : null;
            }
        }

        if (!isset($commodityMap[$displayName])) {
            continue;
        }

        $commodityMap[$displayName]['bahan_id'] = (int) $row['bahan_id'];
        if ($commodityMap[$displayName]['satuan'] === '' && !empty($row['satuan'])) {
            $commodityMap[$displayName]['satuan'] = $row['satuan'];
        }
        if (!empty($row['het_hap']) && (float) $row['het_hap'] > 0) {
            $commodityMap[$displayName]['het_hap'] = (float) $row['het_hap'];
        }
        $commodityMap[$displayName]['nama_bahan_asli'] = $row['nama_bahan'];
        $commodityMap[$displayName]['prices'][$row['tanggal']] = [
            'value' => (float) $row['harga'],
            'id' => (int) $row['id'],
        ];
        $commodityMap[$displayName]['metrics'][$row['tanggal']] = [
            'harga' => (float) $row['harga'],
            'het_hap' => (!empty($row['het_hap']) && (float) $row['het_hap'] > 0) ? (float) $row['het_hap'] : $commodityMap[$displayName]['het_hap'],
            'persen_naik_turun' => $persenNaikTurun,
            'naik_turun_rp' => $naikTurunRp,
        ];
        $commodityMap[$displayName]['price_values'][] = (float) $row['harga'];
        $commodityMap[$displayName]['latest_record'] = [
            'id' => (int) $row['id'],
            'harga' => (float) $row['harga'],
            'tanggal' => $row['tanggal'],
            'rata_rata' => $row['rata_rata'] !== null ? (float) $row['rata_rata'] : null,
            'persen_penyimpangan' => $persenPenyimpangan,
            'fluktuasi_persen' => $row['fluktuasi_persen'] !== null ? (float) $row['fluktuasi_persen'] : null,
            'stabilitas_persen' => $row['stabilitas_persen'] !== null ? (float) $row['stabilitas_persen'] : null,
            'persen_naik_turun' => $persenNaikTurun,
            'naik_turun_rp' => $naikTurunRp,
            'het_hap' => (!empty($row['het_hap']) && (float) $row['het_hap'] > 0) ? (float) $row['het_hap'] : null,
        ];
    }
}

$commodities = [];
$metricRows = listPageMetricRows();
foreach ($commodityMap as $commodity) {
    if (
        $keyword !== ''
        && stripos((string) ($commodity['nama_bahan'] ?? ''), $keyword) === false
        && stripos((string) ($commodity['nama_bahan_asli'] ?? ''), $keyword) === false
    ) {
        continue;
    }

    $commodity = listPageApplyDailyComparisons($commodity, $dateColumns);

    $priceValues = $commodity['price_values'];
    $summary = [
        'highest' => null,
        'average' => null,
        'lowest' => null,
        'recorded_days' => count($priceValues),
    ];

    if (!empty($priceValues)) {
        $summary['highest'] = max($priceValues);
        $summary['lowest'] = min($priceValues);
        $summary['average'] = round(array_sum($priceValues) / count($priceValues));
    }

    $metricSummaries = [];
    foreach ($metricRows as $metric) {
        $metricKey = $metric['key'];
        $metricValues = [];

        if ($metricKey === 'het_hap') {
            foreach ($commodity['metrics'] as $metricCell) {
                if (isset($metricCell['het_hap']) && $metricCell['het_hap'] !== null && $metricCell['het_hap'] !== '') {
                    $metricValues[] = (float) $metricCell['het_hap'];
                }
            }

            if (empty($metricValues) && isset($commodity['het_hap']) && $commodity['het_hap'] !== null && $commodity['het_hap'] !== '') {
                $metricValues[] = (float) $commodity['het_hap'];
            }
        } else {
            foreach ($commodity['metrics'] as $metricCell) {
                if (isset($metricCell[$metricKey]) && $metricCell[$metricKey] !== null && $metricCell[$metricKey] !== '') {
                    $metricValues[] = (float) $metricCell[$metricKey];
                }
            }
        }

        $metricSummaries[$metricKey] = [
            'highest' => null,
            'average' => null,
            'lowest' => null,
        ];

        if (!empty($metricValues)) {
            $metricSummaries[$metricKey]['highest'] = max($metricValues);
            $metricSummaries[$metricKey]['lowest'] = min($metricValues);
            $metricSummaries[$metricKey]['average'] = $metricKey === 'persen_naik_turun'
                ? round(array_sum($metricValues) / count($metricValues), 2)
                : round(array_sum($metricValues) / count($metricValues));
        }
    }

    unset($commodity['price_values']);
    $commodity['summary'] = $summary;
    $commodity['metric_summaries'] = $metricSummaries;
    $commodities[] = $commodity;
}

usort($commodities, function ($a, $b) {
    $templateOrderCompare = ($a['template_order'] ?? 9999) <=> ($b['template_order'] ?? 9999);
    if ($templateOrderCompare !== 0) {
        return $templateOrderCompare;
    }

    return strcasecmp((string) ($a['nama_bahan'] ?? ''), (string) ($b['nama_bahan'] ?? ''));
});

$totalRows = count($commodities);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;
$visibleRows = array_slice($commodities, $offset, $perPage);
$visibleCount = count($visibleRows);

$exportUrl = '../process/export_process.php?' . http_build_query([
    'start_date' => $startDateSql,
    'end_date' => $endDateSql,
    'search' => $keyword,
]);

$paginationPages = [];
for ($i = max(1, $page - 1); $i <= min($totalPages, $page + 1); $i++) {
    $paginationPages[] = $i;
}
if (!in_array(1, $paginationPages, true)) {
    array_unshift($paginationPages, 1);
}
if (!in_array($totalPages, $paginationPages, true)) {
    $paginationPages[] = $totalPages;
}
$paginationPages = array_values(array_unique($paginationPages));
sort($paginationPages);

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
?>

<div class="layout">

<?php require_once '../includes/sidebar.php'; ?>

<div style="flex:1; display:flex; flex-direction:column;">

<?php require_once '../includes/header.php'; ?>

<div class="content">
<div class="container list-page-container">

<div class="list-page-header">
    <div>
        <h1>List Data Harga</h1>
        <p class="subtitle">Ringkasan harga harian per komoditas untuk periode yang dipilih.</p>
    </div>
    <div class="list-period-badge">
        <span>Periode</span>
        <strong><?= htmlspecialchars($startDate->format('d M Y')) ?> - <?= htmlspecialchars($endDate->format('d M Y')) ?></strong>
    </div>
</div>

<?php if ($msg): ?>
<p class="list-flash-msg <?= isset($_GET['err']) ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<?php if ($importFlash): ?>
    <?php
        $type = ($importFlash['type'] ?? 'success') === 'error' ? 'error' : 'success';
        $title = (string) ($importFlash['title'] ?? '');
        $message = (string) ($importFlash['message'] ?? '');
    ?>
    <div class="popup-overlay" role="alert" aria-live="assertive">
        <div class="popup-card <?= $type === 'success' ? 'popup-card-success' : 'popup-card-error' ?>" role="document">
            <div class="popup-icon <?= $type === 'success' ? 'popup-icon-success' : 'popup-icon-error' ?>" aria-hidden="true">
                <?php if ($type === 'success'): ?>
                    <span class="popup-check popup-check-short"></span>
                    <span class="popup-check popup-check-long"></span>
                <?php else: ?>
                    <span class="popup-x popup-x-left"></span>
                    <span class="popup-x popup-x-right"></span>
                <?php endif; ?>
            </div>
            <div class="popup-title"><?= htmlspecialchars($title) ?></div>
            <div class="popup-message"><?= htmlspecialchars($message) ?></div>
            <button type="button" class="popup-close" onclick="closePopup()">Tutup</button>
        </div>
    </div>
<?php endif; ?>

<div class="list-filter-shell">
    <form method="GET" class="list-filter-form">
        <div class="list-search-field">
            <span class="list-search-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="M20 20l-3.5-3.5"></path>
                </svg>
            </span>
            <input type="text" name="search" placeholder="Cari Komoditas" value="<?= htmlspecialchars($keyword) ?>">
        </div>

        <label class="list-filter-field">
            <span>Bulan Awal</span>
            <input type="month" name="start_month" value="<?= htmlspecialchars($startMonth) ?>">
        </label>

        <label class="list-filter-field">
            <span>Bulan Akhir</span>
            <input type="month" name="end_month" value="<?= htmlspecialchars($endMonth) ?>">
        </label>

        <input type="hidden" name="page" value="1">
        <input type="hidden" name="per_page" value="<?= (int) $perPage ?>">

        <button type="submit" class="list-apply-btn">Terapkan</button>
        <button type="button" id="btnImportPopup" class="list-import-btn">Import</button>
        <a href="<?= htmlspecialchars($exportUrl) ?>" class="list-export-btn" target="_blank" rel="noopener">Export</a>
    </form>
</div>

<div class="list-table-card">
    <div class="list-table-scroll">
        <table class="list-pivot-table">
            <thead>
                <tr>
                    <th class="list-sticky-no">No</th>
                    <th class="list-sticky-commodity">Komoditas</th>
                    <th class="list-sticky-metric">Data</th>
                    <?php foreach ($dateColumns as $column): ?>
                    <th class="list-date-head" title="<?= htmlspecialchars($column['full']) ?>">
                        <span><?= htmlspecialchars($column['label']) ?></span>
                        <small><?= htmlspecialchars($column['year']) ?></small>
                    </th>
                    <?php endforeach; ?>
                    <th class="list-summary-head">Tertinggi</th>
                    <th class="list-summary-head">Rata-rata</th>
                    <th class="list-summary-head">Terendah</th>
                </tr>
            </thead>
            <?php if (empty($visibleRows)): ?>
            <tbody>
                <tr>
                    <td colspan="<?= count($dateColumns) + 6 ?>" class="list-empty-cell">
                        <div class="list-empty-state">
                            <strong>Tidak ada data untuk ditampilkan</strong>
                            <span>Coba ubah kata kunci pencarian atau rentang bulan.</span>
                        </div>
                    </td>
                </tr>
            </tbody>
            <?php else: ?>
                    <?php foreach ($visibleRows as $index => $commodity): ?>
                    <?php
                        $latestRecord = $commodity['latest_record'];
                        $groupRowId = 'commodity-group-' . ($offset + $index + 1);
                    ?>
                    <tbody class="list-commodity-group is-collapsed" data-commodity-group id="<?= htmlspecialchars($groupRowId) ?>">
                        <tr
                            class="list-commodity-row list-data-row"
                            data-bahan-id="<?= (int) $commodity['bahan_id'] ?>"
                            data-bahan-nama="<?= htmlspecialchars($commodity['nama_bahan']) ?>"
                            data-latest-id="<?= (int) ($latestRecord['id'] ?? 0) ?>"
                            data-latest-harga="<?= htmlspecialchars((string) ($latestRecord['harga'] ?? '')) ?>"
                            data-latest-tanggal="<?= htmlspecialchars((string) ($latestRecord['tanggal'] ?? '')) ?>"
                        >
                            <td class="list-sticky-no list-group-anchor"><?= $offset + $index + 1 ?></td>
                            <td class="list-sticky-commodity list-group-anchor">
                                <button
                                    type="button"
                                    class="list-commodity-toggle"
                                    aria-expanded="false"
                                    aria-controls="<?= htmlspecialchars($groupRowId) ?>"
                                >
                                    <span class="list-commodity-cell">
                                        <span class="list-commodity-avatar tone-<?= htmlspecialchars($commodity['tone']) ?>">
                                            <?= htmlspecialchars($commodity['avatar']) ?>
                                        </span>
                                        <span class="list-commodity-meta">
                                            <strong><?= htmlspecialchars($commodity['nama_bahan']) ?></strong>
                                            <span>
                                                <?= (int) $commodity['summary']['recorded_days'] ?> hari tercatat
                                                <?php if (!empty($commodity['satuan'])): ?>
                                                    | <?= htmlspecialchars($commodity['satuan']) ?>
                                                <?php endif; ?>
                                            </span>
                                            <span class="list-commodity-note">
                                                Klik untuk tampilkan atau sembunyikan data harian per bulan.
                                            </span>
                                        </span>
                                    </span>
                                    <span class="list-commodity-toggle-side">
                                        <span class="list-commodity-toggle-arrow" aria-hidden="true"></span>
                                    </span>
                                </button>
                            </td>
                            <td class="list-sticky-metric list-metric-label list-primary-metric-label">
                                <span class="list-metric-chip">
                                    <strong>Harga</strong>
                                    <small><?= htmlspecialchars(!empty($commodity['satuan']) ? $commodity['satuan'] : '-') ?></small>
                                </span>
                            </td>
                            <?php foreach ($dateColumns as $column): ?>
                                <?php
                                    $priceCell = $commodity['prices'][$column['key']] ?? null;
                                    $priceValue = $priceCell['value'] ?? null;
                                ?>
                                <td class="list-metric-value harga<?= $priceValue === null ? ' is-empty' : '' ?>">
                                    <?= htmlspecialchars(listPageFormatRupiah($priceValue)) ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="list-summary-cell harga<?= $commodity['summary']['highest'] === null ? ' is-empty' : '' ?>">
                                <?= htmlspecialchars(listPageFormatRupiah($commodity['summary']['highest'])) ?>
                            </td>
                            <td class="list-summary-cell harga<?= $commodity['summary']['average'] === null ? ' is-empty' : '' ?>">
                                <?= htmlspecialchars(listPageFormatRupiah($commodity['summary']['average'])) ?>
                            </td>
                            <td class="list-summary-cell harga<?= $commodity['summary']['lowest'] === null ? ' is-empty' : '' ?>">
                                <?= htmlspecialchars(listPageFormatRupiah($commodity['summary']['lowest'])) ?>
                            </td>
                        </tr>
                        <?php foreach ($metricRows as $metric): ?>
                        <?php if ($metric['key'] === 'harga') { continue; } ?>
                        <tr class="list-metric-row" data-metric-row aria-hidden="true">
                            <td class="list-sticky-no list-metric-spacer" aria-hidden="true"></td>
                            <td class="list-sticky-commodity list-metric-spacer" aria-hidden="true"></td>
                            <td class="list-sticky-metric list-metric-label">
                                <span><?= htmlspecialchars($metric['label']) ?></span>
                            </td>

                            <?php foreach ($dateColumns as $column): ?>
                                <?php
                                    $metricCell = $commodity['metrics'][$column['key']] ?? [];
                                    if ($metric['key'] === 'het_hap') {
                                        $metricValue = $metricCell['het_hap'] ?? ($commodity['het_hap'] ?? null);
                                    } else {
                                        $metricValue = $metricCell[$metric['key']] ?? null;
                                    }
                                    $metricClass = listPageMetricValueClass($metric['key'], $metricValue);
                                ?>
                                <td class="list-metric-value <?= htmlspecialchars($metric['key']) ?><?= $metricClass !== '' ? ' ' . htmlspecialchars($metricClass) : '' ?>">
                                    <?= htmlspecialchars(listPageFormatMetricValue($metric['key'], $metricValue)) ?>
                                </td>
                            <?php endforeach; ?>
                            <?php
                                $metricSummary = $commodity['metric_summaries'][$metric['key']] ?? ['highest' => null, 'average' => null, 'lowest' => null];
                                $summaryKeys = ['highest', 'average', 'lowest'];
                            ?>
                            <?php foreach ($summaryKeys as $summaryKey): ?>
                                <?php
                                    $summaryValue = $metricSummary[$summaryKey] ?? null;
                                    $summaryClass = listPageMetricValueClass($metric['key'], $summaryValue);
                                ?>
                                <td class="list-summary-cell <?= htmlspecialchars($metric['key']) ?><?= $summaryClass !== '' ? ' ' . htmlspecialchars($summaryClass) : '' ?>">
                                    <?= htmlspecialchars(listPageFormatMetricValue($metric['key'], $summaryValue)) ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>

    <div class="list-table-footer">
        <p>Menampilkan <?= (int) $visibleCount ?> dari <?= (int) $totalRows ?> komoditas</p>

        <div class="list-table-footer-actions">
            <form method="GET" class="list-page-size-form">
                <input type="hidden" name="search" value="<?= htmlspecialchars($keyword) ?>">
                <input type="hidden" name="start_month" value="<?= htmlspecialchars($startMonth) ?>">
                <input type="hidden" name="end_month" value="<?= htmlspecialchars($endMonth) ?>">
                <input type="hidden" name="page" value="1">
                <label for="perPage">Rows</label>
                <select id="perPage" name="per_page" onchange="this.form.submit()">
                    <?php foreach ($rowsPerPageOptions as $option): ?>
                    <option value="<?= (int) $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= (int) $option ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <div class="list-pagination">
                <a href="<?= htmlspecialchars(listPageBuildUrl(['page' => 1, 'per_page' => $perPage])) ?>" class="list-page-btn<?= $page === 1 ? ' is-disabled' : '' ?>" aria-label="Halaman pertama">|&lt;</a>
                <a href="<?= htmlspecialchars(listPageBuildUrl(['page' => max(1, $page - 1), 'per_page' => $perPage])) ?>" class="list-page-btn<?= $page === 1 ? ' is-disabled' : '' ?>" aria-label="Halaman sebelumnya">&lt;</a>

                <?php foreach ($paginationPages as $paginationPage): ?>
                <a href="<?= htmlspecialchars(listPageBuildUrl(['page' => $paginationPage, 'per_page' => $perPage])) ?>" class="list-page-number<?= $paginationPage === $page ? ' is-active' : '' ?>">
                    <?= (int) $paginationPage ?>
                </a>
                <?php endforeach; ?>

                <a href="<?= htmlspecialchars(listPageBuildUrl(['page' => min($totalPages, $page + 1), 'per_page' => $perPage])) ?>" class="list-page-btn<?= $page === $totalPages ? ' is-disabled' : '' ?>" aria-label="Halaman berikutnya">&gt;</a>
                <a href="<?= htmlspecialchars(listPageBuildUrl(['page' => $totalPages, 'per_page' => $perPage])) ?>" class="list-page-btn<?= $page === $totalPages ? ' is-disabled' : '' ?>" aria-label="Halaman terakhir">&gt;|</a>
            </div>
        </div>
    </div>
</div>

<div id="rowActionPopup" class="row-action-popup" aria-hidden="true">
    <button type="button" class="row-action-btn row-action-tambah" data-action="tambah">Tambah</button>
    <button type="button" class="row-action-btn row-action-edit" data-action="edit">Edit</button>
</div>

<!-- Modal Import -->
<div id="modalImport" class="list-modal" role="dialog" aria-hidden="true">
  <div class="list-modal-backdrop"></div>
  <div class="list-modal-box import-modal-box">
    <div class="import-modal-header">
      <h3>Import Data Harga</h3>
    </div>
    <form action="../process/import_process.php" method="post" enctype="multipart/form-data" class="import-modal-form">
      <input type="hidden" name="redirect_to" value="list">
      <div class="import-template-box">
        <div class="import-template-copy">
          <p>Silakan unduh format data bapok sebelum melakukan import.</p>
        </div>
        <a href="<?= htmlspecialchars($importTemplateUrl) ?>" class="import-template-download" download>
          Download Format
        </a>
      </div>
      <div class="import-modal-body">
        <div class="form-group">
          <label>Pilih File (Excel / CSV / PDF)</label>
          <input type="file" name="file_import" accept=".csv,.xlsx,.xls,.ods,.pdf" required>
          <p class="import-modal-hint">Format yang didukung: CSV, XLSX, XLS, ODS, dan PDF.</p>
        </div>

        <div class="form-group">
          <label>Pilih Bahan (Opsional - Untuk Update HET)</label>
          <select name="bahan_keyword">
            <option value="">-- Tidak Update HET --</option>
            <?php while ($bi = mysqli_fetch_assoc($bahanForImport)): ?>
            <option value="<?= htmlspecialchars($bi['nama_bahan']) ?>"><?= htmlspecialchars($bi['nama_bahan']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Masukkan HET/HAP (Opsional)</label>
          <input type="number" name="het_hap" step="0.01" placeholder="Masukkan HET/HAP">
        </div>
      </div>

      <div class="list-modal-actions import-modal-actions">
        <button type="submit" class="btn-save">Import Data</button>
        <button type="button" class="btn-cancel-modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Tambah -->
<div id="modalTambah" class="list-modal" role="dialog" aria-hidden="true">
    <div class="list-modal-backdrop"></div>
    <div class="list-modal-box list-entry-modal-box">
        <h3 class="list-entry-modal-title">Tambah Data Harga</h3>
        <p class="list-entry-modal-subtitle">Tambahkan data harga untuk komoditas yang dipilih.</p>
        <form method="post" action="../process/list_process.php" class="list-entry-modal-form">
            <input type="hidden" name="action" value="tambah">
            <input type="hidden" name="return_query" value="<?= htmlspecialchars($returnQuery) ?>">
            <div class="form-group">
                <label>Bahan</label>
                <select name="bahan_id" id="addBahanSelect" required>
                    <option value="">-- Pilih Bahan --</option>
                    <?php while ($b = mysqli_fetch_assoc($bahanList)): ?>
                    <option value="<?= (int) $b['id'] ?>"><?= htmlspecialchars($b['nama_bahan']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="harga" id="addHarga" min="0" step="100" required placeholder="Contoh: 14000">
            </div>
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" id="addTanggal" value="<?= htmlspecialchars($endDate->format('Y-m-d')) ?>" required>
            </div>
            <div class="list-modal-actions list-entry-modal-actions">
                <button type="submit" class="btn-save list-entry-modal-submit">Simpan</button>
                <button type="button" class="btn-cancel-modal list-entry-modal-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="list-modal" role="dialog" aria-hidden="true">
    <div class="list-modal-backdrop"></div>
    <div class="list-modal-box list-entry-modal-box">
        <h3 class="list-entry-modal-title">Edit Data Harga</h3>
        <p class="list-entry-modal-subtitle">Perbarui data harga terakhir untuk komoditas yang dipilih.</p>
        <form method="post" action="../process/list_process.php" class="list-entry-modal-form">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="return_query" value="<?= htmlspecialchars($returnQuery) ?>">
            <input type="hidden" name="id" id="editId">
            <div class="form-group">
                <label>Bahan</label>
                <input type="text" id="editBahanNama" readonly class="form-readonly">
            </div>
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="harga" id="editHarga" min="0" step="100" required>
            </div>
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" id="editTanggal" required>
            </div>
            <div class="list-modal-actions list-entry-modal-actions">
                <button type="submit" class="btn-save list-entry-modal-submit">Simpan</button>
                <button type="button" class="btn-cancel-modal list-entry-modal-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

</div>
</div>
</div>
</div>

<script src="<?= BASE_URL ?>assets/js/list.js"></script>
<?php require_once '../includes/footer.php'; ?>
