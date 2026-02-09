const ctx = document.getElementById("grafikHarga");

new Chart(ctx, {
  type: "bar",
  data: {
    labels: ["Naik", "Turun", "Stabil"],
    datasets: [
      {
        label: "Jumlah Bahan",
        data: chartData,
        backgroundColor: [
          "rgba(255, 99, 132, 0.7)",
          "rgba(75, 192, 192, 0.7)",
          "rgba(255, 205, 86, 0.7)",
        ],
      },
    ],
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true },
    },
  },
});
