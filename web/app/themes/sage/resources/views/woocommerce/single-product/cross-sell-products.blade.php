<h2 class="text-h2 text-green-dark mb-3.5">{{ __('Additions to flowers', 'sage-front') }}</h2>
@include('elements.input-checkboxes-products-2',
  [
    'name' => 'addition_ids',
    'items' => $additions
  ])
