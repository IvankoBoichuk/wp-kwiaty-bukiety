@php
  $cartCount = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
  $bottomNavigation = [
    [
      'id' => 'home',
      'name' => 'Home',
      'type' => 'link',
      'url' => home_url('/'),
      'target' => '_self',
      'count' => 0,
    ],
    [
      'id' => 'search',
      'name' => 'Search',
      'type' => 'button',
      'action' => 'search',
      'count' => 0,
    ],
    [
      'id' => 'cart',
      'name' => 'Cart',
      'type' => 'link',
      'url' => function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart'),
      'target' => '_self',
      'count' => $cartCount,
    ],
    [
      'id' => 'menu',
      'name' => 'Menu',
      'type' => 'button',
      'action' => 'toggleMenu',
      'count' => 0,
    ],
  ];
@endphp

<header class="bg-background sticky top-0 z-50 border-b border-[#426E59]">
  @include('partials.delivery-timer', ['deliveryTimer' => $deliveryTimer])

  <div class="bg-background flex items-center justify-between gap-4 p-3">
    @if (!empty($logos?->dark))
      <a href="{{ home_url('/') }}" class="text-lg font-semibold tracking-[0.16em] text-[#244734] uppercase">
        <img src="{{ $logos->dark->src() }}" alt="{{ $logos->dark->alt() ?? $siteName }}" width="66" height="31" />
      </a>
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
    </div>
  </div>

  @if ($menu)
    <div
      x-show="isOpen"
      @click="closeMenu()"
      x-transition:enter="transition-opacity ease-out duration-300"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition-opacity ease-in duration-200"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
      class="fixed inset-0 z-40 bg-black/50"
      x-cloak
    ></div>
    <div
      id="mobile-navigation"
      x-show="isOpen"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="translate-x-full"
      x-transition:enter-end="translate-x-0"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="translate-x-0"
      x-transition:leave-end="translate-x-full"
      class="bg-background fixed top-0 right-0 bottom-0 z-50 flex w-80 max-w-[85vw] flex-col overflow-y-auto border-l border-[#426E59]"
      x-cloak
    >
      <nav class="flex-1 overflow-y-auto px-4 pt-4 pb-16" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">
        <div x-data="accordion">
          @foreach ($menu as $item)
            <div class="border-b border-gray-200 last:border-b-0">
              @if (!empty($item['children']))
                <button
                  @click="toggle('parent-{{ $loop->index }}')"
                  type="button"
                  class="flex w-full items-center justify-between py-3 text-left font-medium"
                >
                  <span>{{ $item['title'] }}</span>
                  <svg
                    class="size-5 transition-transform"
                    :class="isActive('parent-{{ $loop->index }}') ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                <div x-show="isActive('parent-{{ $loop->index }}')" x-collapse class="pb-2 pl-4">
                  <div x-data="accordion">
                    @foreach ($item['children'] as $child)
                      @if (!empty($child['children']))
                        <div class="my-2 border-l-2 border-gray-300">
                          <button
                            @click="toggle('child-{{ $loop->parent->index }}-{{ $loop->index }}')"
                            type="button"
                            class="flex w-full items-center justify-between py-2 pl-3 text-left"
                          >
                            <span class="text-sm">{{ $child['title'] }}</span>
                            <svg
                              class="size-4 transition-transform"
                              :class="isActive('child-{{ $loop->parent->index }}-{{ $loop->index }}') ? 'rotate-180' : ''"
                              fill="none"
                              stroke="currentColor"
                              viewBox="0 0 24 24"
                            >
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                          </button>

                          <div
                            x-show="isActive('child-{{ $loop->parent->index }}-{{ $loop->index }}')"
                            x-collapse
                            class="pb-2 pl-4"
                          >
                            @foreach ($child['children'] as $subchild)
                              <a
                                href="{{ $subchild['url'] }}"
                                class="hover:text-primary block py-2 pl-3 text-sm text-gray-600"
                                @click="closeMenu()"
                              >
                                {{ $subchild['title'] }}
                              </a>
                            @endforeach
                          </div>
                        </div>
                      @else
                        <a
                          href="{{ $child['url'] }}"
                          class="hover:text-primary block py-2 pl-3 text-sm text-gray-600"
                          @click="closeMenu()"
                        >
                          {{ $child['title'] }}
                        </a>
                      @endif
                    @endforeach
                  </div>
                </div>
              @else
                <a href="{{ $item['url'] }}" class="block py-3 font-medium" @click="closeMenu()">
                  {{ $item['title'] }}
                </a>
              @endif
            </div>
          @endforeach
        </div>
      </nav>
    </div>
  @endif

  <nav class="fixed inset-x-0 bottom-0 z-50 w-full bg-[#072114] px-6 py-3 md:hidden" aria-label="Bottom navigation">
    <ul class="flex items-center justify-between">
      @foreach ($bottomNavigation as $item)
        <li>
          @if ($item['type'] === 'link')
            <a
              href="{{ $item['url'] }}"
              target="{{ $item['target'] }}"
              aria-label="{{ $item['name'] }}"
              class="text-gray-6 relative block text-[14px] leading-4.75"
            >
              @if ($item['id'] === 'home')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="#F2F2F2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M9 22V12H15V22" stroke="#F2F2F2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              @elseif ($item['id'] === 'cart')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M8 22C8.55228 22 9 21.5523 9 21C9 20.4477 8.55228 20 8 20C7.44772 20 7 20.4477 7 21C7 21.5523 7.44772 22 8 22Z" stroke="#F2F2F2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M19 22C19.5523 22 20 21.5523 20 21C20 20.4477 19.5523 20 19 20C18.4477 20 18 20.4477 18 21C18 21.5523 18.4477 22 19 22Z" stroke="#F2F2F2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M2.0498 2.0498H4.0498L6.7098 14.4698C6.80738 14.9247 7.06048 15.3313 7.42552 15.6197C7.79056 15.908 8.24471 16.0602 8.7098 16.0498H18.4898C18.945 16.0491 19.3863 15.8931 19.7408 15.6076C20.0954 15.3222 20.3419 14.9243 20.4398 14.4798L22.0898 7.0498H5.1198" stroke="#F2F2F2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              @endif

              <span
                data-count="{{ $item['count'] }}"
                class="text-background absolute bottom-2 left-2 flex size-5.5 items-center justify-center rounded-full bg-[#EB5757] text-center text-[13px] leading-none font-semibold before:content-[attr(data-count)] data-[count='0']:hidden"
              ></span>
            </a>
          @elseif ($item['type'] === 'button')
            <button
              type="button"
              @click="{{ $item['action'] }}()"
              aria-label="{{ $item['name'] }}"
              class="text-gray-6 relative block text-[14px] leading-4.75"
            >
              @if ($item['id'] === 'search')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="#F2F2F2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M20.9999 20.9999L16.6499 16.6499" stroke="#F2F2F2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              @elseif ($item['id'] === 'menu')
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M4 12H20" stroke="#F2F2F2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M4 6H20" stroke="#F2F2F2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M4 18H20" stroke="#F2F2F2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              @endif
            </button>
          @endif
        </li>
      @endforeach
    </ul>
  </nav>
</header>
