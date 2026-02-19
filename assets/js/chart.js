document.addEventListener("DOMContentLoaded", function () {
  const ctx = document.getElementById("dashboardChart");

  if (!ctx) return; // supaya aman kalau bukan di dashboard

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Admin", "Member"],
      datasets: [
        {
          label: "Jumlah Pengguna",
          data: [4, 18], // sementara hardcode dulu
          backgroundColor: ["#16a34a", "#8ee05a"],
          borderRadius: 10,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
});
