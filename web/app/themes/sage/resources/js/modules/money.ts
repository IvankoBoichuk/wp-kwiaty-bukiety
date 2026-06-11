import type { StoreApiMoney } from './woo-store-api'

export interface FormattedMoneyValue {
    amount: number
    formatted: string
}

export interface MoneyFormatConfig {
    currencySymbol?: string
    currencyPrefix?: string
    currencySuffix?: string
    currencyDecimalSeparator?: string
    currencyThousandSeparator?: string
    currencyMinorUnit?: number
}

function normalizeMoneyFormat(money: string | MoneyFormatConfig): Required<MoneyFormatConfig> {
    if (typeof money === 'string') {
        return {
            currencySymbol: money,
            currencyPrefix: '',
            currencySuffix: money ? ` ${money}` : '',
            currencyDecimalSeparator: ',',
            currencyThousandSeparator: ' ',
            currencyMinorUnit: 2,
        }
    }

    const currencySymbol = (money.currencySymbol || '').trim()
    const currencyPrefix = money.currencyPrefix ?? ''
    const currencySuffix = money.currencySuffix ?? ''

    return {
        currencySymbol,
        currencyPrefix,
        currencySuffix: currencyPrefix || currencySuffix ? currencySuffix : (currencySymbol ? ` ${currencySymbol}` : ''),
        currencyDecimalSeparator: money.currencyDecimalSeparator || ',',
        currencyThousandSeparator: money.currencyThousandSeparator || ' ',
        currencyMinorUnit: Number.isFinite(money.currencyMinorUnit) ? Math.max(0, money.currencyMinorUnit ?? 2) : 2,
    }
}

function formatNumericAmount(amount: number, fractionDigits: number, decimalSeparator: string, thousandSeparator: string): string {
    const sign = amount < 0 ? '-' : ''
    const absoluteAmount = Math.abs(amount)
    const [integerPart, decimalPart] = absoluteAmount.toFixed(fractionDigits).split('.')
    const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator)

    if (!decimalPart) {
        return `${sign}${formattedInteger}`
    }

    return `${sign}${formattedInteger}${decimalSeparator}${decimalPart}`
}

export function formatMoney(amount: number, money: string | MoneyFormatConfig): string {
    const normalizedMoney = normalizeMoneyFormat(money)
    const hasFraction = !Number.isInteger(amount)
    const fractionDigits = hasFraction ? normalizedMoney.currencyMinorUnit : 0
    const formattedAmount = formatNumericAmount(
        amount,
        fractionDigits,
        normalizedMoney.currencyDecimalSeparator,
        normalizedMoney.currencyThousandSeparator,
    )

    return `${normalizedMoney.currencyPrefix}${formattedAmount}${normalizedMoney.currencySuffix}`.trim()
}

export function formatStoreApiMoney(amount: string, money: StoreApiMoney): FormattedMoneyValue {
    const minorUnit = Number.isFinite(money.currency_minor_unit) ? money.currency_minor_unit : 2
    const rawAmount = Number.parseInt(amount || '0', 10)
    const normalizedAmount = Number.isNaN(rawAmount) ? 0 : rawAmount
    const value = normalizedAmount / (10 ** minorUnit)

    return {
        amount: value,
        formatted: formatMoney(value, {
            currencySymbol: money.currency_symbol,
            currencyPrefix: money.currency_prefix,
            currencySuffix: money.currency_suffix,
            currencyDecimalSeparator: money.currency_decimal_separator,
            currencyThousandSeparator: money.currency_thousand_separator,
            currencyMinorUnit: minorUnit,
        }),
    }
}
