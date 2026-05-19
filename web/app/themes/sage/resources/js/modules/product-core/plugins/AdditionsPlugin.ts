import type { ProductPlugin, ProductPurchaseAddition, ProductPurchaseStore } from '../types'

export class AdditionsPlugin implements ProductPlugin {
    pluginName = 'additions'
    init(store: ProductPurchaseStore): void {
        const additionButtons = document.querySelectorAll<HTMLButtonElement>('.addition-toggle')
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

        additionButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const parent = btn.closest('.group')

                if (!parent) {
                    return
                }

                const isSelected = btn.dataset.selected === 'true'
                const nextSelectedState = !isSelected
                const plusIcon = btn.querySelector('.addition-icon-plus')
                const minusIcon = btn.querySelector('.addition-icon-minus')
                const additionId = Number(btn.dataset.additionId)
                const additionPrice = Number(btn.dataset.additionPrice ?? '0')
                const additionName = btn.dataset.additionName ?? ''

                if (additionId > 0 && additionName !== '') {
                    const addition: ProductPurchaseAddition = {
                        id: additionId,
                        name: additionName,
                        price: Number.isFinite(additionPrice) ? additionPrice : 0,
                    }

                    store.setAddition(addition, nextSelectedState)
                }

                if (isSelected) {
                    btn.dataset.selected = 'false'
                    plusIcon?.classList.remove('hidden')
                    minusIcon?.classList.add('hidden')
                    parent.classList.remove('active')
                    syncAdditionInputs(btn.dataset.additionId ?? '', false)
                    return
                }

                btn.dataset.selected = 'true'
                plusIcon?.classList.add('hidden')
                minusIcon?.classList.remove('hidden')
                parent.classList.add('active')
                syncAdditionInputs(btn.dataset.additionId ?? '', true)
            })
        })
    }
}