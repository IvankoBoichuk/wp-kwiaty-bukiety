<!doctype html>
<html @php(language_attributes())>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  @php(do_action('get_header'))
  @php(wp_head())
  @php($webfonts = \Illuminate\Support\Facades\Vite::asset('webfonts.css'))

  <link
    rel="preload"
    as="style"
    href="{{ $webfonts }}"
    onload="
      this.onload = null;
      this.rel = 'stylesheet';
    "
  />
  <noscript><link rel="stylesheet" href="{{ $webfonts }}" /></noscript>
</head>

<body @php(body_class()) x-data="mobileMenu">
  @php(wp_body_open())

  @include('sections.header')
  <div
    x-show="hasDesktopMenuOpen()"
    x-transition:enter="transition-opacity ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="pointer-events-none fixed inset-0 z-50 hidden bg-black/30 lg:block"
    x-cloak
  ></div>
  <main id="main" class="flex grow flex-col gap-12 bg-[#FCF9F6] pt-2.5 pb-12 md:pt-4 lg:gap-25 lg:pt-8 lg:pb-25">
    <a class="sr-only focus:not-sr-only" href="#main"> {{ __('Skip to content', 'sage-front') }} </a>
    @yield('content')
  </main>
  @hasSection('sidebar')
    <aside class="sidebar">
      @yield('sidebar')
    </aside>
  @endif

  @include('sections.footer')

  @php(do_action('get_footer'))
  @stack('scripts')
  @php(wp_footer())
</body>
</html>
