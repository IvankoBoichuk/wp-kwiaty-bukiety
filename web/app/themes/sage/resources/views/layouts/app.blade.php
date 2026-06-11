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

  <div id="app" class="container mx-auto px-3">
    <a class="sr-only focus:not-sr-only" href="#main"> {{ __('Skip to content', 'sage-front') }} </a>

    @include('sections.header')

    @yield('content')

    @hasSection('sidebar')
      <aside class="sidebar">
        @yield('sidebar')
      </aside>
    @endif

    @include('sections.footer')
  </div>

  @php(do_action('get_footer'))
  @stack('scripts')
  @php(wp_footer())
</body>
</html>
