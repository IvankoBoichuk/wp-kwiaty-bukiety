@use('App\Catalog\Settings')
<div class="flex flex-col gap-8">
  <div class="grid grid-cols-2 gap-x-3 gap-y-6">
    {{-- Delivery date --}}
    @include('elements.input-date-1',
      [
        'name' => 'delivery_date',
        'min' => date('Y-m-d'),
        'label' => __('Delivery date', 'sage-front'),
        'icon' => 'calendar'
      ])

    {{-- Delivery time --}}
    @include('elements.input-time-1',
      [
        'name' => 'delivery_time',
        'label' => __('Delivery time', 'sage-front'),
        'icon' => 'clock'
      ])

    {{-- Delivery location --}}
    @include('elements.input-select-1',
      [
        'name' => 'delivery_location',
        'label' => __('Delivery location', 'sage-front'),
        'icon' => 'location',
        'options' => Settings::locationsOptions(),
        'class' => 'col-span-2'
      ])

    {{-- Delivery type --}}
    @include('elements.input-radio-btns-1',
      [
        'name' => 'delivery_type',
        'label' => __('Delivery type', 'sage-front'),
        'icon' => 'opened-box',
        'options' => Settings::deliveryOptions(),
        'class' => 'col-span-2'
      ])

    {{-- Deceased's full name --}}
    @include('elements.input-text-1',
      [
        'name' => 'deceased_full_name',
        'label' => __('Deceased\'s full name', 'sage-front'),
        'icon' => 'account',
        'class' => 'col-span-2'
      ])

    {{-- Ribbon message (20 PLN) --}}
    @include('elements.input-text-1',
      [
        'id' => 'card_message_payed',
        'name' => 'card_message',
        'label' => __('Ribbon message (20 PLN)', 'sage-front'),
        'icon' => 'message',
        'class' => 'col-span-2'
      ])

    {{-- Add-ons --}}
    @include('elements.input-checkboxes-products-1',
      [
        'name' => 'addition_ids',
        'label' => __('Add-ons', 'sage-front'),
        'icon' => 'plus',
        'items' => Settings::funeralAdditionsProducts(),
        'class' => 'col-span-2'
      ])
  </div>
</div>
