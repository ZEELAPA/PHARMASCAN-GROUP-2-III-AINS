document.addEventListener('DOMContentLoaded', () => {
    const toastElement = document.getElementById('status-toast');

    // Only run the script if the toast message actually exists on the page
    if (toastElement) {
        
        // 1. Slide the toast in
        // We use a tiny delay to ensure the browser has rendered the element
        // in its initial (hidden) state before we trigger the transition.
        setTimeout(() => {
            toastElement.classList.add('show');
        }, 100); // 100ms delay

        // 2. Wait 5 seconds, then slide the toast out
        setTimeout(() => {
            toastElement.classList.remove('show');
        }, 5000); // 5000ms = 5 seconds
        
        // 3. Optional: After the slide-out animation finishes, remove the element
        // The animation takes 0.5s, so we wait 5s + 0.5s
        setTimeout(() => {
            if (toastElement) {
                toastElement.remove();
            }
        }, 5500);
    }
});