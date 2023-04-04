<div class="space-y-4">
  {{ $this->addresses->links() }}
  <div class="grid gap-4 text-sm md:grid-cols-2">
    @foreach($this->addresses as $address)
      <div wire:key="address_{{ $address->id }}" class="leading-relaxed bg-white rounded shadow">
        <div class="flex justify-between px-4 py-3 rounded-t bg-gray-50">
          <div class="space-x-1">
            @if($address->billing_default)
              <span class="px-3 py-1 text-xs text-sky-500 bg-sky-50">Billing Default</span>
            @endif
            @if($address->shipping_default)
              <span class="px-3 py-1 text-xs text-green-600 bg-green-50">Shipping Default</span>
            @endif
          </div>
          <div class="flex space-x-4">
            <x-hub::button theme="gray" size="xs" wire:click.prevent="$set('addressIdToEdit', '{{ $address->id }}')">Edit</x-hub::button>

            <x-hub::button theme="danger" size="xs" wire:click.prevent="$set('addressToRemove', '{{ $address->id }}')">
              {{ __('adminhub::components.customers.show.remove_address_btn') }}
            </x-hub::button>
          </div>
        </div>

        <div class="p-4">
          <span class="block">{{ $address->first_name }} {{ $address->last_name }}</span>

          @if($address->company_name)
            <span class="block">{{ $address->company_name }}
          @endif

          <span class="block">{{ $address->line_one }}</span>
          @if($address->line_two)
            <span class="block">{{ $address->line_two }}
          @endif
          @if($address->line_three)
            <span class="block">{{ $address->line_three }}</span>
          @endif
          <span class="block">{{ $address->city }}</span>
          <span class="block">{{ $address->state }}</span>
          <span class="block">{{ $address->postcode }}</span>
          <span class="block">{{ $address->country?->name }}</span>
          <span class="block">{{ $address->contact_email }}</span>
          <span class="block">{{ $address->contact_phone }}</span>
        </div>
      </div>
    @endforeach
  </div>
</div>
