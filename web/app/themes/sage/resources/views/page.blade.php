@extends('layouts.app')

@section('content')
  <main id="main" class="flex grow flex-col gap-12 bg-[#FCF9F6] px-3 pt-2.5 pb-12 md:pt-4 lg:gap-25 lg:pt-8 lg:pb-25">
    @while (have_posts())
      @php(the_post())
      @includeFirst(['partials.content-page', 'partials.content'])
    @endwhile
  </main>
@endsection
