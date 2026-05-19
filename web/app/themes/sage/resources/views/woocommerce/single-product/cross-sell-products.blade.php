<h2 class="text-h2 text-green-dark mb-3.5">Dodatki do kwiatów</h2>

<div class="grid grid-cols-2 gap-3">
  @foreach ($additions as $item)
    <div
      class="text-gray-1 group [&.active]:text-white [&.active]:bg-green-easy [&.active]:border-green-easy flex flex-col overflow-hidden rounded-xl border border-[#E0E0D7]"
    >
      {{-- Image --}}
      <img src="{{ $item->image->src() }}" alt="{{ $item->image->alt() }}" class="h-38.5 w-full object-cover" />

      {{-- Content --}}
      <div class="flex flex-1 flex-col justify-between gap-2 p-2">
        <h3 class="text-body-15 mb-2 font-semibold">{{ $item->name }}</h3>

        <div class="flex items-center justify-between">
          <span class="pl-1 text-[16px] font-bold">{!! $item->price !!}</span>

          <button
            type="button"
            class="addition-toggle text-green-default hover:bg-green-default flex items-center justify-center rounded-xl border border-current bg-[#A5AB5F]/25 p-2 transition-all group-[.active]:text-white hover:text-white"
            data-addition-id="{{ $item->id }}"
            data-addition-price="{{ $item->product->get_price() }}"
            data-addition-name="{{ $item->name }}"
            data-selected="false"
          >
            <svg class="addition-icon-plus" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10 4.16663V15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M15.8335 10H4.16683" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            <svg class="addition-icon-minus hidden" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M15.8335 10H4.16683" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  @endforeach
</div>
