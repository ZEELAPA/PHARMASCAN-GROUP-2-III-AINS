document.addEventListener('DOMContentLoaded', () => {
    // --- ROBUST SIDEBAR LOGIC ---
    const sidebar = document.getElementById('sidebar');

    // Only run sidebar code if the sidebar element actually exists on the page
    if (sidebar) {
        const sidebarToggleTrigger = document.querySelector('.top-panel');
        const navLinks = sidebar.querySelectorAll('.nav-panel a');

        // Ensure the sidebar has a smooth transition from the start
        sidebar.style.transition = 'width 0.3s ease';

        // 1. Make ONLY the top panel clickable to toggle the sidebar
        if (sidebarToggleTrigger) {
            sidebarToggleTrigger.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
            });
        }

        // 2. Prevent clicks on links from bubbling up and triggering the toggle
        navLinks.forEach(link => {
            link.addEventListener('click', (event) => {
                // This stops the click event from reaching the sidebar's listener
                event.stopPropagation();
            });
        });
    }
    
    // TOAST CODE

    const toastElements = document.querySelectorAll('.status-toast');
    const initialTopOffset = 20; // Starting position from the top in pixels
    const gap = 10; // Gap between toasts in pixels
    let currentTop = initialTopOffset;

    if (toastElements.length > 0) {
        toastElements.forEach((toast, index) => {
            // Stagger the animation slightly for a nicer effect
            const showDelay = 100 + (index * 150);
            const hideDelay = 5000 + (index * 150);

            // Calculate the vertical position for this toast
            toast.style.top = `${currentTop}px`;
            
            // Update the 'currentTop' for the next toast
            // offsetHeight gives the actual rendered height of the element
            currentTop += toast.offsetHeight + gap; 

            // --- Animation Timers for EACH toast ---
            setTimeout(() => {
                toast.classList.add('show');
            }, showDelay);

            setTimeout(() => {
                toast.classList.remove('show');
            }, hideDelay);
            
            setTimeout(() => {
                toast.remove();
            }, hideDelay + 500); // Animation is 0.5s
        });
    }
});

let currentFormId = null;

function openModal(formId) {
    currentFormId = formId;
    const modal = document.getElementById('confirmationModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeModal() {
    const modal = document.getElementById('confirmationModal');
    if (modal) {
        modal.style.display = 'none';
    }
    currentFormId = null;
}

function confirmSubmit() {
    if (currentFormId) {
        const form = document.getElementById(currentFormId);
        if (form) {
            form.submit();
        }
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('confirmationModal');
    if (event.target === modal) {
        closeModal();
    }
}