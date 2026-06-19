<div class="border-y border-[#E0E0D7] py-6" x-data="{ open: false }">
  <button
    type="button"
    class="flex w-full items-center justify-between text-left transition-all"
    @click="open = !open"
    :aria-expanded="open"
  >
    <h2 class="h2-mobile text-green-default">{{ __('Product description', 'sage-front') }}</h2>
    <svg
      class="transition-transform duration-300"
      :class="{
        'rotate-180': open,
      }"
      width="24"
      height="24"
      viewBox="0 0 24 24"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path d="M6 9L12 15L18 9" stroke="#969998" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  </button>

  <div class="overflow-hidden transition-all duration-300" x-show="open" x-collapse>
    <div class="prose prose-a:text-[#0885CD] prose-a:no-underline prose-a:hover:underline pt-2">
      {!! $description !!}
    </div>
  </div>
</div>
