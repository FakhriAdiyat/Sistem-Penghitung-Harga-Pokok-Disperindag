console.log("MAIN JS OK");

function clearAutofillFields(form) {
  if (!form) return;

  form.querySelectorAll("[data-autofill-field]").forEach((field) => {
    field.value = "";
  });
}

function togglePassword(trigger) {
  let input = null;
  let icon = null;

  if (trigger && trigger.closest) {
    const wrap = trigger.closest(".password-wrapper");
    if (wrap) {
      input = wrap.querySelector("input");
      icon = wrap.querySelector("svg");
    }
  }

  if (!input) input = document.getElementById("passwordInput");
  if (!icon) icon = document.getElementById("eyeIcon");
  if (!input || !icon) return;

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

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("form[data-clear-autofill]").forEach((form) => {
    clearAutofillFields(form);
    window.requestAnimationFrame(() => clearAutofillFields(form));
    window.setTimeout(() => clearAutofillFields(form), 120);
  });
});
