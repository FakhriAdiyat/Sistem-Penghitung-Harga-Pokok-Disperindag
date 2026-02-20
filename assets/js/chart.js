document.addEventListener("DOMContentLoaded", function () {
  const ctx = document.getElementById("dashboardchart");

  if (!ctx) return;

  new Chart(ctx, {
    type: "line",
    data: {
      labels: grafikLabels,
      datasets: [
        {
          label: "Rata-rata Harga Bahan Pokok",
          data: grafikData,
          borderWidth: 2,
          tension: 0.4,
          fill: false,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true,
        },
      },
      scales: {
        y: {
          beginAtZero: false,
        },
      },
    },
  });
});
