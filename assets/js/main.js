console.log("MAIN JS OK");

function confirmLogout() {
  return confirm("Apakah Anda yakin ingin logout?");
}

function showSessionTimeoutToast() {
  const toast = document.getElementById("toastTimeout");
  if (!toast) return;

  setTimeout(() => toast.classList.add("hide"), 4000);
}

document.addEventListener("DOMContentLoaded", showSessionTimeoutToast);
