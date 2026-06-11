<div class="card flex flex-col gap-8">
  {{-- Delivery date --}}
  <div class="flex flex-col gap-3" data-delivery-date-section>
    <div class="text-green-default flex items-center gap-2">
      <svg class="text-gray-3" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M15.8333 3.33331H4.16667C3.24619 3.33331 2.5 4.07951 2.5 4.99998V16.6666C2.5 17.5871 3.24619 18.3333 4.16667 18.3333H15.8333C16.7538 18.3333 17.5 17.5871 17.5 16.6666V4.99998C17.5 4.07951 16.7538 3.33331 15.8333 3.33331Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M13.3335 1.66669V5.00002" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M6.6665 1.66669V5.00002" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M2.5 8.33331H17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <span class="text-body-14">{{ __('Delivery date', 'sage-front') }}</span>
    </div>

    <div
      class="flex gap-1.5"
      :class="$store.productPurchase.deliveryDateError ? 'rounded-2xl ring-1 ring-[#D54C4C] p-1' : ''"
    >
      <button
        type="button"
        class="delivery-date-option text-green-dark text-body-13 hover:bg-green-easy flex flex-1 items-center justify-center gap-1 rounded-xl bg-[#F7F7F6] px-4 py-2.5 transition-all hover:text-white"
        data-date-option="today"
      >
        {{ __('Today', 'sage-front') }}
      </button>
      <button
        type="button"
        class="delivery-date-option text-green-dark text-body-13 hover:bg-green-easy flex flex-1 items-center justify-center gap-1 rounded-xl bg-[#F7F7F6] px-4 py-2.5 transition-all hover:text-white"
        data-date-option="tomorrow"
      >
        {{ __('Tomorrow', 'sage-front') }}
      </button>
      <button
        type="button"
        class="delivery-date-option delivery-date-custom text-green-dark text-body-13 hover:bg-green-easy flex flex-none items-center justify-center gap-1 rounded-xl bg-[#F7F7F6] px-4 py-2.5 transition-all hover:text-white"
        data-date-option="custom"
      >
        <span class="delivery-date-label">{{ __('Custom date', 'sage-front') }}</span>
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 15L12.5 10L7.5 5" stroke="#828282" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
        <input
          type="date"
          class="delivery-date-input pointer-events-none absolute hidden opacity-0"
          data-date-input
          min="{{ date('Y-m-d') }}"
        />
      </button>
    </div>

    <p
      x-show="$store.productPurchase.deliveryDateError"
      x-text="$store.productPurchase.deliveryDateError"
      class="text-[12px] leading-4 text-[#D54C4C]"
    ></p>
  </div>

  {{-- Delivery time --}}
  <div class="flex flex-col gap-3">
    <div class="text-green-default flex items-center gap-2">
      <svg class="text-gray-3" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M9.99984 18.3334C14.6022 18.3334 18.3332 14.6024 18.3332 10C18.3332 5.39765 14.6022 1.66669 9.99984 1.66669C5.39746 1.66669 1.6665 5.39765 1.6665 10C1.6665 14.6024 5.39746 18.3334 9.99984 18.3334Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M10 5V10L13.3333 11.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <span class="text-body-14">{{ __('Delivery time', 'sage-front') }}</span>
    </div>

    <div
      class="flex flex-wrap gap-2"
      :class="$store.productPurchase.deliveryTimeError ? 'rounded-2xl ring-1 ring-[#D54C4C] p-1' : ''"
    >
      @foreach (['08-12', '12-15', '15-18', '18-21'] as $timeSlot)
        <button
          type="button"
          class="delivery-time-option text-body-13 text-green-dark hover:bg-green-easy flex-1 rounded-xl bg-[#F7F7F6] px-4 py-2.5 transition-all hover:text-white"
          data-time-slot="{{ $timeSlot }}"
        >
          {{ $timeSlot }}
        </button>
      @endforeach
    </div>

    <p
      x-show="$store.productPurchase.deliveryTimeError"
      x-text="$store.productPurchase.deliveryTimeError"
      class="text-[12px] leading-4 text-[#D54C4C]"
    ></p>
  </div>

  {{-- Tresc bileciku --}}
  <div class="flex flex-col gap-3">
    <div class="text-green-default flex items-center gap-2">
      <svg class="text-gray-3" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.5 9.58336C17.5029 10.6832 17.2459 11.7683 16.75 12.75C16.162 13.9265 15.2581 14.916 14.1395 15.6078C13.021 16.2995 11.7319 16.6662 10.4167 16.6667C9.31678 16.6696 8.23176 16.4126 7.25 15.9167L2.5 17.5L4.08333 12.75C3.58744 11.7683 3.33047 10.6832 3.33333 9.58336C3.33384 8.26815 3.70051 6.97907 4.39227 5.86048C5.08402 4.7419 6.07355 3.838 7.25 3.25002C8.23176 2.75413 9.31678 2.49716 10.4167 2.50002H10.8333C12.5703 2.59585 14.2109 3.32899 15.441 4.55907C16.671 5.78915 17.4042 7.42973 17.5 9.16669V9.58336Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <span class="text-body-14">{{ __('Card message', 'sage-front') }}</span>
    </div>

    <textarea
      class="text-body-13 text-green-default focus:border-green-easy min-h-23 w-full resize-none rounded-xl border border-[#C7C7C7] bg-transparent px-3 py-2.5 placeholder:text-[#404844] focus:outline-none"
      placeholder="{{ __('Leave empty if you don\'t need a card', 'sage-front') }}"
      name="card-message"
    ></textarea>
  </div>
</div>
