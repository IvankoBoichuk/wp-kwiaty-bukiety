import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

export function initMenu() {
  // Register collapse plugin
  Alpine.plugin(collapse);

  Alpine.data('mobileMenu', () => ({
    isOpen: false,

    toggleMenu() {
      this.isOpen = !this.isOpen;

      // Prevent body scroll when menu is open
      if (this.isOpen) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    },

    closeMenu() {
      this.isOpen = false;
      document.body.style.overflow = '';
    },

    search() {
      // Placeholder for search functionality
      console.log('Search triggered');
      // You can implement search modal or redirect here
    }
  }));

  Alpine.data('accordion', () => ({
    activeItem: null as string | null,

    toggle(id: string) {
      this.activeItem = this.activeItem === id ? null : id;
    },

    isActive(id: string) {
      return this.activeItem === id;
    }
  }));

  Alpine.start();
}
