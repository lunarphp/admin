<input {{ $attributes->merge([
        'type' => 'text',
        'class' =>
            'form-input block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed',
    ])->class([
        'border-red-400' => !!$error,
    ]) }}
       maxlength="255">
