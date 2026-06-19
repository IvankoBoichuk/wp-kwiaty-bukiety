<header id="header" class="bg-background sticky top-0 z-60 border-b border-[#426E59]">
  @include('partials.delivery-timer', ['deliveryTimer' => $deliveryTimer])
  @php($isMobileNavigation = wp_is_mobile())

  <div
    class="bg-background bx-container flex items-center justify-between gap-4 py-3 {{ $menu && !$isMobileNavigation ? 'lg:grid lg:grid-cols-[auto_1fr_auto] lg:gap-8' : '' }}"
  >
    @if (!empty($logos?->dark))
      <a href="{{ home_url('/') }}" class="text-lg font-semibold tracking-[0.16em] text-[#244734] uppercase">
        <img src="{{ $logos->dark->src() }}" alt="{{ $logos->dark->alt() ?? $siteName }}" width="66" height="31" />
      </a>
    @endif

    @if ($menu)
      @unless ($isMobileNavigation)
        @include('partials.header-navigation-desktop', ['menu' => $menu])
      @endunless
    @endif

    <div class="flex items-center gap-3">
      @if (!empty($phone))
        <a
          href="{{ $phone['href'] }}"
          class="border-green-default bg-background text-green-default inline-flex items-center justify-center rounded-full border-2 px-5.5 py-1.5 text-[14px] font-semibold transition-all duration-200"
        >
          <span>{{ $phone['value'] }}</span>
        </a>
      @endif

      @if ($menu && $isMobileNavigation)
        @include('partials.header-navigation-mobile', ['menu' => $menu])
      @endif
    </div>
  </div>
</header>
