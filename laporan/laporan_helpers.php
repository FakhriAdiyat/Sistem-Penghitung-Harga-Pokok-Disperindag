<?php

function laporanGetLatestTanggal(mysqli $conn): string
{
    $fallback = date('Y-m-d');
    $result = mysqli_query($conn, "SELECT MAX(tanggal) AS max_tanggal FROM harga");

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $maxTanggal = $row['max_tanggal'] ?? '';
        if (is_string($maxTanggal) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $maxTanggal)) {
            return $maxTanggal;
        }
    }

    return $fallback;
}

function laporanNormalizeTanggalInput($input, ?string $fallback = null): string
{
    $fallback = $fallback ?: date('Y-m-d');
    $input = is_string($input) ? trim($input) : '';
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $input);

    if ($date && $date->format('Y-m-d') === $input) {
        return $date->format('Y-m-d');
    }

    return $fallback;
}

function laporanFormatTanggalIndonesia($date): string
{
    if (!$date instanceof DateTimeInterface) {
        $date = new DateTimeImmutable((string) $date);
    }

    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    return (int) $date->format('j') . ' ' . $bulan[(int) $date->format('n')] . ' ' . $date->format('Y');
}

function laporanNormalizeCommodityText($value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    $value = str_replace(['(', ')', '/', '-', '.', ','], ' ', $value);
    $value = str_replace(['tenderloin', ' 250gr', '1ltr', 'lt r'], ['tanderloin', ' 250 gr', '1 ltr', 'ltr'], $value);
    $value = preg_replace('/\s+/u', ' ', $value);

    return trim((string) $value);
}

