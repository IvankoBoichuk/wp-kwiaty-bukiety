@php
  $placeholderImage = function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src('woocommerce_thumbnail') : '';
  $fields = $cartCheckout['fields'] ?? [];
  $paymentGateways = $cartCheckout['paymentGateways'] ?? [];
  $canUseCoupons = function_exists('wc_coupons_enabled') && wc_coupons_enabled();

  remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);

  $renderField = static function (string $key) use ($fields, $checkoutInstance) {
    if (!($checkoutInstance instanceof \WC_Checkout) || empty($fields[$key])) {
      return '';
    }

    $field = $fields[$key];

    return woocommerce_form_field($key, $field, $field['value'] ?? $checkoutInstance->get_value($key));
  };

  $renderFieldWithError = static function (string $key) use ($renderField) {
    $fieldMarkup = $renderField($key);

    if ($fieldMarkup === '') {
      return '';
    }

    return '<div>' .
      $fieldMarkup .
      '<p class="mt-1 text-[12px] leading-4 text-[#C6463D]" x-cloak x-show="$store.cartCheckout.validationErrors[' .
      "'{$key}'" .
      ']" x-text="$store.cartCheckout.validationErrors[' .
      "'{$key}'" .
      ']"></p></div>';
  };
@endphp

@php
  $orderButtonText = apply_filters('woocommerce_order_button_text', __('Buy and pay', 'sage-front'));
@endphp

@php
  do_action('woocommerce_before_cart');
@endphp

