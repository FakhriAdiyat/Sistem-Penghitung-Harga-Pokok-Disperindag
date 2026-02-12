const toggleBtn = document.getElementById("toggleSidebar");
const sidebar = document.querySelector(".sidebar");
const content = document.querySelector(".content");
const header = document.querySelector(".header");

toggleBtn.addEventListener("click", () => {
  sidebar.classList.toggle("collapsed");
  content.classList.toggle("full");
  header.classList.toggle("full");
});

// Klik di luar sidebar untuk menutup
document.addEventListener("click", function (e) {
  if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
    sidebar.classList.add("collapsed");
    content.classList.add("full");
    header.classList.add("full");
  }
});