function laporanTemplateDefinitions(): array
{
    static $definitions = null;

    if ($definitions === null) {
        $definitions = [
            ['row' => 18, 'aliases' => ['Beras Premium']],
            ['row' => 19, 'aliases' => ['Beras Medium']],
            ['row' => 20, 'aliases' => ['Beras Termurah']],
            ['row' => 21, 'aliases' => ['Beras Ketan Putih']],
            ['row' => 23, 'aliases' => ['Gula Pasir Kristal (Curah)']],
            ['row' => 24, 'aliases' => ['Gula Kemasan']],
            ['row' => 25, 'aliases' => ['Gula Merah']],
            ['row' => 27, 'aliases' => ['Minyak Goreng Sanco botol 1ltr', 'Minyak Goreng Sanco']],
            ['row' => 28, 'aliases' => ['Minyak Goreng Tanpa merk (Curah)', 'Minyak Goreng Curah']],
            ['row' => 29, 'aliases' => ['Minyak Goreng Minyakita']],
            ['row' => 31, 'aliases' => ['Tepung Terigu Curah']],
            ['row' => 32, 'aliases' => ['Tepung Terigu Segitiga Biru']],
            ['row' => 33, 'aliases' => ['Tepung Terigu Cakra Kembar']],
            ['row' => 34, 'aliases' => ['Tepung Terigu Kunci']],
            ['row' => 36, 'aliases' => ['Daging Sapi Sapi murni Paha Depan', 'Daging Sapi murni Paha Depan', 'Daging Sapi Paha Depan']],
            ['row' => 37, 'aliases' => ['Daging Sapi Sapi murni Paha Belakang', 'Daging Sapi murni Paha Belakang', 'Daging Sapi Paha Belakang']],
            ['row' => 38, 'aliases' => ['Daging Sapi Sapi Tetelan', 'Daging Sapi Tetelan']],
            ['row' => 39, 'aliases' => ['Daging Sapi Sapi Sirloin (Has Luar)', 'Daging Sapi Sirloin (Has Luar)']],
            ['row' => 40, 'aliases' => ['Daging Sapi Sapi Tanderloin (Has Dalam)', 'Daging Sapi Tanderloin (Has Dalam)', 'Daging Sapi Tenderloin (Has Dalam)']],
            ['row' => 41, 'aliases' => ['Daging Sapi Sapi Sandung Lemur', 'Daging Sapi Sandung Lemur']],
            ['row' => 42, 'aliases' => ['Daging Sapi Beku']],
            ['row' => 44, 'aliases' => ['Daging Ayam Ayam Broiler', 'Daging Ayam Broiler']],
            ['row' => 45, 'aliases' => ['Daging Ayam Ayam Kampung', 'Daging Ayam Kampung']],
            ['row' => 47, 'aliases' => ['Telur Ayam Ras']],
            ['row' => 48, 'aliases' => ['Telur Ayam Kampung']],
            ['row' => 50, 'aliases' => ['Cabai Keriting']],
            ['row' => 51, 'aliases' => ['Cabai Biasa Merah', 'Cabai Merah Biasa']],
            ['row' => 52, 'aliases' => ['Cabai Biasa Hijau', 'Cabai Hijau Biasa']],
            ['row' => 54, 'aliases' => ['Cabe Rawit Hijau']],
            ['row' => 55, 'aliases' => ['Cabe Rawit Merah']],
            ['row' => 56, 'aliases' => ['Bawang Merah']],
            ['row' => 58, 'aliases' => ['Bawang Putih Honan']],
            ['row' => 59, 'aliases' => ['Bawang Putih Kating']],
            ['row' => 60, 'aliases' => ['Bawang Putih Bombai']],
            ['row' => 61, 'aliases' => ['Kol/Kubis']],
            ['row' => 63, 'aliases' => ['Tomat Hijau']],
            ['row' => 64, 'aliases' => ['Tomat Merah']],
            ['row' => 65, 'aliases' => ['Sawi Hijau']],
            ['row' => 66, 'aliases' => ['Kangkung']],
            ['row' => 67, 'aliases' => ['Timun Sedang', 'Timun']],
            ['row' => 69, 'aliases' => ['Susu Kental Manis Merk Bendera', 'Susu Kental Manis Bendera']],
            ['row' => 70, 'aliases' => ['Susu Kental Manis Merk Indomilk', 'Susu Kental Manis Indomilk']],
            ['row' => 72, 'aliases' => ['Susu Bubuk Merk Dencow', 'Susu Bubuk Dencow']],
            ['row' => 73, 'aliases' => ['Susu Bubuk Balita SGM', 'Susu Bubuk SGM']],
            ['row' => 75, 'aliases' => ['Garam Beriodium Bata', 'Garam Bata']],
            ['row' => 76, 'aliases' => ['Garam Beriodium Halus (250gr)', 'Garam Halus']],
            ['row' => 78, 'aliases' => ['Kacang Kedelai Eks Import']],
            ['row' => 79, 'aliases' => ['Kacang Kedelai Lokal', 'Kacang Kedelai']],
            ['row' => 80, 'aliases' => ['Kacang Hijau']],
            ['row' => 81, 'aliases' => ['Kacang Tanah']],
            ['row' => 82, 'aliases' => ['Mie Instan']],
            ['row' => 83, 'aliases' => ['Tahu']],
            ['row' => 84, 'aliases' => ['Tempe']],
            ['row' => 86, 'aliases' => ['Ikan Teri Asin Medan', 'Ikan Asin Medan']],
            ['row' => 87, 'aliases' => ['Ikan Teri Asin Kering', 'Ikan Asin Kering']],
            ['row' => 88, 'aliases' => ['Ikan Bandeng Besar']],
            ['row' => 89, 'aliases' => ['Ikan Kembung Besar', 'Ikan kembung Besar']],
            ['row' => 90, 'aliases' => ['Ikan Tongkol']],
            ['row' => 91, 'aliases' => ['Ikan Tuna Sedang']],
            ['row' => 92, 'aliases' => ['Ikan Cakalang']],
            ['row' => 93, 'aliases' => ['Ikan Udang Sedang', 'Ikan Udang']],
            ['row' => 94, 'aliases' => ['Ikan Ikan Mas', 'Ikan Mas']],
            ['row' => 95, 'aliases' => ['Ketela Pohon']],
            ['row' => 96, 'aliases' => ['Ubi Jalar Putih']],
            ['row' => 97, 'aliases' => ['Kentang']],
            ['row' => 99, 'aliases' => ['Jagung Pipilan Kering (bknu/ unggas', 'Jagung Pipilan Kering', 'Jagung Pipilan']],
            ['row' => 100, 'aliases' => ['Jagung Jagung Manis', 'Jagung Manis']],
            ['row' => 102, 'aliases' => ['Buah-Buahan Pisang', 'Buah Pisang']],
            ['row' => 103, 'aliases' => ['Buah-Buahan Jeruk', 'Buah Jeruk']],
        ];
    }

    return $definitions;
}

