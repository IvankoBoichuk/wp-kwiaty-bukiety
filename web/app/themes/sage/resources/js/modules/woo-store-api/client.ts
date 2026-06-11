import type {
    StoreApiClientOptions,
    StoreApiErrorResponse,
    StoreApiMethod,
    StoreApiQuery,
    StoreApiRequestOptions,
} from './types'

export class WooStoreApiError<TData = unknown> extends Error {
    readonly code?: string
    readonly status: number
    readonly data?: TData

    constructor(message: string, options: { code?: string, status: number, data?: TData }) {
        super(message)
        this.name = 'WooStoreApiError'
        this.code = options.code
        this.status = options.status
        this.data = options.data
    }
}

export class WooStoreApiClient {
    protected readonly fetchImpl: typeof fetch
    protected readonly baseUrl: string
    protected nonce: string
    protected cartToken: string

    constructor(options: StoreApiClientOptions = {}) {
        this.fetchImpl = options.fetch || fetch
        this.baseUrl = (options.baseUrl || '/wp-json/wc/store/v1').replace(/\/+$/, '')
        this.nonce = options.nonce || ''
        this.cartToken = options.cartToken || ''
    }

    getNonce(): string {
        return this.nonce
    }

    setNonce(nonce: string): void {
        this.nonce = nonce
    }

    getCartToken(): string {
        return this.cartToken
    }

    setCartToken(cartToken: string): void {
        this.cartToken = cartToken
    }

    async get<TResponse>(path: string, options: StoreApiRequestOptions = {}): Promise<TResponse> {
        return this.request<TResponse>('GET', path, undefined, options)
    }

    async post<TResponse, TBody = undefined>(path: string, body?: TBody, options: StoreApiRequestOptions = {}): Promise<TResponse> {
        return this.request<TResponse>('POST', path, body, options)
    }

    async put<TResponse, TBody = undefined>(path: string, body?: TBody, options: StoreApiRequestOptions = {}): Promise<TResponse> {
        return this.request<TResponse>('PUT', path, body, options)
    }

    async delete<TResponse, TBody = undefined>(path: string, body?: TBody, options: StoreApiRequestOptions = {}): Promise<TResponse> {
        return this.request<TResponse>('DELETE', path, body, options)
    }

    protected async request<TResponse, TBody = undefined>(
        method: StoreApiMethod,
        path: string,
        body?: TBody,
        options: StoreApiRequestOptions = {},
    ): Promise<TResponse> {

        const response = await this.fetchImpl(this.buildUrl(path, options.query), {
            method,
            credentials: 'same-origin',
            headers: this.buildHeaders(options.headers),
            body: body === undefined ? undefined : JSON.stringify(body),
            signal: options.signal,
        })

        this.captureResponseTokens(response)

        const payload = await this.parseJson<StoreApiErrorResponse | TResponse>(response)

        if (!response.ok) {
            const errorPayload = (payload || {}) as StoreApiErrorResponse

            throw new WooStoreApiError(errorPayload.message || 'Woo Store API request failed.', {
                code: errorPayload.code,
                status: this.resolveStatus(response.status, errorPayload.data),
                data: errorPayload.data,
            })
        }

        return payload as TResponse
    }

    protected buildUrl(path: string, query?: StoreApiQuery): string {
        const url = new URL(`${this.baseUrl}/${path.replace(/^\/+/, '')}`, window.location.origin)

        if (!query) {
            return url.toString()
        }

        Object.entries(query).forEach(([key, value]) => {
            if (Array.isArray(value)) {
                value.forEach((entry) => {
                    if (entry !== null && entry !== undefined) {
                        url.searchParams.append(key, String(entry))
                    }
                })

                return
            }

            if (value !== null && value !== undefined) {
                url.searchParams.set(key, String(value))
            }
        })

        return url.toString()
    }

    protected buildHeaders(headers?: HeadersInit): Headers {
        const result = new Headers(headers)

        if (!result.has('Accept')) {
            result.set('Accept', 'application/json')
        }

        if (!result.has('Content-Type')) {
            result.set('Content-Type', 'application/json')
        }

        if (this.nonce !== '' && !result.has('Nonce')) {
            result.set('Nonce', this.nonce)
        }

        if (this.cartToken !== '' && !result.has('Cart-Token')) {
            result.set('Cart-Token', this.cartToken)
        }

        return result
    }

    protected captureResponseTokens(response: Response): void {
        const nextNonce = response.headers.get('Nonce')
        const nextCartToken = response.headers.get('Cart-Token')

        if (nextNonce) {
            this.nonce = nextNonce
        }

        if (nextCartToken) {
            this.cartToken = nextCartToken
        }
    }

    protected async parseJson<TPayload>(response: Response): Promise<TPayload | undefined> {
        const contentType = response.headers.get('Content-Type') || ''

        if (!contentType.toLowerCase().includes('application/json')) {
            return undefined
        }

        return await response.json() as TPayload
    }

    protected resolveStatus(status: number, data: unknown): number {
        if (!data || typeof data !== 'object') {
            return status
        }

        const nestedStatus = 'status' in data ? data.status : undefined

        return typeof nestedStatus === 'number' ? nestedStatus : status
    }
}