// Sidebar toggle button
const toggleBtn = document.getElementById('toggle-btn');
const wrapper = document.getElementById('wrapper');

function collapseSidebar() {
    if (window.innerWidth <= 768) {
        wrapper.classList.add('collapsed');
    } else {
        wrapper.classList.remove('collapsed');
    }
}

// Run on page load
collapseSidebar();

// Run on window resize
window.addEventListener('resize', collapseSidebar);

// Manual toggle button
toggleBtn.addEventListener('click', () => {
    wrapper.classList.toggle('collapsed');
});

