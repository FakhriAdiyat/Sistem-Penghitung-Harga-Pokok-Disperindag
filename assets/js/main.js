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
