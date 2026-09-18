<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Can Cruz - Casa Rural</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="overflow-x-hidden bg-[#EDECEA] font-body text-[#1b1b18]">

    {{-- Hamburger --}}
    <button id="menu-toggle" type="button" aria-label="Abrir menú" aria-controls="sidebar" aria-expanded="false"
            class="fixed right-[30px] top-5 z-[100] cursor-pointer bg-[#1A2332] p-[0.3em] text-white transition-colors hover:bg-[#D5AB3B] hover:text-[#1A2332]">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    {{-- Sidebar --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-[99] w-60 -translate-x-full bg-white shadow-xl transition-transform duration-300 ease-out">
        <nav class="flex flex-col pt-20 text-[#1A2332]">
            <a href="#home" class="border-b border-gray-100 px-5 py-3 hover:bg-gray-100">Home</a>
            <a href="#about" class="border-b border-gray-100 px-5 py-3 hover:bg-gray-100">Sobre Nosotros</a>
            <a href="#reserva" class="border-b border-gray-100 px-5 py-3 font-medium text-[#D5AB3B] hover:bg-gray-100">Reserva</a>
            <a href="#gastronomia" class="border-b border-gray-100 px-5 py-3 hover:bg-gray-100">Gastronomía</a>
            <a href="#suites" class="border-b border-gray-100 px-5 py-3 hover:bg-gray-100">Suites</a>
            <a href="#experiencias" class="border-b border-gray-100 px-5 py-3 hover:bg-gray-100">Experiencias</a>
            <a href="#contacto" class="border-b border-gray-100 px-5 py-3 hover:bg-gray-100">Contacto</a>
        </nav>
    </aside>

    {{-- Hero --}}
    <section id="home"
             class="relative h-[80vh] border-b-[5px] border-[#010035] bg-cover bg-center"
             style="background-image: url('{{ asset('img/bg-Imagen.jpg') }}')">
        <div id="logo" class="absolute left-1/2 top-[10px] max-w-[200px] -translate-x-1/2 bg-[#0a1124]/85 p-5 sm:max-w-none sm:p-[30px]">
            <img src="{{ asset('img/logo3.png') }}" alt="Masia Can Cruz, casa rural" class="w-full">
        </div>
    </section>

    {{-- Overlapping reservation box (white shadow layer + navy layer) --}}
    <div id="reserva" class="relative z-10 -mt-24 px-4">
        <div class="relative mx-auto max-w-2xl">
            <div class="absolute -inset-3 bg-white shadow-[0_0_40px_#646464]"></div>

            <div class="relative bg-[#0a1124]/95 p-8 text-white">
                <h1 class="text-center font-serif text-4xl">Masia Can Cruz</h1>
                <p class="mx-auto mt-3 max-w-xl text-center text-gray-200">
                    Casa rural en el corazón del Montseny. Reserva directamente con nosotros.
                </p>

                @if (session('success'))
                    <div class="mt-6 border border-green-400 bg-green-400/10 p-4 text-green-200">{{ session('success') }}</div>
                @endif
                @if (session('warning'))
                    <div class="mt-6 border border-yellow-400 bg-yellow-400/10 p-4 text-yellow-200">{{ session('warning') }}</div>
                @endif
                @if (session('error'))
                    <div class="mt-6 border border-red-400 bg-red-400/10 p-4 text-red-200">{{ session('error') }}</div>
                @endif

                <div class="mt-6">
                    <x-reservation-form variant="dark" />
                </div>
            </div>
        </div>
    </div>

    {{-- Sobre Nosotros --}}
    <section id="about" class="container mx-auto px-4 py-16">
        <h2 class="mb-8 text-center font-serif text-4xl">Sobre Nosotros</h2>
        <div class="mx-auto max-w-3xl space-y-4 text-gray-700">
            <p>
                Enmarcada por el Parc Natural del Montseny, reserva de la Biosfera por la UNESCO, la Masía
                Can Cruz cuenta con una ubicación y vistas privilegiadas que proporcionan el ambiente ideal
                para quienes buscan bienestar en la naturaleza.
            </p>
            <p>
                Un refugio de piedra y madera donde el tiempo se detiene: senderos entre bosques, silencio,
                cielos estrellados y una hospitalidad cercana que invita a volver.
            </p>
        </div>
    </section>

    {{-- Gastronomía --}}
    <section id="gastronomia" class="container mx-auto px-4 py-16">
        <div class="grid items-center gap-8 md:grid-cols-12">
            <div class="z-10 bg-white p-6 shadow-xl md:col-span-4">
                <div class="mb-4 inline-flex bg-[#0a1124] p-3">
                    <img src="{{ asset('img/capa7.jpg') }}" alt="" class="h-12 w-12">
                </div>
                <h3 class="font-serif text-3xl">Gastronomía</h3>
                <h4 class="mt-2 font-bold uppercase tracking-wide text-[#D5AB3B]">Restaurantes y bares</h4>
                <p class="mt-4 text-gray-600">
                    Con variadas opciones, los huéspedes pueden disfrutar desde el emblemático cordero
                    patagónico hasta la más delicada cocina internacional, y por las tardes deleitarse con el
                    clásico Té Llao Llao.
                </p>
                <a href="#" class="mt-4 inline-block font-medium text-[#1A2332] underline">Ver más</a>
            </div>
            <div class="md:col-span-8">
                <img src="{{ asset('img/capa5.jpg') }}" alt="Gastronomía de proximidad" class="h-auto w-full">
            </div>
        </div>
    </section>

    {{-- Habitaciones y Suites --}}
    <section id="suites" class="container mx-auto px-4 py-16">
        <div class="grid items-center gap-8 md:grid-cols-12">
            <div class="z-10 bg-white p-6 shadow-xl md:col-span-4">
                <h3 class="font-serif text-3xl">Habitaciones y Suites</h3>
                <h4 class="mt-2 font-bold uppercase tracking-wide text-[#D5AB3B]">Lujo y comfort</h4>
                <p class="mt-4 text-gray-600">
                    Nuestra masía dispone de lujosas suites, un gran jardín con piscina exterior, golf y un
                    spa ecológico.
                </p>
                <a href="#" class="mt-4 inline-block font-medium text-[#1A2332] underline">Ver más</a>
            </div>
            <div class="md:col-span-8">
                <img src="{{ asset('img/habita.jpg') }}" alt="Habitaciones y suites" class="h-auto w-full">
            </div>
        </div>
    </section>

    {{-- Experiencias --}}
    <section id="experiencias" class="bg-white py-16">
        <div class="container mx-auto px-4">
            <h2 class="mb-8 text-center font-serif text-4xl">Experiencias</h2>
            <div class="grid gap-4 md:grid-cols-3">
                <figure class="relative">
                    <img src="{{ asset('img/bg-Imagen.jpg') }}" alt="Escapadas en el Montseny" class="h-72 w-full object-cover">
                    <figcaption class="absolute inset-0 flex items-center justify-center bg-black/35 font-serif text-2xl text-white">
                        Escapadas
                    </figcaption>
                </figure>
                <figure class="relative">
                    <img src="{{ asset('img/capa5.jpg') }}" alt="Grupos y eventos" class="h-72 w-full object-cover">
                    <figcaption class="absolute inset-0 flex items-center justify-center bg-black/35 font-serif text-2xl text-white">
                        Grupos y Eventos
                    </figcaption>
                </figure>
                <figure class="relative">
                    <img src="{{ asset('img/huerta.jpg') }}" alt="Actividades en la huerta" class="h-72 w-full object-cover">
                    <figcaption class="absolute inset-0 flex items-center justify-center bg-black/35 font-serif text-2xl text-white">
                        Actividades
                    </figcaption>
                </figure>
            </div>
        </div>
    </section>

    {{-- Footer / Contacto --}}
    <footer id="contacto" class="bg-[#1A2332] py-12 text-white">
        <div class="container mx-auto px-4 text-center">
            <img src="{{ asset('img/logo3.png') }}" alt="Masia Can Cruz" class="mx-auto h-20 w-auto">
            <h2 class="mt-4 font-serif text-3xl">Contacto</h2>
            <p class="mt-3 text-gray-300">Masia Can Cruz · Parc Natural del Montseny</p>

            <div class="mt-6 flex justify-center gap-4">
                <a href="#" aria-label="Instagram" class="p-[0.2em] text-white hover:text-[#D5AB3B]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.8.2 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.2 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.2-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.4-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c.1-1.2.2-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4C8.4 2.2 8.8 2.2 12 2.2zm0 1.8c-3.1 0-3.5 0-4.7.1-.9 0-1.4.2-1.7.3-.4.2-.7.4-1 .7-.3.3-.5.6-.7 1-.1.3-.3.8-.3 1.7-.1 1.2-.1 1.6-.1 4.7s0 3.5.1 4.7c0 .9.2 1.4.3 1.7.2.4.4.7.7 1 .3.3.6.5 1 .7.3.1.8.3 1.7.3 1.2.1 1.6.1 4.7.1s3.5 0 4.7-.1c.9 0 1.4-.2 1.7-.3.4-.2.7-.4 1-.7.3-.3.5-.6.7-1 .1-.3.3-.8.3-1.7.1-1.2.1-1.6.1-4.7s0-3.5-.1-4.7c0-.9-.2-1.4-.3-1.7-.2-.4-.4-.7-.7-1-.3-.3-.6-.5-1-.7-.3-.1-.8-.3-1.7-.3-1.2-.1-1.6-.1-4.7-.1zm0 3.1a4.9 4.9 0 110 9.8 4.9 4.9 0 010-9.8zm0 8.1a3.2 3.2 0 100-6.4 3.2 3.2 0 000 6.4zm6.2-8.3a1.1 1.1 0 11-2.3 0 1.1 1.1 0 012.3 0z"/>
                    </svg>
                </a>
                <a href="#" aria-label="Facebook" class="p-[0.2em] text-white hover:text-[#D5AB3B]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.3-1.5 1.6-1.5h1.6V3.6c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.5-4 4.1v2.3H7.5V13h2.8v8h3.2z"/>
                    </svg>
                </a>
            </div>

            <p class="mt-6 text-sm text-gray-400">© {{ date('Y') }} Masia Can Cruz</p>
        </div>
    </footer>

</body>
</html>
