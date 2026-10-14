const menu = document.querySelector('[data-mobile-menu]');
document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => menu?.classList.toggle('is-open'));
document.querySelector('[data-dismiss-toast]')?.addEventListener('click', (event) => event.currentTarget.closest('.toast')?.remove());

const filterDrawer = document.querySelector('[data-filter-drawer]');
document.querySelector('[data-filter-open]')?.addEventListener('click', () => filterDrawer?.classList.add('is-open'));
document.querySelector('[data-filter-close]')?.addEventListener('click', () => filterDrawer?.classList.remove('is-open'));

document.querySelectorAll('[data-quantity]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = button.parentElement?.querySelector('input');
        if (!input) return;
        const current = Number(input.value || 0);
        input.value = Math.max(Number(input.min || 0), Math.min(Number(input.max || 10), current + (button.dataset.quantity === 'plus' ? 1 : -1)));
    });
});

document.querySelectorAll('img').forEach((image) => {
    image.addEventListener('error', () => {
        image.style.background = '#e9eee3';
        image.style.objectFit = 'cover';
    }, { once: true });
});
