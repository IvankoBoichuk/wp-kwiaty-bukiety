import type { ProductPlugin, ProductPurchaseStore } from '../types'

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

function formatDateValue(date: Date): string {
    const year = date.getFullYear()
    const month = `${date.getMonth() + 1}`.padStart(2, '0')
    const day = `${date.getDate()}`.padStart(2, '0')

    return `${year}-${month}-${day}`
}

function setHiddenValue(selector: string, value: string): void {
    document.querySelectorAll<HTMLInputElement>(selector).forEach((input) => {
        input.value = value
    })
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
        return true
    }

    const slot = TIME_SLOTS[slotKey]

    if (!slot) {
        return false
    }

    const minTime = getMinimumDeliveryTime()
    const minHour = minTime.getHours()

    if (minHour >= slot.end) {
        return false
    }

    if (minHour >= slot.start && minHour < slot.end) {
        return true
    }

    return minHour < slot.start
}

export class DeliveryPlugin implements ProductPlugin {
    pluginName = 'delivery'
    private selectedDate: Date | null = null

    init(store: ProductPurchaseStore): void {
        const dateOptions = document.querySelectorAll<HTMLButtonElement>('.delivery-date-option')
        const customDateBtn = document.querySelector<HTMLButtonElement>('.delivery-date-custom')
        const dateInput = document.querySelector<HTMLInputElement>('[data-date-input]')
        const dateLabel = document.querySelector<HTMLSpanElement>('.delivery-date-label')
        const cardMessageField = document.querySelector<HTMLTextAreaElement>('[name="card-message"]')
        const timeOptions = document.querySelectorAll<HTMLButtonElement>('.delivery-time-option')

        setHiddenValue('[data-delivery-date-hidden]', '')
        setHiddenValue('[data-delivery-time-hidden]', '')
        setHiddenValue('[data-card-message-hidden]', cardMessageField?.value ?? '')
        store.setDeliveryDate('')
        store.setDeliveryTime('')
        store.setCardMessage(cardMessageField?.value ?? '')

        if (dateInput) {
            dateInput.min = new Date().toISOString().split('T')[0]
        }

        this.updateTimeSlotAvailability(store, this.selectedDate)

        dateOptions.forEach((btn) => {
            btn.addEventListener('click', () => {
                dateOptions.forEach((button) => {
                    button.classList.remove('bg-green-easy', 'text-white')
                    button.classList.add('bg-[#F7F7F6]', 'text-green-dark')
                })

                btn.classList.remove('bg-[#F7F7F6]', 'text-green-dark')
                btn.classList.add('bg-green-easy', 'text-white')

                const dateOption = btn.dataset.dateOption

                if (dateOption === 'today') {
                    this.selectedDate = new Date()
                    this.syncSelectedDate(store, this.selectedDate)
                } else if (dateOption === 'tomorrow') {
                    this.selectedDate = new Date()
                    this.selectedDate.setDate(this.selectedDate.getDate() + 1)
                    this.syncSelectedDate(store, this.selectedDate)
                } else if (dateOption === 'custom' && dateInput) {
                    dateInput.showPicker()
                }
            })
        })

        if (dateInput && dateLabel && customDateBtn) {
            dateInput.addEventListener('change', (event) => {
                const target = event.target as HTMLInputElement

                if (!target.value) {
                    return
                }

                this.selectedDate = new Date(`${target.value}T00:00:00`)
                dateLabel.textContent = this.selectedDate.toLocaleDateString('pl-PL', {
                    day: '2-digit',
                    month: '2-digit',
                })

                this.syncSelectedDate(store, this.selectedDate)
            })
        }

        timeOptions.forEach((btn) => {
            btn.addEventListener('click', () => {
                if (btn.disabled) {
                    return
                }

                timeOptions.forEach((button) => {
                    button.classList.remove('bg-green-easy', 'text-white')
                    button.classList.add('bg-[#F7F7F6]', 'text-green-dark')
                })

                btn.classList.remove('bg-[#F7F7F6]', 'text-green-dark')
                btn.classList.add('bg-green-easy', 'text-white')

                const value = btn.dataset.timeSlot ?? ''
                setHiddenValue('[data-delivery-time-hidden]', value)
                store.setDeliveryTime(value)
            })
        })

        cardMessageField?.addEventListener('input', () => {
            setHiddenValue('[data-card-message-hidden]', cardMessageField.value)
            store.setCardMessage(cardMessageField.value)
        })
    }

    private syncSelectedDate(store: ProductPurchaseStore, selectedDate: Date): void {
        this.updateTimeSlotAvailability(store, selectedDate)

        const value = formatDateValue(selectedDate)
        setHiddenValue('[data-delivery-date-hidden]', value)
        store.setDeliveryDate(value)
    }

    private updateTimeSlotAvailability(store: ProductPurchaseStore, selectedDate: Date | null): void {
        const timeOptions = document.querySelectorAll<HTMLButtonElement>('.delivery-time-option')

        if (!selectedDate) {
            timeOptions.forEach((btn) => {
                btn.disabled = true
                btn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none')
                btn.classList.remove('bg-green-easy', 'text-white')
                btn.classList.add('bg-[#F7F7F6]', 'text-green-dark')
            })

            setHiddenValue('[data-delivery-time-hidden]', '')
            store.setDeliveryTime('')

            return
        }

        let activeSlotStillAvailable = false

        timeOptions.forEach((btn) => {
            const slotKey = btn.dataset.timeSlot

            if (!slotKey) {
                return
            }

            const isAvailable = isTimeSlotAvailable(slotKey, selectedDate)

            if (isAvailable) {
                btn.disabled = false
                btn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none')

                if (btn.classList.contains('bg-green-easy')) {
                    activeSlotStillAvailable = true
                }

                return
            }

            btn.disabled = true
            btn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none')
            btn.classList.remove('bg-green-easy', 'text-white')
            btn.classList.add('bg-[#F7F7F6]', 'text-green-dark')
        })

        if (!activeSlotStillAvailable) {
            setHiddenValue('[data-delivery-time-hidden]', '')
            store.setDeliveryTime('')
        }
    }
}