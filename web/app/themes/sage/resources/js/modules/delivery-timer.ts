type HourRange = {
    start: number;
    end: number;
};

type DeliveryTimerConfig = {
    timezone: string;
    weekdayHours: HourRange;
    weekendHours: HourRange;
    holidays: string[];
};

type TimerState =
    | {
        type: 'countdown';
        value: string;
    }
    | {
        type: 'message';
        value: string;
    };

type ZonedNow = {
    date: Date;
    dateKey: string;
    dayIndex: number;
    hour: number;
};

const SELECTOR = '[data-delivery-timer]';

const pad = (value: number): string => String(value).padStart(2, '0');

const getZonedNow = (timeZone: string): ZonedNow => {
    const parts = new Intl.DateTimeFormat('en-CA', {
        timeZone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).formatToParts(new Date());

    const values = parts.reduce<Record<string, string>>((carry, part) => {
        if (part.type !== 'literal') {
            carry[part.type] = part.value;
        }

        return carry;
    }, {});

    const year = Number(values.year);
    const month = Number(values.month);
    const day = Number(values.day);
    const hour = Number(values.hour);
    const minute = Number(values.minute);
    const second = Number(values.second);
    const date = new Date(year, month - 1, day, hour, minute, second);

    return {
        date,
        dateKey: `${values.year}-${values.month}-${values.day}`,
        dayIndex: date.getDay(),
        hour,
    };
};

const getHoursForDay = (
    config: DeliveryTimerConfig,
    dayIndex: number,
): HourRange => {
    return dayIndex === 0 || dayIndex === 6
        ? config.weekendHours
        : config.weekdayHours;
};

const isHoliday = (dateKey: string, holidays: Set<string>): boolean => {
    return holidays.has(dateKey);
};

const formatHour = (hour: number): string => `${pad(hour)}:00`;

const formatDate = (date: Date): string => {
    return new Intl.DateTimeFormat('pl-PL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    }).format(date);
};

const formatDuration = (milliseconds: number): string => {
    const totalSeconds = Math.max(0, Math.floor(milliseconds / 1000));
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
};


const nextWorkingDate = (
    currentDate: Date,
    holidays: Set<string>,
): Date => {
    const candidate = new Date(currentDate);
    candidate.setHours(0, 0, 0, 0);

    do {
        candidate.setDate(candidate.getDate() + 1);
    } while (isHoliday(toDateKey(candidate), holidays));

    return candidate;
};

const toDateKey = (date: Date): string => {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
};

const nextDeliveryMessage = (
    config: DeliveryTimerConfig,
    now: ZonedNow,
    holidays: Set<string>,
    mode: 'today' | 'next',
): string => {
    if (mode === 'today') {
        const hours = getHoursForDay(config, now.dayIndex);
        return `Najbliższa dostawa dziś od <span>${formatHour(hours.start)}</span>`;
    }

    const nextDate = nextWorkingDate(now.date, holidays);
    const nextDayIndex = nextDate.getDay();
    const nextHours = getHoursForDay(config, nextDayIndex);
    const tomorrow = new Date(now.date);

    tomorrow.setHours(0, 0, 0, 0);
    tomorrow.setDate(tomorrow.getDate() + 1);

    if (toDateKey(nextDate) === toDateKey(tomorrow)) {
        return `Najbliższa dostawa jutro od <span>${formatHour(nextHours.start)}</span>`;
    }

    return `Najbliższa dostawa ${formatDate(nextDate)} od <span>${formatHour(nextHours.start)}</span>`;
};

const resolveState = (
    config: DeliveryTimerConfig,
    holidays: Set<string>,
): TimerState => {
    const now = getZonedNow(config.timezone);
    const dayHours = getHoursForDay(config, now.dayIndex);
    const openingTime = new Date(now.date);
    const closingTime = new Date(now.date);

    openingTime.setHours(dayHours.start, 0, 0, 0);
    closingTime.setHours(dayHours.end, 0, 0, 0);

    if (isHoliday(now.dateKey, holidays)) {
        return {
            type: 'message',
            value: nextDeliveryMessage(config, now, holidays, 'next'),
        };
    }

    if (now.date < openingTime) {
        return {
            type: 'message',
            value: nextDeliveryMessage(config, now, holidays, 'today'),
        };
    }

    if (now.date >= closingTime) {
        return {
            type: 'message',
            value: nextDeliveryMessage(config, now, holidays, 'next'),
        };
    }

    return {
        type: 'countdown',
        value: formatDuration(closingTime.getTime() - now.date.getTime()),
    };
};

const initDeliveryTimer = (element: HTMLElement): void => {
    const rawConfig = element.dataset.deliveryTimer;

    if (!rawConfig) {
        return;
    }

    const config = JSON.parse(rawConfig) as DeliveryTimerConfig;
    const holidays = new Set(config.holidays);
    const prompt = element.querySelector<HTMLElement>('.delivery-timer__prompt');
    const time = element.querySelector<HTMLElement>('.delivery-timer__time');
    const next = element.querySelector<HTMLElement>('.delivery-timer__next');

    if (!prompt || !time || !next) {
        return;
    }

    let intervalId: number | null = null;

    const stopInterval = (): void => {
        if (intervalId === null) {
            return;
        }

        window.clearInterval(intervalId);
        intervalId = null;
    };

    const ensureInterval = (): void => {
        if (intervalId !== null) {
            return;
        }

        intervalId = window.setInterval(render, 1000);
    };

    const render = (): void => {
        const state = resolveState(config, holidays);

        if (state.type === 'countdown') {
            ensureInterval();
            prompt.hidden = false;
            time.hidden = false;
            next.hidden = true;
            time.textContent = state.value;
            return;
        }

        stopInterval();
        prompt.hidden = true;
        time.hidden = true;
        next.hidden = false;
        next.innerHTML = state.value;
    };

    render();
};

export const initDeliveryTimers = (): void => {
    document.querySelectorAll<HTMLElement>(SELECTOR).forEach(initDeliveryTimer);
};