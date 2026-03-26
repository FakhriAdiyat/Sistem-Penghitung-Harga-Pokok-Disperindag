console.log("MAIN JS OK");

function confirmLogout(e) {
  if (e && e.preventDefault) e.preventDefault();
  var url = e && e.currentTarget ? e.currentTarget.getAttribute("href") : "";
  if (!url) return false;
  if (typeof openConfirmModal !== "function") {
    if (confirm("Apakah Anda yakin ingin logout?")) window.location.href = url;
    return false;
  }
  openConfirmModal({
    title: "Logout",
    message: "Apakah Anda yakin ingin logout?",
    confirmText: "Logout",
    cancelText: "Batal",
    danger: false,
  }).then(function (ok) {
    if (ok) window.location.href = url;
  });
  return false;
}

function showSessionTimeoutToast() {
  const toast = document.getElementById("toastTimeout");
  if (!toast) return;

  setTimeout(() => toast.classList.add("hide"), 4000);
}

document.addEventListener("DOMContentLoaded", showSessionTimeoutToast);

document.addEventListener("DOMContentLoaded", function () {
  const toggle = document.getElementById("togglePassword");
  const password = document.getElementById("password");

  if (toggle && password) {
    toggle.addEventListener("click", function () {
      const type =
        password.getAttribute("type") === "password" ? "text" : "password";
      password.setAttribute("type", type);

      const icon = document.getElementById("iconEye");

      if (type === "password") {
        icon.innerHTML = `
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
        <circle cx="12" cy="12" r="3"/>
    `;
      } else {
        icon.innerHTML = `
        <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20C5 20 1 12 1 12a21.77 21.77 0 0 1 5.06-6.94"/>
        <path d="M1 1l22 22"/>
    `;
      }
    });
  }
});