<section class="bg-background">
  <div class="flex w-full flex-col pt-4" x-data>
    @php
      woocommerce_output_all_notices();
    @endphp

    <script>
      window.cartCheckoutConfig = @json($cartCheckout['config'] ?? []);
    </script>

    <div class="shadow-card mb-4 grid grid-cols-3 gap-2 rounded-full bg-white/80 p-1">
      <button
        type="button"
        class="cart-checkout-step-tab rounded-full px-3 py-2 text-[13px] font-semibold transition"
        :class="$store.cartCheckout.currentStep === 1 ? 'bg-green-dark text-white' : 'text-green-default'"
        @click="$store.cartCheckout.goToStep(1)"
      >
        Cart
      </button>
      <button
        type="button"
        class="cart-checkout-step-tab rounded-full px-3 py-2 text-[13px] font-semibold transition"
        :class="$store.cartCheckout.currentStep === 2 ? 'bg-green-dark text-white' : 'text-green-default'"
        :disabled="$store.cartCheckout.isCartEmpty"
        @click="$store.cartCheckout.goToStep(2)"
      >
        Info
      </button>
      <button
        type="button"
        class="cart-checkout-step-tab rounded-full px-3 py-2 text-[13px] font-semibold transition"
        :class="$store.cartCheckout.currentStep === 3 ? 'bg-green-dark text-white' : 'text-green-default'"
        :disabled="$store.cartCheckout.isCartEmpty"
        @click="$store.cartCheckout.goToStep(3)"
      >
        Payment
      </button>
    </div>

    <form
      id="sage-cart-checkout-form"
      method="post"
      action="{{ esc_url($cartCheckout['checkoutUrl'] ?? wc_get_checkout_url()) }}"
      @submit.prevent="$store.cartCheckout.submitOrder($event)"
      class="checkout woocommerce-checkout"
      enctype="multipart/form-data"
    >
      @php
        do_action('woocommerce_before_checkout_form', $checkoutInstance);
      @endphp
      <input type="hidden" name="ship_to_different_address" value="1" />
      {!!
        wp_nonce_field(
          'woocommerce-process_checkout',
          'woocommerce-process-checkout-nonce',
        )
      !!}

      <div class="relative">
        <div
          x-cloak
          x-show="$store.cartCheckout.currentStep === 1"
          class="cart-checkout-step-panel"
          x-transition:enter="transform-gpu transition duration-350 ease-[cubic-bezier(0.22,1,0.36,1)]"
          x-transition:enter-start="translate-y-5 scale-[0.985] opacity-0"
          x-transition:enter-end="translate-y-0 scale-100 opacity-100"
          x-transition:leave="pointer-events-none absolute inset-0 transform-gpu transition duration-180 ease-in"
          x-transition:leave-start="translate-y-0 scale-100 opacity-100"
          x-transition:leave-end="-translate-y-3 scale-[0.99] opacity-0"
        >
          <header class="mb-5 flex items-center justify-between">
            <h1 class="text-h2">Koszyk (<span x-text="$store.cartCheckout.cartCount"></span>)</h1>
          </header>
          @if (!empty($cartCheckout['deliverySummary']))
            <p class="text-green-default/75 mb-4 text-[14px] leading-5" x-show="!$store.cartCheckout.isCartEmpty">
              {{ $cartCheckout['deliverySummary'] }}
            </p>
          @endif

          <div class="space-y-4" x-show="!$store.cartCheckout.isCartEmpty">
            <template x-for="item in $store.cartCheckout.items" :key="item.key">
              <article class="flex gap-3 border-b border-[#E9E1D8] pb-4">
                <div class="h-28 w-28 shrink-0 overflow-hidden rounded-sm bg-[#EFEAE3]">
                  <img
                    :src="item.image || '{{ esc_js($placeholderImage) }}'"
                    :alt="item.imageAlt || item.name"
                    class="h-full w-full object-cover"
                  />
                </div>

                <div class="min-w-0 flex-1">
                  <div class="mb-3 flex items-start gap-3">
                    <div class="min-w-0 flex-1">
                      <h2 class="text-green-default mb-1 text-[16px] leading-5 font-semibold" x-text="item.name"></h2>
                      <p
                        class="text-green-default/75 text-[14px] leading-5"
                        x-show="item.summary"
                        x-text="item.summary"
                      ></p>
                    </div>

                    <button
                      type="button"
                      class="text-green-easy text-[24px] leading-none"
                      :disabled="$store.cartCheckout.isLoading"
                      @click="$store.cartCheckout.removeItem(item.key)"
                      aria-label="Remove item"
                    >
                      ×
                    </button>
                  </div>

                  <div class="flex items-end justify-between gap-3">
                    <div class="text-green-dark text-[16px] font-semibold" x-text="item.lineTotal.formatted"></div>

                    <div class="flex items-center gap-3">
                      <button
                        type="button"
                        class="text-green-easy border-green-easy inline-flex h-11 w-11 items-center justify-center rounded-[14px] border text-[28px] leading-none"
                        :disabled="$store.cartCheckout.isLoading"
                        @click="$store.cartCheckout.updateQuantity(item.key, item.quantity - 1)"
                      >
                        −
                      </button>

                      <span
                        class="text-green-dark min-w-4 text-center text-[20px] font-semibold"
                        x-text="item.quantity"
                      ></span>

                      <button
                        type="button"
                        class="text-green-easy border-green-easy inline-flex h-11 w-11 items-center justify-center rounded-[14px] border text-[28px] leading-none"
                        :disabled="$store.cartCheckout.isLoading"
                        @click="$store.cartCheckout.updateQuantity(item.key, item.quantity + 1)"
                      >
                        +
                      </button>
                    </div>
                  </div>
                </div>
              </article>
            </template>
          </div>

          <div class="card text-center" x-show="$store.cartCheckout.isCartEmpty">
            <h2 class="text-green-default mb-3 text-[20px] font-semibold">Koszyk jest pusty</h2>
            <a
              href="{{ esc_url(wc_get_page_permalink('shop')) }}"
              class="border-green-default bg-green-dark inline-flex rounded-full border px-6 py-3 text-[13px] font-semibold text-white"
            >
              Wroc do zakupow
            </a>
          </div>
        </div>

        <div
          x-cloak
          x-show="$store.cartCheckout.currentStep === 2"
          class="cart-checkout-step-panel"
          data-cart-checkout-info-step
          x-transition:enter="transform-gpu transition duration-350 ease-[cubic-bezier(0.22,1,0.36,1)]"
          x-transition:enter-start="translate-y-5 scale-[0.985] opacity-0"
          x-transition:enter-end="translate-y-0 scale-100 opacity-100"
          x-transition:leave="pointer-events-none absolute inset-0 transform-gpu transition duration-180 ease-in"
          x-transition:leave-start="translate-y-0 scale-100 opacity-100"
          x-transition:leave-end="-translate-y-3 scale-[0.99] opacity-0"
        >
          <header class="mb-5">
            <h1 class="text-h2">Złożenie zamówienia</h1>
          </header>

          <div
            class="space-y-3.5"
            @input="$store.cartCheckout.clearFieldError($event.target.name)"
            @change="$store.cartCheckout.clearFieldError($event.target.name)"
          >
            <section class="card">
              <h2 class="text-green-default mb-4 text-[18px] leading-6 font-semibold">Dane odbiorcy</h2>
              <div class="grid gap-3">
                {!! $renderFieldWithError('shipping_type_of_place') !!} {!! $renderFieldWithError('shipping_address_1') !!} {!! $renderFieldWithError('shipping_postcode') !!} {!! $renderFieldWithError('shipping_city') !!} {!! $renderFieldWithError('shipping_first_name') !!} {!! $renderFieldWithError('shipping_place_name') !!} {!! $renderFieldWithError('shipping_phone') !!}
              </div>
            </section>

            <section class="card">
              <h2 class="text-green-default mb-4 text-[18px] leading-6 font-semibold">Dane nadawcy</h2>
              <div class="grid gap-3">
                {!! $renderFieldWithError('billing_first_name') !!} {!! $renderFieldWithError('billing_last_name') !!} {!! $renderFieldWithError('billing_phone') !!} {!! $renderFieldWithError('billing_email') !!} {!! $renderFieldWithError('billing_nip') !!}
              </div>
            </section>

            <section class="card">
              <h2 class="text-green-default mb-4 text-[18px] leading-6 font-semibold">Uwagi dotyczące dostawy</h2>
              <div class="grid gap-3">{!! $renderFieldWithError('order_comments') !!}</div>
            </section>
          </div>
        </div>

        <div
          x-cloak
          x-show="$store.cartCheckout.currentStep === 3"
          class="cart-checkout-step-panel"
          x-transition:enter="transform-gpu transition duration-350 ease-[cubic-bezier(0.22,1,0.36,1)]"
          x-transition:enter-start="translate-y-5 scale-[0.985] opacity-0"
          x-transition:enter-end="translate-y-0 scale-100 opacity-100"
          x-transition:leave="pointer-events-none absolute inset-0 transform-gpu transition duration-180 ease-in"
          x-transition:leave-start="translate-y-0 scale-100 opacity-100"
          x-transition:leave-end="-translate-y-3 scale-[0.99] opacity-0"
        >
          <header class="mb-5">
            <h1 class="text-h2">Twoje zamówienie</h1>
          </header>

          @if ($canUseCoupons)
            <section class="card mb-4">
              <h2 class="text-green-default mb-4 text-[18px] leading-6 font-semibold">Kod rabatowy</h2>
              @php
                woocommerce_checkout_coupon_form();
              @endphp
            </section>
          @endif

          <section class="mb-5">
            <div class="space-y-3">
              <template x-for="item in $store.cartCheckout.items" :key="`summary-${item.key}`">
                <div
                  class="flex items-start justify-between gap-3 border-b border-[#E9E1D8] pb-3 last:border-b-0 last:pb-0"
                >
                  <div class="text-green-default min-w-0 flex-1 text-[14px] leading-5">
                    <span class="text-green-easy mr-2" x-text="`x${item.quantity}`"></span>
                    <span x-text="item.name"></span>
                  </div>
                  <div
                    class="text-green-default text-[14px] leading-5 font-semibold"
                    x-text="item.lineTotal.formatted"
                  ></div>
                </div>
              </template>
            </div>

            <div class="text-green-default mt-3 space-y-3 border-t border-[#E0E0D7] pt-4 text-[15px] leading-5">
              <div class="flex items-center justify-between">
                <span x-text="$store.cartCheckout.totals.shipping.label"></span>
                <span x-text="$store.cartCheckout.totals.shipping.amount.formatted"></span>
              </div>
              <div class="flex items-center justify-between">
                <span x-text="$store.cartCheckout.totals.discount.label"></span>
                <span x-text="$store.cartCheckout.totals.discount.amount.formatted"></span>
              </div>
              <div class="flex items-center justify-between border-y border-[#E0E0D7] py-4 font-bold">
                <span x-text="$store.cartCheckout.totals.total.label"></span>
                <span x-text="$store.cartCheckout.totals.total.amount.formatted"></span>
              </div>
            </div>
          </section>

          <section class="card">
            <h2 class="text-green-default mb-4 text-[18px] leading-6 font-semibold">Forma płatności</h2>

            @foreach ($paymentGateways as $gateway)
              @php
                if (!($gateway instanceof \WC_Payment_Gateway)) {
                  continue;
                }

                ob_start();
                $gateway->payment_fields();
                $paymentFieldsMarkup = trim((string) ob_get_clean());
              @endphp
              <div class="mb-4 last:mb-0">
                <label class="flex cursor-pointer items-center gap-3 px-2 py-2">
                  <input
                    type="radio"
                    name="payment_method"
                    value="{{ esc_attr($gateway->id) }}"
                    x-model="$store.cartCheckout.selectedPaymentMethod"
                    @checked(($cartCheckout['selectedPaymentMethod'] ?? '') === $gateway->id)
                    class="border-green-easy accent-green-easy size-4"
                  />
                  <div
                    class="text-green-default leading-6"
                    :class="$store.cartCheckout.selectedPaymentMethod === '{{ esc_js($gateway->id) }}' ? 'text-[15px] font-bold' : ''"
                  >
                    {!! wp_kses_post($gateway->get_title()) !!}
                  </div>
                </label>
                <div>
                  @if ($paymentFieldsMarkup !== '')
                    <div
                      class="pay-method text-green-default/80 text-[13px] leading-5"
                      x-show="$store.cartCheckout.selectedPaymentMethod === '{{ esc_js($gateway->id) }}'"
                    >
                      {!! $paymentFieldsMarkup !!}
                    </div>
                  @endif
                </div>
              </div>
            @endforeach

            @php
              do_action('woocommerce_checkout_terms_and_conditions');
            @endphp
            <div class="sr-only">
              <input type="hidden" name="woocommerce_checkout_update_totals" value="1" />
            </div>
          </section>
          @php
            do_action('woocommerce_after_checkout_form', $checkoutInstance);
          @endphp
        </div>

        <div
          x-data="{ hasCartCheckout: Boolean(window.cartCheckoutConfig) }"
          x-cloak
          x-show="hasCartCheckout"
          class="sticky bottom-(--header-bottom-height) mt-5 flex items-center justify-between gap-5 py-3"
        >
          <div class="mx-auto flex w-full max-w-md items-center gap-4">
            <div
              class="min-w-0 flex-1"
              x-show="$store.cartCheckout.currentStep === 1 && !$store.cartCheckout.isCartEmpty"
            >
              <div class="text-green-default text-[14px] leading-5">Total:</div>
              <div class="text-green-dark text-[18px] font-semibold" x-text="$store.cartCheckout.formattedTotal"></div>
            </div>

            <button
              type="button"
              class="bg-purple-dark flex-1 rounded-full px-6 py-4 text-[13px] font-semibold text-white transition disabled:opacity-60"
              x-show="$store.cartCheckout.currentStep === 1 && !$store.cartCheckout.isCartEmpty"
              :disabled="$store.cartCheckout.isLoading"
              @click="$store.cartCheckout.goToStep(2)"
            >
              Checkout
            </button>

            <button
              type="button"
              class="bg-purple-dark flex-1 rounded-full px-6 py-4 text-[13px] font-semibold text-white transition disabled:opacity-60"
              x-show="$store.cartCheckout.currentStep === 2"
              @click="
                if ($store.cartCheckout.validateInfoStep()) {
                  $store.cartCheckout.goToStep(3);
                }
              "
            >
              Przejdź do płatności
            </button>

            <button
              type="submit"
              form="sage-cart-checkout-form"
              class="bg-purple-dark flex-1 rounded-full px-6 py-4 text-[13px] font-semibold text-white transition disabled:opacity-60"
              x-show="$store.cartCheckout.currentStep === 3"
              :disabled="$store.cartCheckout.isSubmitting"
            >
              <span
                x-text="$store.cartCheckout.isSubmitting ? 'Przetwarzanie...' : '{{ esc_js($orderButtonText) }}'"
              ></span>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

@php
  do_action('woocommerce_after_cart');
@endphp
