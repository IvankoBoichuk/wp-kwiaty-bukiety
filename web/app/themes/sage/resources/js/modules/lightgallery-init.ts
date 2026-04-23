/**
 * LightGallery Initialization with Lazy Loading
 * Load and initialize lightbox gallery only when needed
 */

let lightGalleryLoaded = false
const galleryInstances = new Map()

async function loadLightGallery(gallery: HTMLElement): Promise<void> {
    if (galleryInstances.has(gallery)) {
        return
    }

    // Lazy load lightGallery and plugins
    const [{ default: lightGallery }, { default: lgZoom }, { default: lgThumbnail }] = await Promise.all([
        import('lightgallery'),
        import('lightgallery/plugins/zoom'),
        import('lightgallery/plugins/thumbnail'),
    ])

    // Load CSS only once
    if (!lightGalleryLoaded) {
        await Promise.all([
            import('../../css/lightgallery.css'),
        ])
        lightGalleryLoaded = true
    }

    // Initialize lightGallery
    const instance = lightGallery(gallery, {
        selector: '.lightgallery-item',
        plugins: [lgZoom, lgThumbnail],
        speed: 500,
        download: false,
        counter: true,
        closeOnTap: true,
        escKey: true,
        keyPress: true,
        zoom: true,
        thumbnail: true,
        animateThumb: true,
    })

    galleryInstances.set(gallery, instance)
}

export function initLightGallery(): void {
    const galleries = document.querySelectorAll<HTMLElement>('.product-gallery-swiper')

    galleries.forEach((gallery) => {
        const links = gallery.querySelectorAll<HTMLAnchorElement>('.lightgallery-item')

        // Block default behavior on all links
        links.forEach((link) => {
            link.addEventListener('click', async (e) => {
                e.preventDefault()

                // Load and initialize gallery on first click
                if (!galleryInstances.has(gallery)) {
                    await loadLightGallery(gallery)
                }

                // Open gallery at clicked item
                const instance = galleryInstances.get(gallery)
                if (instance) {
                    const index = Array.from(links).indexOf(link)
                    instance.openGallery(index)
                }
            })
        })
    })
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLightGallery)
} else {
    initLightGallery()
}
