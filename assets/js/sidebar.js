document.addEventListener("DOMContentLoaded", function () {
  const toggleBtn = document.getElementById("toggleSidebar");

  toggleBtn.addEventListener("click", () => {
    document.body.classList.toggle("sidebar-open");
  });
});
