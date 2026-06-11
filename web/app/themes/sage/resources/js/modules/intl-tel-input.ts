import intlTelInput from 'intl-tel-input'
import type { Iti } from 'intl-tel-input'
import 'intl-tel-input/styles'

const itiInstances = new WeakMap<HTMLInputElement, Iti>()

export function initIntlTelInputs(): void {
    const phoneInputs = document.querySelectorAll<HTMLInputElement>('input[type="tel"]')

    phoneInputs.forEach((input) => {
        if (input.dataset.intlTelInputInitialized === 'true') {
            return
        }

        const instance = intlTelInput(input, {
            initialCountry: 'pl',
            nationalMode: true,
            autoPlaceholder: 'aggressive',
            formatOnDisplay: true,
            separateDialCode: true,
            loadUtils: () => import('intl-tel-input/utils'),
        })

        itiInstances.set(input, instance)

        input.dataset.intlTelInputInitialized = 'true'
    })
}

export function normalizeIntlTelInputValue(input: HTMLInputElement): string {
    const instance = itiInstances.get(input)

    if (!instance) {
        return input.value.trim()
    }

    const normalizedValue = instance.getNumber('E164')

    if (!normalizedValue) {
        return input.value.trim()
    }

    input.value = normalizedValue

    return normalizedValue
}

export function isIntlTelInputValid(input: HTMLInputElement): boolean {
    const instance = itiInstances.get(input)

    if (!instance) {
        return input.value.replace(/\D/g, '').length >= 9
    }

    const validationResult = instance.isValidNumber()

    if (validationResult === null) {
        return input.value.replace(/\D/g, '').length >= 9
    }

    return validationResult
}