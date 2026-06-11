<form role="search" method="get" class="search-form" action="{{ home_url('/') }}">
  <label>
    <span class="sr-only"> {{ _x('Search for:', 'label', 'sage-front') }} </span>

    <input
      type="search"
      placeholder="{!! esc_attr_x('Search &hellip;', 'placeholder', 'sage-front') !!}"
      value="{{ get_search_query() }}"
      name="s"
    />
  </label>

  <button>{{ _x('Search', 'submit button', 'sage-front') }}</button>
</form>
