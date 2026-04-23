/**
 * Product Order Options
 * Handle delivery date and time selection
 */

interface TimeSlot {
    start: number
    end: number
}

const TIME_SLOTS: Record<string, TimeSlot> = {
    '08-12': { start: 8, end: 12 },
    '12-15': { start: 12, end: 15 },
    '15-18': { start: 15, end: 18 },
    '18-21': { start: 18, end: 21 },
}

function isToday(date: Date): boolean {
    const today = new Date()
    return (
        date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear()
    )
}

function getMinimumDeliveryTime(): Date {
    const now = new Date()
    now.setHours(now.getHours() + 2)
    return now
}

function isTimeSlotAvailable(slotKey: string, deliveryDate: Date): boolean {
    if (!isToday(deliveryDate)) {
        return true // All slots available for future dates
    }

    const slot = TIME_SLOTS[slotKey]
    if (!slot) return false

    const minTime = getMinimumDeliveryTime()
    const minHour = minTime.getHours()

    // Check if minimum delivery time is before slot end
    // If current time + 2h is 16:30, then slot 15-18 is still available (ends at 18:00)
    // But slot 12-15 is not (ends at 15:00)
    if (minHour >= slot.end) {
        return false
    }

    // If we're in the slot's time range, check if there's enough time
    if (minHour >= slot.start && minHour < slot.end) {
        // Allow if there's at least some time left in the slot
        return true
    }

    return minHour < slot.start
}

function updateTimeSlotAvailability(selectedDate: Date): void {
    const timeOptions = document.querySelectorAll<HTMLButtonElement>('.delivery-time-option')

    timeOptions.forEach((btn) => {
        const slotKey = btn.dataset.timeSlot
        if (!slotKey) return

        const isAvailable = isTimeSlotAvailable(slotKey, selectedDate)

        if (isAvailable) {
            btn.disabled = false
            btn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none')
        } else {
            btn.disabled = true
            btn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none')
            // Remove active state if slot is disabled
            btn.classList.remove('bg-green-easy', 'text-white')
            btn.classList.add('bg-[#F7F7F6]', 'text-green-dark')
        }
    })
}

export function initProductOrderOptions(): void {
    let selectedDate = new Date() // Default to today

    // Handle delivery date selection
    const dateOptions = document.querySelectorAll<HTMLButtonElement>('.delivery-date-option')
    const customDateBtn = document.querySelector<HTMLButtonElement>('.delivery-date-custom')
    const dateInput = document.querySelector<HTMLInputElement>('[data-date-input]')
    const dateLabel = document.querySelector<HTMLSpanElement>('.delivery-date-label')

    // Set minimum date for date input
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0]
        dateInput.min = today
    }

    // Initial time slot availability check
    updateTimeSlotAvailability(selectedDate)

    dateOptions.forEach((btn) => {
        btn.addEventListener('click', () => {
            // Remove active state from all buttons
            dateOptions.forEach((b) => {
                b.classList.remove('bg-green-easy', 'text-white')
                b.classList.add('bg-[#F7F7F6]', 'text-green-dark')
            })

            // Add active state to clicked button
            btn.classList.remove('bg-[#F7F7F6]', 'text-green-dark')
            btn.classList.add('bg-green-easy', 'text-white')

            // Update selected date and time slot availability
            const dateOption = btn.dataset.dateOption
            if (dateOption === 'today') {
                selectedDate = new Date()
                updateTimeSlotAvailability(selectedDate)
            } else if (dateOption === 'tomorrow') {
                selectedDate = new Date()
                selectedDate.setDate(selectedDate.getDate() + 1)
                updateTimeSlotAvailability(selectedDate)
            } else if (dateOption === 'custom' && dateInput) {
                dateInput.showPicker()
            }
        })
    })

    // Handle custom date selection
    if (dateInput && dateLabel && customDateBtn) {
        dateInput.addEventListener('change', (e) => {
            const target = e.target as HTMLInputElement
            if (target.value) {
                const date = new Date(target.value + 'T00:00:00')
                selectedDate = date
                const formatted = date.toLocaleDateString('pl-PL', {
                    day: '2-digit',
                    month: '2-digit',
                })
                dateLabel.textContent = formatted
                updateTimeSlotAvailability(selectedDate)
            }
        })
    }

    // Handle delivery time selection
    const timeOptions = document.querySelectorAll<HTMLButtonElement>('.delivery-time-option')

    timeOptions.forEach((btn) => {
        btn.addEventListener('click', () => {
            if (btn.disabled) return

            // Remove active state from all buttons
            timeOptions.forEach((b) => {
                b.classList.remove('bg-green-easy', 'text-white')
                b.classList.add('bg-[#F7F7F6]', 'text-green-dark')
            })

            // Add active state to clicked button
            btn.classList.remove('bg-[#F7F7F6]', 'text-green-dark')
            btn.classList.add('bg-green-easy', 'text-white')
        })
    })
}

function initProductAdditions(): void {
    const additionButtons = document.querySelectorAll<HTMLButtonElement>('.addition-toggle')

    additionButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const parent = btn.closest('.group') // Assuming the button is inside an element with class 'group'
            if (!parent) return

            const isSelected = btn.dataset.selected === 'true'
            const plusIcon = btn.querySelector('.addition-icon-plus')
            const minusIcon = btn.querySelector('.addition-icon-minus')

            if (isSelected) {
                // Deselect
                btn.dataset.selected = 'false'
                plusIcon?.classList.remove('hidden')
                minusIcon?.classList.add('hidden')
                parent.classList.remove('active')
            } else {
                // Select
                btn.dataset.selected = 'true'
                plusIcon?.classList.add('hidden')
                minusIcon?.classList.remove('hidden')
                parent.classList.add('active')
            }
        })
    })
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initProductOrderOptions()
        initProductAdditions()
    })
} else {
    initProductOrderOptions()
    initProductAdditions()
}
