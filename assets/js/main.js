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

function togglePassword() {
  const input = document.getElementById("passwordInput");
  const icon = document.getElementById("eyeIcon");

  if (input.type === "password") {
    input.type = "text";

    // icon mata dicoret
    icon.innerHTML = `
            <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/>
            <line x1="3" y1="3" x2="21" y2="21" stroke="gray" stroke-width="2"/>
        `;
  } else {
    input.type = "password";

    // icon normal
    icon.innerHTML = `
            <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
        `;
  }
}
