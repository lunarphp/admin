<div class="shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
        <header class="flex items-center justify-between">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                {{ __('adminhub::partials.products.collections.heading') }}
            </h3>

            @livewire('hub.components.collection-search', [
                'existing' => $product->collections,
            ])
        </header>

        <x-hub::errors :error="$errors->first('collections')"/>

        <div class="space-y-2">
            @foreach ($collections as $index => $collection)
                <div wire:key="collection_{{ $index }}">
                    <div class="flex items-center px-4 py-2 text-sm border rounded">
                        @if ($collection['thumbnail'])
                            <span class="flex-shrink-0 block w-12 mr-4">
                                <img src="{{ $collection['thumbnail'] }}"
                                     class="rounded">
                            </span>
                        @endif

                        <div class="flex grow">
                            <div class="grow flex gap-1.5 flex-wrap items-center">
                                <strong class="rounded px-1.5 py-0.5 bg-sky-50 text-xs text-sky-500">
                                    {{ $collection['group_name'] }}
                                </strong>

                                @if (count($collection['breadcrumb']))
                                    <span class="text-gray-500 flex gap-1.5 items-center">
                                        <span class="font-medium">
                                            {{ collect($collection['breadcrumb'])->first() }}
                                        </span>

                                        <x-hub::icon ref="chevron-right"
                                                     class="w-4 h-4"
                                                     style="solid" />
                                    </span>
                                @endif

                                @if (count($collection['breadcrumb']) > 1)
                                    <span class="text-gray-500 flex gap-1.5 items-center">
                                        <x-hub::tooltip text="{{ collect($collection['breadcrumb'])->implode(' > ') }}">
                                            <span class="font-medium cursor-help">...</span>
                                        </x-hub::tooltip>

                                        <x-hub::icon ref="chevron-right"
                                                     class="w-4 h-4"
                                                     style="solid" />
                                    </span>
                                @endif

                                <strong class="text-gray-700 truncate max-w-[40ch]"
                                        title="{{ $collection['name'] }}">
                                    {{ $collection['name'] }}
                                </strong>
                            </div>

                            <div class="flex items-center">
                                <x-hub::dropdown minimal>
                                    <x-slot name="options">
                                        <x-hub::dropdown.link class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50"
                                                              :href="route('hub.collections.show', [
                                                                  'group' => $collection['group_id'],
                                                                  'collection' => $collection['id'],
                                                              ])">
                                            {{ __('adminhub::partials.products.collections.view_collection') }}
                                        </x-hub::dropdown.link>

                                        <x-hub::dropdown.button wire:click.prevent="removeCollection({{ $index }})"
                                                                class="flex items-center justify-between px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                            {{ __('adminhub::global.remove') }}
                                        </x-hub::dropdown.button>
                                    </x-slot>
                                </x-hub::dropdown>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
