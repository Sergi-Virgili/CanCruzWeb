<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Can Cruz | Acceso</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <a class="login-page__skip-link" href="#login-form">Ir al formulario</a>

    <div class="login-page__shell">
        <header class="login-page__header">
            <a class="login-page__wordmark" href="{{ route('home') }}" aria-label="Can Cruz, volver a la web">
                <span class="login-page__mark" aria-hidden="true">C</span>
                <span>Can <em>Cruz</em></span>
            </a>

            <a class="login-page__home-link" href="{{ route('home') }}">Volver a la web</a>
        </header>

        <main class="login-page__main">
            <section class="login-card" aria-labelledby="login-title">
                <div class="login-card__intro">
                    <p class="login-card__eyebrow">Área privada</p>
                    <h1 id="login-title">Bienvenido de nuevo</h1>
                    <p>Accede al panel para gestionar las reservas de Can Cruz.</p>
                </div>

                @if ($errors->any())
                    <div class="login-alert" id="login-errors" role="alert" aria-live="polite">
                        <p>Revisa los datos e inténtalo de nuevo.</p>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="login-form" id="login-form" method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="login-form__field">
                        <label for="email">Correo electrónico</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="admin@ejemplo.com"
                            required
                            autocomplete="email"
                            @if ($errors->has('email')) aria-invalid="true" aria-describedby="login-errors" @endif
                        >
                    </div>

                    <div class="login-form__field">
                        <label for="password">Contraseña</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Tu contraseña"
                            required
                            autocomplete="current-password"
                            @if ($errors->has('password')) aria-invalid="true" aria-describedby="login-errors" @endif
                        >
                    </div>

                    <label class="login-form__remember" for="remember">
                        <input type="checkbox" id="remember" name="remember" value="1" @checked(old('remember'))>
                        <span>Recordarme en este dispositivo</span>
                    </label>

                    <button type="submit" class="login-form__submit">Entrar al panel</button>
                </form>
            </section>
        </main>

        <footer class="login-page__footer">
            <span>Gestión privada de reservas</span>
            <span aria-hidden="true">·</span>
            <a href="{{ route('home') }}">Masia Can Cruz</a>
        </footer>
    </div>
</body>
</html>
