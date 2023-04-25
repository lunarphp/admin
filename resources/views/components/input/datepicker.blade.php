<div
  x-data="{
    value: @entangle($attributes->wire('model')).defer,
    flatpickr: {},
    init() {
      this.$nextTick(() => {

        passedOptions = {{ json_encode($options) }}

        options = {
            altFormat: passedOptions.enableTime ? 'Y-m-d H:i' : 'Y-m-d',
            altInput: true,
        }

        this.flatpickr = flatpickr($refs.input, {...options, ...passedOptions})
      })
    },
    clear() {
        this.flatpickr.setDate(null)
        this.value = null
    }
  }"
  @change="value = $event.target.value"
  class="flex relative"
  wire:ignore
>
  <x-hub::input.text
    x-ref="input"
    type="text"
    x-bind:value="value"
    {{ $attributes->whereDoesntStartWith('wire:model') }}
  />

  <div x-show="value" class="absolute right-0 mr-3">
    <button x-on:click="clear" type="button" class="inline-flex items-center text-sm text-gray-400 hover:text-gray-800">
      <x-hub::icon ref="x-circle" class="w-4 mt-2" />
    </button>
  </div>
</div>
