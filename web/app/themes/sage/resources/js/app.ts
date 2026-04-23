import '../css/app.css';
import setScrollBarWidth from './modules/set-scrollbar-width';
import './modules/video-player';
import './modules/swiper-init';
import './modules/lightgallery-init';
import './modules/product-order';
import './modules/cities-load-more';
import { initDeliveryTimers } from './modules/delivery-timer';
import { initMenu } from './modules/menu';
import { initCounterAnimation } from './modules/counter-animation';

setScrollBarWidth();
initMenu();
initCounterAnimation();
initDeliveryTimers();