@if (!empty($deliveryTimer))
  <div
    class="delivery-timer bg-primary flex items-center justify-between gap-4 py-2 pr-11.5 pl-4"
    data-delivery-timer="{!! esc_attr(wp_json_encode($deliveryTimer, JSON_UNESCAPED_UNICODE)) !!}"
  >
    <div class="delivery-timer__prompt flex flex-col">
      <p class="delivery-timer__title text-gray-6 text-[16px] leading-4.75">Dostawa kwiatów nawet dziś</p>
      <span class="delivery-timer__subtitle text-gray-4 text-[14px] leading-3.75">Zamów w ciągu:</span>
    </div>

    <div class="delivery-timer__time text-gray-6 text-[19px] leading-5.25 font-semibold" aria-live="polite">
      02:14:36
    </div>
    <p class="delivery-timer__next text-gray-6 [&>span]:font-medium [&>span]:text-white text-[16px] leading-4.75" hidden></p>
  </div>
@endif
