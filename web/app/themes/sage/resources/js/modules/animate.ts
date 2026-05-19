import 'animate.css/source/_vars.css';
import 'animate.css/source/_base.css';
import 'animate.css/source/attention_seekers/heartBeat.css';

// type animationName = 'tada' | 'bounce' | 'flash' | 'pulse' | 'rubberBand' | 'shakeX' | 'shakeY' | 'headShake' | 'swing' | 'wobble' | 'jello' | 'heartBeat';
type animationName = 'heartBeat';

export const animateCSS = (element: HTMLElement | string, animation: animationName) =>
    // We create a Promise and return it
    new Promise((resolve, reject) => {
        const node = typeof element === 'string' ? document.querySelector(element) : element;

        if (!node) {
            reject(new Error('Element not found'));
            return;
        }

        node.classList.add('animated', animation);

        // When the animation ends, we clean the classes and resolve the Promise
        function handleAnimationEnd(event: Event) {
            event.stopPropagation();
            node?.classList.remove('animated', animation);
            resolve('Animation ended');
        }

        node.addEventListener('animationend', handleAnimationEnd, { once: true });
    });