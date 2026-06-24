<div>
  @include('elements.label-product-setting',
    [
      'label' => __('Additions to flowers', 'sage-front'),
      'icon' => 'plus'
    ])
  @include('elements.input-checkboxes-products-2',
    [
      'name' => 'addition_ids',
      'items' => $additions
    ])
</div>
