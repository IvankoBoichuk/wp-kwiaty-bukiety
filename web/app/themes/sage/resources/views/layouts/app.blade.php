<!doctype html>
<html @php (language_attributes())>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  @php (do_action('get_header'))
  @php (wp_head())
  @php ($webfonts = \Illuminate\Support\Facades\Vite::asset('webfonts.css'))

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
  @vite (['resources/js/app.ts'])
</head>

<body @php (body_class()) x-data="mobileMenu">
  @php (wp_body_open())

  <div id="app">
    <a class="sr-only focus:not-sr-only" href="#main">
      {{ __('Skip to content', 'sage') }}
    </a>

    @include ('sections.header')

    <main
      id="main"
      class="flex grow flex-col gap-12 bg-[#FCF9F6] px-3 pt-2.5 pb-12 md:pt-4 lg:gap-25 lg:pt-8 lg:pb-25"
    >
      @yield ('content')
    </main>

    @hasSection ('sidebar')
      <aside class="sidebar">
        @yield ('sidebar')
      </aside>
    @endif

    @include ('sections.footer')
  </div>

  @php (do_action('get_footer'))
  @php (wp_footer())
</body>
</html>
