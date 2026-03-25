function closePopup() {
    const popup = document.querySelector('.popup-overlay');
    if (popup) {
        popup.style.opacity = "0";
        setTimeout(() => {
            popup.style.display = "none";
        }, 300);

        try {
            const url = new URL(window.location.href);
            url.searchParams.delete('error');
            url.searchParams.delete('success');
            window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
        } catch (e) {
            // ignore
        }
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const popup = document.querySelector('.popup-overlay');
    if (popup) {
        try {
            const url = new URL(window.location.href);
            url.searchParams.delete('error');
            url.searchParams.delete('success');
            window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
        } catch (e) {
            // ignore
        }

        setTimeout(() => {
            popup.style.opacity = "0";
            setTimeout(() => {
                popup.style.display = "none";
            }, 300);
        }, 3000);
    }
});