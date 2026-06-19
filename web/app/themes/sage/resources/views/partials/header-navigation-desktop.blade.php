<div
  class="relative"
  @mouseleave="closeDesktopMenu()"
  @focusout="if (!$el.contains($event.relatedTarget)) closeDesktopMenu();"
>
  <nav aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">
    <ul class="flex items-center justify-center gap-8 text-[13px] font-semibold text-[#244734] uppercase">
      @foreach ($menu as $item)
        <li>
          @if (!empty($item['children']))
            <a
              href="{{ $item['url'] }}"
              @mouseenter="openDesktopMenu('desktop-menu-{{ $loop->index }}')"
              @focusin="openDesktopMenu('desktop-menu-{{ $loop->index }}')"
              class="relative z-50 flex items-center gap-2 py-3 transition-colors duration-200 hover:text-[#426E59] focus:text-[#426E59] focus:outline-none"
            >
              <span>{{ $item['title'] }}</span>
              <svg
                class="size-4 transition-transform duration-200"
                :class="isDesktopMenuActive('desktop-menu-{{ $loop->index }}') ? 'rotate-180' : ''"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </a>
          @else
            <a
              href="{{ $item['url'] }}"
              class="block py-3 transition-colors duration-200 hover:text-[#426E59] focus:text-[#426E59] focus:outline-none"
            >
              {{ $item['title'] }}
            </a>
          @endif
        </li>
      @endforeach
    </ul>
  </nav>

  <div class="absolute top-full left-1/2 z-50 w-screen -translate-x-1/2">
    @foreach ($menu as $item)
      @if (!empty($item['children']))
        <div
          x-show="isDesktopMenuActive('desktop-menu-{{ $loop->index }}')"
          x-transition:enter="transition-opacity ease-out duration-200"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition-opacity ease-in duration-150"
          x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"
          class="bg-background border border-[#C7D7CF] px-10 py-9 shadow-[0_24px_60px_rgba(36,71,52,0.14)]"
          x-cloak
        >
          <div class="mx-auto w-full max-w-280">
            <div class="mb-8 flex items-end justify-between gap-8 border-b border-[#D7E1DB] pb-6">
              <div class="space-y-2">
                <p class="text-[11px] font-semibold tracking-[0.24em] text-[#6D8577] uppercase">{{ __('Explore', 'sage-back') }}</p>
                <a
                  href="{{ $item['url'] }}"
                  class="block text-[32px] leading-none font-semibold text-[#244734] normal-case transition-colors duration-200 hover:text-[#426E59]"
                >
                  {{ $item['title'] }}
                </a>
              </div>

              <a
                href="{{ $item['url'] }}"
                class="shrink-0 border-b border-[#244734] pb-1 text-[12px] font-semibold tracking-[0.14em] text-[#244734] uppercase transition-colors duration-200 hover:border-[#426E59] hover:text-[#426E59]"
              >
                {{ __('View all', 'sage-back') }}
              </a>
            </div>

            <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_20rem] xl:items-start">
              <ul class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($item['children'] as $child)
                  <li
                    class="border-l border-[#D7E1DB] pl-5 first:border-l-0 first:pl-0 md:first:border-l md:first:pl-5 xl:first:border-l-0 xl:first:pl-0"
                  >
                    @if (!empty($child['children']))
                      <div class="flex h-full flex-col gap-4">
                        <a
                          href="{{ $child['url'] }}"
                          class="block text-[13px] font-semibold tracking-[0.16em] text-[#244734] uppercase transition-colors duration-200 hover:text-[#426E59]"
                        >
                          {{ $child['title'] }}
                        </a>

                        <ul class="space-y-3 text-[15px] leading-6 font-medium normal-case">
                          @foreach ($child['children'] as $subchild)
                            <li>
                              <a
                                href="{{ $subchild['url'] }}"
                                class="block text-[#5B6F62] transition-colors duration-200 hover:text-[#244734]"
                              >
                                {{ $subchild['title'] }}
                              </a>
                            </li>
                          @endforeach
                        </ul>
                      </div>
                    @else
                      <a
                        href="{{ $child['url'] }}"
                        class="flex h-full min-h-32 flex-col justify-between border border-[#D7E1DB] px-5 py-5 text-[#244734] transition-colors duration-200 hover:border-[#426E59] hover:text-[#426E59]"
                      >
                        <span class="text-[13px] font-semibold tracking-[0.16em] uppercase">{{ $child['title'] }}</span>
                        <span
                          class="text-[15px] leading-6 font-medium text-[#5B6F62] normal-case"
                          >{{ __('Browse section', 'sage-back') }}</span
                        >
                      </a>
                    @endif
                  </li>
                @endforeach
              </ul>

              <aside class="bg-[#F5F1EA] px-6 py-7 text-[#244734]">
                <p class="mb-3 text-[11px] font-semibold tracking-[0.22em] text-[#6D8577] uppercase">{{ __('Curated selection', 'sage-back') }}</p>
                <h3 class="mb-4 text-[24px] leading-7 font-semibold normal-case">{{ $item['title'] }}</h3>
                <p class="mb-8 text-[15px] leading-6 text-[#5B6F62]">
                  {{
                    __(
                      'Discover featured categories, seasonal picks, and the most popular directions in this section.',
                      'sage-back',
                    )
                  }}
                </p>

                <div class="mb-8 space-y-3 border-t border-[#D7E1DB] pt-5">
                  <div class="flex items-baseline justify-between gap-4">
                    <span
                      class="text-[11px] font-semibold tracking-[0.18em] text-[#6D8577] uppercase"
                      >{{ __('Sections', 'sage-back') }}</span
                    >
                    <span class="text-[28px] leading-none font-semibold">{{ count($item['children']) }}</span>
                  </div>
                  <p class="text-[14px] leading-5 text-[#5B6F62]">
                    {{
                      __(
                        'A broader overview of the catalogue with direct access to key destinations.',
                        'sage-back',
                      )
                    }}
                  </p>
                </div>

                <a
                  href="{{ $item['url'] }}"
                  class="inline-flex items-center gap-3 border-b border-[#244734] pb-1 text-[12px] font-semibold tracking-[0.14em] text-[#244734] uppercase transition-colors duration-200 hover:border-[#426E59] hover:text-[#426E59]"
                >
                  <span>{{ __('Open section', 'sage-back') }}</span>
                  <span aria-hidden="true">&rarr;</span>
                </a>
              </aside>
            </div>
          </div>
        </div>
      @endif
    @endforeach
  </div>
</div>
