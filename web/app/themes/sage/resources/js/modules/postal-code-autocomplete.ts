type PostalCodeItem = {
    postal_code?: string
    settlement?: string
    street?: string
    house_numbers?: string
    municipality?: string
    county?: string
    province?: string
}

type ApiResponse = {
    data: PostalCodeItem[]
    count: number
}

class PostalCodeAutocomplete {
    private postcodeInput: HTMLInputElement | null
    private settlementInput: HTMLInputElement | null

    private postcodeDatalist: HTMLDataListElement
    private settlementDatalist: HTMLDataListElement

    private debounceTimer: number | null = null
    private abortController: AbortController | null = null

    constructor() {
        this.postcodeInput = document.querySelector<HTMLInputElement>('#shipping_postcode')
        this.settlementInput = document.querySelector<HTMLInputElement>('#shipping_city')

        this.postcodeDatalist = this.createDatalist('shipping-postcode-datalist')
        this.settlementDatalist = this.createDatalist('shipping-settlement-datalist')
    }

    public init(): void {
        if (!this.postcodeInput || !this.settlementInput) {
            return
        }

        this.postcodeInput.setAttribute('list', this.postcodeDatalist.id)
        this.settlementInput.setAttribute('list', this.settlementDatalist.id)

        this.postcodeInput.insertAdjacentElement('afterend', this.postcodeDatalist)
        this.settlementInput.insertAdjacentElement('afterend', this.settlementDatalist)

        this.postcodeInput.addEventListener('input', () => this.handlePostcodeInput())
        this.postcodeInput.addEventListener('change', () => this.handlePostcodeInput())

        this.settlementInput.addEventListener('input', () => this.handleSettlementInput())
        this.settlementInput.addEventListener('change', () => this.handleSettlementInput())
    }

    private handlePostcodeInput(): void {
        if (!this.postcodeInput || !this.settlementInput) {
            return
        }

        const postcode = this.postcodeInput.value.trim()
        const settlement = this.settlementInput.value.trim()

        this.clear(this.settlementDatalist)

        if (settlement !== '') {
            return
        }

        if (!/^\d{2}-\d{3}$/.test(postcode)) {
            return
        }

        this.debounce(async () => {
            try {
                const response = await this.fetchByPostcode(postcode)

                if (this.postcodeInput?.value.trim() !== postcode) {
                    return
                }

                this.renderSettlementOptions(response.data)
            } catch (error) {
                this.handleRequestError(error)
            }
        })
    }

    private handleSettlementInput(): void {
        if (!this.postcodeInput || !this.settlementInput) {
            return
        }

        const postcode = this.postcodeInput.value.trim()
        const settlement = this.settlementInput.value.trim()

        this.clear(this.postcodeDatalist)

        if (postcode !== '') {
            return
        }

        if (settlement.length < 2) {
            return
        }

        this.debounce(async () => {
            try {
                const response = await this.fetchBySettlement(settlement)

                if (this.settlementInput?.value.trim() !== settlement) {
                    return
                }

                this.renderPostcodeOptions(response.data)
            } catch (error) {
                this.handleRequestError(error)
            }
        })
    }

    private async fetchByPostcode(postcode: string): Promise<ApiResponse> {
        const url = `/wp-json/sage/v1/postal-codes/by-postal-code?postal_code=${encodeURIComponent(postcode)}`

        return this.request(url)
    }

    private async fetchBySettlement(settlement: string): Promise<ApiResponse> {
        const url = `/wp-json/sage/v1/postal-codes/by-settlement?settlement=${encodeURIComponent(settlement)}&limit=20`

        return this.request(url)
    }

    private async request(url: string): Promise<ApiResponse> {
        this.abortController?.abort()
        this.abortController = new AbortController()

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
            signal: this.abortController.signal,
        })

        if (!response.ok) {
            throw new Error(`Postal code API error: ${response.status}`)
        }

        return response.json()
    }

    private renderSettlementOptions(items: PostalCodeItem[]): void {
        const settlements = this.unique(
            items
                .map((item) => item.settlement)
                .filter((settlement): settlement is string => Boolean(settlement))
        )

        this.renderOptions(this.settlementDatalist, settlements)
    }

    private renderPostcodeOptions(items: PostalCodeItem[]): void {
        const postcodes = this.unique(
            items
                .map((item) => item.postal_code)
                .filter((postcode): postcode is string => Boolean(postcode))
        )

        this.renderOptions(this.postcodeDatalist, postcodes)
    }

    private renderOptions(datalist: HTMLDataListElement, values: string[]): void {
        this.clear(datalist)

        values.forEach((value) => {
            const option = document.createElement('option')
            option.value = value

            datalist.appendChild(option)
        })
    }

    private createDatalist(id: string): HTMLDataListElement {
        const datalist = document.createElement('datalist')
        datalist.id = id

        return datalist
    }

    private clear(element: HTMLElement): void {
        element.innerHTML = ''
    }

    private unique(values: string[]): string[] {
        return [...new Set(values)]
    }

    private debounce(callback: () => void | Promise<void>, delay = 350): void {
        if (this.debounceTimer) {
            window.clearTimeout(this.debounceTimer)
        }

        this.debounceTimer = window.setTimeout(callback, delay)
    }

    private handleRequestError(error: unknown): void {
        if (error instanceof DOMException && error.name === 'AbortError') {
            return
        }

        console.error(error)
    }
}

export const initPostalCodeAutocomplete = (): void => {
    new PostalCodeAutocomplete().init()
}