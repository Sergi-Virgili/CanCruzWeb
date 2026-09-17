<x-layouts.app>
    <form method="POST" action="{{ route('reservations.store') }}" class="max-w-xl mx-auto space-y-6">
        @csrf

        <h1 class="text-2xl font-bold text-center">Solicitud de Reserva</h1>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nombre completo</label>
            <input type="text"
                   id="name"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   maxlength="255"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                          @error('name') border-red-500 @enderror">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
            <input type="email"
                   id="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   maxlength="255"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                          @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="entry_date" class="block text-sm font-medium text-gray-700">Fecha de entrada</label>
                <input type="date"
                       id="entry_date"
                       name="entry_date"
                       value="{{ old('entry_date') }}"
                       required
                       min="{{ now()->toDateString() }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                              @error('entry_date') border-red-500 @enderror">
                @error('entry_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="out_date" class="block text-sm font-medium text-gray-700">Fecha de salida</label>
                <input type="date"
                       id="out_date"
                       name="out_date"
                       value="{{ old('out_date') }}"
                       required
                       min="{{ now()->addDay()->toDateString() }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                              @error('out_date') border-red-500 @enderror">
                @error('out_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="message" class="block text-sm font-medium text-gray-700">Mensaje</label>
            <textarea id="message"
                      name="message"
                      required
                      maxlength="2000"
                      rows="4"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                             @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
            @error('message')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">Máximo 2000 caracteres.</p>
        </div>

        <div class="text-center">
            <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Enviar solicitud
            </button>
        </div>
    </form>
</x-layouts.app>