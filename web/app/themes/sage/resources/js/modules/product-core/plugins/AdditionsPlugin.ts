import type { ProductPlugin, ProductPurchaseAddition, ProductPurchaseStore } from '../types'

export class AdditionsPlugin implements ProductPlugin {
    pluginName = 'additions'

    init(store: ProductPurchaseStore): void {
        const additionCheckboxes = document.querySelectorAll<HTMLInputElement>('.addition-checkbox')
        const additionContainers = document.querySelectorAll<HTMLElement>('[data-addition-inputs]')

        const syncAdditionInputs = (additionId: string, isSelected: boolean): void => {
            if (!additionId) {
                return
            }

            additionContainers.forEach((container) => {
                const existing = container.querySelector<HTMLInputElement>(`input[value="${additionId}"]`)

                if (isSelected) {
                    if (existing) {
                        return
                    }

                    const input = document.createElement('input')
                    input.type = 'hidden'
                    input.name = 'addition_ids[]'
                    input.value = additionId
                    container.appendChild(input)
                    return
                }

                existing?.remove()
            })
        }

        additionCheckboxes.forEach((checkbox) => {
            const syncCheckboxState = (): void => {
                const additionId = Number(checkbox.dataset.additionId)
                const additionPrice = Number(checkbox.dataset.additionPrice ?? '0')
                const additionName = checkbox.dataset.additionName ?? ''

                if (additionId > 0 && additionName !== '') {
                    const addition: ProductPurchaseAddition = {
                        id: additionId,
                        name: additionName,
                        price: Number.isFinite(additionPrice) ? additionPrice : 0,
                    }

                    store.setAddition(addition, checkbox.checked)
                }

                syncAdditionInputs(checkbox.dataset.additionId ?? '', checkbox.checked)
            }

            syncCheckboxState()
            checkbox.addEventListener('change', syncCheckboxState)
        })
    }
}