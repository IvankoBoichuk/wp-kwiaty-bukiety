/**
 * Animate numbers from 0 to their target value when the section becomes visible
 */
export function initCounterAnimation() {
    const counterElements = document.querySelectorAll('[data-counter]');
    
    if (counterElements.length === 0) return;

    // Parse number from various formats (e.g., "4.8 / 5", "120 000+", "50 000+", "10")
    function parseCounterValue(text: string): { value: number; decimals: number; suffix: string; prefix: string } {
        // Check for ratio format like "4.8 / 5"
        if (text.includes('/')) {
            const parts = text.split('/');
            const value = parseFloat(parts[0].trim().replace(/\s/g, ''));
            return { 
                value, 
                decimals: parts[0].includes('.') ? parts[0].split('.')[1].trim().length : 0,
                suffix: ' / ' + parts[1].trim(),
                prefix: ''
            };
        }

        // Extract suffix (like "+")
        const suffix = text.match(/\+$/)?.[0] || '';
        
        // Remove spaces and suffix, then parse
        const cleanText = text.replace(/\s/g, '').replace(/\+$/, '');
        const value = parseFloat(cleanText);
        
        // Check if original had decimals
        const decimals = text.includes('.') ? text.split('.')[1].replace(/\D/g, '').length : 0;

        return { value, decimals, suffix, prefix: '' };
    }

    // Format number with spaces for thousands
    function formatNumber(num: number, decimals: number): string {
        if (decimals > 0) {
            return num.toFixed(decimals).replace('.', '.');
        }
        
        // Add space separator for thousands
        return Math.floor(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    // Animate a single counter
    function animateCounter(element: Element, duration: number = 2000) {
        const targetText = element.getAttribute('data-counter');
        if (!targetText) return;

        const { value: targetValue, decimals, suffix, prefix } = parseCounterValue(targetText);
        const startTime = performance.now();

        function update(currentTime: number) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentValue = targetValue * easeOut;

            element.textContent = prefix + formatNumber(currentValue, decimals) + suffix;

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                // Ensure final value is exact
                element.textContent = prefix + formatNumber(targetValue, decimals) + suffix;
            }
        }

        requestAnimationFrame(update);
    }

    // Use Intersection Observer to trigger animation when section is visible
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const counterElements = entry.target.querySelectorAll('[data-counter]');
                    
                    // Animate counters in sequence with delay between each
                    counterElements.forEach((el, index) => {
                        setTimeout(() => {
                            animateCounter(el, 1500);
                        }, index * 400); // 400ms delay between each animation start
                    });
                    
                    // Unobserve after animating once
                    observer.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.3, // Trigger when 30% of the section is visible
        }
    );

    // Find the parent section and observe it
    const section = document.querySelector('[data-counter-section]');
    if (section) {
        observer.observe(section);
    }
}
