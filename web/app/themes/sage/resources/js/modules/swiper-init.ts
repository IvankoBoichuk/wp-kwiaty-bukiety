/**
 * Swiper Initialization
 * Initialize all Swiper sliders on the page
 */

import Swiper from 'swiper'
import { Pagination, Navigation, Thumbs } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import 'swiper/css/navigation'

function initEventsSwipers(): void {
    const swipers = document.querySelectorAll<HTMLElement>('.events-swiper')

    swipers.forEach((swiperEl) => {
        new Swiper(swiperEl, {
            modules: [Pagination, Navigation],
            spaceBetween: 12,
            slidesPerView: 1.3,
            slidesPerGroup: 1,
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    slidesPerGroup: 2,
                },
                1024: {
                    slidesPerView: 4,
                    slidesPerGroup: 4,
                },
            },
            pagination: {
                el: swiperEl.querySelector('.swiper-pagination') as HTMLElement,
                clickable: true,
            },
            navigation: {
                prevEl: `#${swiperEl.id}-prev`,
                nextEl: `#${swiperEl.id}-next`,
            },
        })
    })
}

function initProductGallery(): void {
    const swipers = document.querySelectorAll<HTMLElement>('.product-gallery-swiper')

    swipers.forEach((swiperEl) => {
        const galleryEl = swiperEl.closest<HTMLElement>('.product-gallery')
        const prevEl = galleryEl?.querySelector<HTMLElement>('.product-gallery-prev')
        const nextEl = galleryEl?.querySelector<HTMLElement>('.product-gallery-next')
        const thumbsEl = galleryEl?.querySelector<HTMLElement>('.product-gallery-thumbs')
        const thumbsSwiper = thumbsEl
            ? new Swiper(thumbsEl, {
                spaceBetween: 12,
                slidesPerView: 5.2,
                watchSlidesProgress: true,
                slideToClickedSlide: true,
                breakpoints: {
                    1024: {
                        slidesPerView: 6.2,
                    },
                },
            })
            : undefined

        new Swiper(swiperEl, {
            modules: [Navigation, Thumbs],
            spaceBetween: 16,
            slidesPerView: 1,
            navigation: {
                nextEl,
                prevEl,
            },
            thumbs: thumbsSwiper
                ? {
                    swiper: thumbsSwiper,
                }
                : undefined,
        })
    })
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initEventsSwipers()
        initProductGallery()
    })
} else {
    initEventsSwipers()
    initProductGallery()
}
