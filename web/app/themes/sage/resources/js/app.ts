import '../css/app.css';
import './modules/video-player';
import './modules/swiper-init';
import './modules/lightgallery-init';
import './modules/product-bootstrap';
import './modules/cities-load-more';
import Alpine from 'alpinejs';
import setScrollBarWidth from './modules/set-scrollbar-width';
import { initDeliveryTimers } from './modules/delivery-timer';
import { initMenu } from './modules/menu';
import { initCounterAnimation } from './modules/counter-animation';
import collapse from '@alpinejs/collapse';

// Register collapse plugin
window.Alpine = Alpine;
Alpine.plugin(collapse);

setScrollBarWidth();
initMenu();
initCounterAnimation();
initDeliveryTimers();
Alpine.start();