function laporanTemplateAliasIndex(): array
{
    static $index = null;

    if ($index === null) {
        $index = [];
        foreach (laporanTemplateDefinitions() as $definition) {
            foreach ($definition['aliases'] as $alias) {
                $index[laporanNormalizeCommodityText($alias)] = (int) $definition['row'];
            }
        }
    }

    return $index;
}

function laporanCommodityCandidates(array $row): array
{
    $nama = trim((string) ($row['nama_bahan'] ?? ''));
    $kategori = trim((string) ($row['kategori'] ?? ''));
    $candidates = [];

    if ($nama !== '' && $kategori !== '' && $kategori !== '-') {
        $candidates[] = $nama . ' ' . $kategori;
    }

    if ($nama !== '') {
        $candidates[] = $nama;
    }

    return array_values(array_unique(array_filter($candidates)));
}

function laporanFindTemplateRow(array $row): ?int
{
    $aliasIndex = laporanTemplateAliasIndex();

    foreach (laporanCommodityCandidates($row) as $candidate) {
        $normalized = laporanNormalizeCommodityText($candidate);
        if (isset($aliasIndex[$normalized])) {
            return (int) $aliasIndex[$normalized];
        }
    }

    return null;
}

function laporanFetchLatestRowsByDate(mysqli $conn, string $tanggal): array
{
    $tanggal = laporanNormalizeTanggalInput($tanggal, date('Y-m-d'));
    $tanggalSafe = mysqli_real_escape_string($conn, $tanggal);
    $rows = [];

    $query = mysqli_query(
        $conn,
        "
        SELECT
            h.id AS harga_id,
            h.tanggal,
            h.harga,
            b.id AS bahan_id,
            b.nama_bahan,
            b.kategori,
            b.satuan,
            b.het_hap
        FROM harga h
        INNER JOIN (
            SELECT bahan_id, MAX(id) AS latest_id
            FROM harga
            WHERE tanggal = '$tanggalSafe'
            GROUP BY bahan_id
        ) latest ON latest.latest_id = h.id
        INNER JOIN bahan_pokok b ON b.id = h.bahan_id
        ORDER BY h.id ASC
        "
    );

    if (!$query) {
        return $rows;
    }

    while ($row = mysqli_fetch_assoc($query)) {
        $rows[] = $row;
    }

    return $rows;
}

function laporanBuildTemplateRowMap(array $rows): array
{
    $mapped = [];

    foreach ($rows as $row) {
        $templateRow = laporanFindTemplateRow($row);
        if ($templateRow === null) {
            continue;
        }

        $hargaId = (int) ($row['harga_id'] ?? 0);
        if (!isset($mapped[$templateRow]) || $hargaId > (int) ($mapped[$templateRow]['harga_id'] ?? 0)) {
            $mapped[$templateRow] = $row;
        }
    }

    return $mapped;
}

function laporanTemplateGroups(): array
{
    static $groups = null;

    if ($groups !== null) {
        return $groups;
    }

    $groups = [];
    $templatePath = __DIR__ . '/../assets/Format/Format Laporan.xlsx';
    if (!is_file($templatePath) || !class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
        return $groups;
    }

    try {
        $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath)->getSheet(0);
        $currentIndex = -1;

        for ($row = 17; $row <= 103; $row++) {
            $groupNumber = trim((string) $sheet->getCell('A' . $row)->getFormattedValue());
            $label = trim((string) $sheet->getCell('B' . $row)->getFormattedValue());
            $unit = trim((string) $sheet->getCell('C' . $row)->getFormattedValue());

            if ($groupNumber !== '' && $label !== '') {
                $groups[] = [
                    'number' => $groupNumber,
                    'title' => $label,
                    'items' => [],
                ];
                $currentIndex = count($groups) - 1;
                continue;
            }

            if ($currentIndex === -1 || $label === '') {
                continue;
            }

            $groups[$currentIndex]['items'][] = [
                'row' => $row,
                'label' => $label,
                'unit' => $unit,
            ];
        }
    } catch (Throwable $e) {
        $groups = [];
    }

    return $groups;
}
