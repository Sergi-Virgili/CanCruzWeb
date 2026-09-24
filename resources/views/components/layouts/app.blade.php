<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Can Cruz') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @if (request()->routeIs('admin.*'))
        <div class="admin-shell" data-admin-shell>
            <button class="admin-shell__overlay" type="button" data-admin-mobile-overlay aria-label="Cerrar menú" hidden></button>

            <aside class="admin-sidebar" data-admin-sidebar data-collapsed="false">
                <div class="admin-sidebar__brand">
                    <a class="admin-sidebar__wordmark" href="{{ route('admin.dashboard') }}" aria-label="Can Cruz, ir al dashboard">
                        <span class="admin-sidebar__mark" aria-hidden="true">C</span>
                        <span class="admin-sidebar__brand-name">Can <em>Cruz</em></span>
                    </a>
                    <button class="admin-sidebar__toggle" type="button" data-admin-sidebar-toggle aria-expanded="true" aria-controls="admin-navigation" aria-label="Contraer menú">
                        <span aria-hidden="true">‹</span>
                    </button>
                </div>

                <nav class="admin-sidebar__navigation" id="admin-navigation" aria-label="Navegación de administración">
                    <p class="admin-sidebar__label">Gestión</p>
                    <a class="admin-sidebar__link @if (request()->routeIs('admin.dashboard')) is-active @endif" href="{{ route('admin.dashboard') }}" aria-label="Dashboard" title="Dashboard" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z" /></svg>
                        <span>Dashboard</span>
                    </a>
                    <a class="admin-sidebar__link @if (request()->routeIs('admin.reservations.*')) is-active @endif" href="{{ route('admin.reservations.index') }}" aria-label="Reservas" title="Reservas" @if (request()->routeIs('admin.reservations.*')) aria-current="page" @endif>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 5h14v14H5zM8 3v4M16 3v4M5 10h14M8 14h3M8 17h6" /></svg>
                        <span>Reservas</span>
                        @if (($pendingCount ?? 0) > 0)
                            <span class="admin-sidebar__badge">{{ $pendingCount }}</span>
                        @endif
                    </a>
                    <a class="admin-sidebar__link @if (request()->routeIs('admin.calendar.*')) is-active @endif" href="{{ route('admin.calendar.index') }}" aria-label="Calendario" title="Calendario" @if (request()->routeIs('admin.calendar.*')) aria-current="page" @endif>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 5h14v14H5zM8 3v4M16 3v4M5 10h14M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01" /></svg>
                        <span>Calendario</span>
                    </a>
                </nav>

                <div class="admin-sidebar__footer">
                    <div class="admin-sidebar__account">
                        <span class="admin-sidebar__account-mark" aria-hidden="true">{{ auth()->user()->name ? str(auth()->user()->name)->substr(0, 1)->upper() : 'A' }}</span>
                        <span class="admin-sidebar__account-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="admin-sidebar__logout" type="submit" aria-label="Cerrar sesión" title="Cerrar sesión">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M10 5H5v14h5M14 8l4 4-4 4M9 12h9" /></svg>
                            <span>Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </aside>

            <main class="admin-main">
                <header class="admin-header">
                    <button class="admin-header__mobile-toggle" type="button" data-admin-mobile-toggle aria-expanded="false" aria-controls="admin-navigation" aria-label="Abrir menú">
                        <span aria-hidden="true">☰</span>
                    </button>
                    <div>
                        <p class="admin-header__eyebrow">Panel de administración</p>
                        <h1 class="admin-header__title">
                            @if (request()->routeIs('admin.dashboard'))
                                Dashboard
                            @elseif (request()->routeIs('admin.calendar.*'))
                                Calendario
                            @elseif (request()->routeIs('admin.reservations.edit'))
                                Editar reserva
                            @else
                                Reservas
                            @endif
                        </h1>
                    </div>
                </header>

                <div class="admin-content">
                    @include('components.layouts.flash-messages')
                    {{ $slot }}
                </div>
            </main>
        </div>
    @else
        <main class="container mx-auto px-4 py-8">
            @include('components.layouts.flash-messages')
            {{ $slot }}
        </main>
    @endif
</body>
</html>
