<h2 class="h2-mobile lg:h3-desktop text-green-default mb-6">
  {{ __('Reviews', 'sage-front') }} ({{ count($reviews) }})
</h2>

@if ($reviews && count($reviews) > 0)
  <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
    @foreach ($reviews as $review)
      @include('partials.review-card', ['review' => $review])
    @endforeach
  </div>
  <div class="flex justify-center">
    @include('partials.button',
      [
        'tag' => 'button',
        'text' => __('More Reviews', 'sage-front'),
        'link' => '#reviews',
        'target' => '_self',
        'variant' => 'border',
        'size' => 'lg',
        'showIcon' => false
      ])
  </div>
@endif
