<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Masia Can Cruz, casa rural completa para familias y grupos de hasta 8 personas en el Parc Natural del Montseny.">
    <title>Masia Can Cruz | Casa rural completa en el Montseny</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-home">
    <a href="#contenido" class="skip-link">Saltar al contenido</a>

    <x-public-header />

    <main id="contenido">
        <section id="home" class="home-hero" aria-labelledby="hero-title">
            <picture>
                <source srcset="{{ asset('img/bg-Imagen.jpg') }} 2060w" media="(min-width: 900px)" type="image/jpeg">
                <source srcset="{{ asset('img/bg-Imagen.jpg') }} 2060w" type="image/jpeg">
                <img src="{{ asset('img/bg-Imagen.jpg') }}" alt="Patio de piedra de Masia Can Cruz" width="2060" height="1320" fetchpriority="high" class="home-hero__image">
            </picture>
            <div class="home-hero__shade"></div>
            <div class="site-container home-hero__content">
                <p class="eyebrow text-clay">Casa completa · Parc Natural del Montseny</p>
                <h1 id="hero-title">Una casa con raíces.<br>Un lugar para estar juntos.</h1>
                <p class="home-hero__lead">Masia Can Cruz es una casa rural completa para compartir el Montseny en familia o con amigos, a vuestro ritmo y con total privacidad.</p>
                <div class="home-facts" aria-label="Características principales">
                    <span>Hasta 8 personas</span>
                    <span>Casa completa</span>
                    <span>Reserva directa</span>
                </div>
                <a href="#booking" class="button button--light">Consultar disponibilidad y reservar</a>
            </div>
        </section>

        <section id="reserva" class="booking-section" aria-labelledby="booking-title">
            <div class="booking-shell">
                <div class="booking-intro">
                    <p class="eyebrow text-clay">Disponibilidad</p>
                    <h2 id="booking-title">Elige tu estancia</h2>
                    <p>Selecciona la entrada y la salida. Después te pediremos los datos necesarios para enviar la solicitud.</p>
                </div>
                <div id="booking" class="booking-form-panel">
                    <x-public-flash />
                    <x-reservation-form variant="public" :progressive="true" />
                </div>
            </div>
        </section>

        <section id="la-casa" class="home-section">
            <div class="site-container editorial-grid">
                <div class="editorial-copy">
                    <p class="eyebrow text-clay">La casa</p>
                    <h2>Espacio para compartir.<br>Calma para desconectar.</h2>
                    <p>Can Cruz se reserva como una casa completa: un lugar privado donde reunirse, cocinar, descansar y disfrutar del entorno sin horarios ajenos.</p>
                    <p>La piedra, la madera y la luz natural conservan el carácter de la masía, mientras los espacios comunes hacen fácil estar juntos.</p>
                    <a href="#informacion" class="text-link">Ver información práctica <span aria-hidden="true">→</span></a>
                </div>
                <figure class="arched-image">
                    <img src="{{ asset('img/habita.jpg') }}" alt="Dormitorio de Masia Can Cruz" width="1244" height="829" loading="lazy" decoding="async">
                </figure>
            </div>
        </section>

        <section class="pillars-section" aria-labelledby="pillars-title">
            <div class="site-container">
                <p class="eyebrow text-clay">Una estancia a vuestra manera</p>
                <h2 id="pillars-title" class="section-title">Tres formas de vivir Can Cruz</h2>
                <div class="pillars-grid">
                    <article class="pillar-card">
                        <span class="pillar-card__number">01</span>
                        <h3>Casa completa</h3>
                        <p>Privacidad para que familias y grupos de hasta ocho personas compartan la casa con libertad.</p>
                    </article>
                    <article class="pillar-card pillar-card--clay">
                        <span class="pillar-card__number">02</span>
                        <h3>Piscina y jardín</h3>
                        <p>Tiempo al aire libre para bajar el ritmo y disfrutar del entorno natural que rodea la masía.</p>
                    </article>
                    <article class="pillar-card pillar-card--dark">
                        <span class="pillar-card__number">03</span>
                        <h3>Bienestar</h3>
                        <p>Rincones tranquilos pensados para descansar después de descubrir el Montseny.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="vivir-can-cruz" class="home-section home-section--stone">
            <div class="site-container experience-grid">
                <figure class="experience-image">
                    <img src="{{ asset('img/spa.jpg') }}" alt="Espacio interior de bienestar" width="660" height="380" loading="lazy" decoding="async">
                </figure>
                <div class="editorial-copy">
                    <p class="eyebrow text-clay">Vivir Can Cruz</p>
                    <h2>El lujo de disponer de tiempo.</h2>
                    <p>Despertar sin prisa, compartir la mesa, salir a caminar y volver a una casa que es solo vuestra durante la estancia.</p>
                    <blockquote>“Una casa para encontrarse, conversar y volver a disfrutar de las cosas sencillas.”</blockquote>
                </div>
            </div>
        </section>

        <section id="montseny" class="montseny-section">
            <img src="{{ asset('img/bg-Imagen.jpg') }}" alt="Entorno de piedra y vegetación en el Montseny" width="2060" height="1320" loading="lazy" decoding="async">
            <div class="montseny-section__shade"></div>
            <div class="site-container montseny-section__content">
                <p class="eyebrow text-clay">Parc Natural del Montseny</p>
                <h2>Naturaleza cerca.<br>Ruido lejos.</h2>
                <p>Una base tranquila para descubrir bosques, caminos y pueblos del Montseny, y regresar a compartir el final del día.</p>
            </div>
        </section>

        <section id="informacion" class="home-section">
            <div class="site-container information-grid">
                <div>
                    <p class="eyebrow text-clay">Información práctica</p>
                    <h2>Lo esencial, antes de reservar.</h2>
                </div>
                <dl class="facts-list">
                    <div><dt>Capacidad</dt><dd>Hasta 8 personas</dd></div>
                    <div><dt>Modalidad</dt><dd>Alquiler de la casa completa</dd></div>
                    <div><dt>Ubicación</dt><dd>Parc Natural del Montseny</dd></div>
                    <div><dt>Reserva</dt><dd>Solicitud directa, sujeta a confirmación</dd></div>
                </dl>
            </div>
        </section>

        <section class="gallery-section" aria-labelledby="gallery-title">
            <div class="site-container">
                <p class="eyebrow text-clay">La masía</p>
                <h2 id="gallery-title" class="section-title">Una casa con su propio ritmo</h2>
                <div class="gallery-grid">
                    <figure class="gallery-grid__wide"><img src="{{ asset('img/bg-Imagen.jpg') }}" alt="Patio de piedra de la masía" width="2060" height="1320" loading="lazy" decoding="async"></figure>
                    <figure><img src="{{ asset('img/habita.jpg') }}" alt="Detalle de un dormitorio" width="1244" height="829" loading="lazy" decoding="async"></figure>
                    <figure><img src="{{ asset('img/spa.jpg') }}" alt="Detalle de un espacio interior" width="660" height="380" loading="lazy" decoding="async"></figure>
                </div>
            </div>
        </section>

        <section class="home-section faq-section" aria-labelledby="faq-title">
            <div class="site-container faq-grid">
                <div>
                    <p class="eyebrow text-clay">Preguntas frecuentes</p>
                    <h2 id="faq-title">Antes de enviar la solicitud</h2>
                </div>
                <div class="faq-list">
                    <details>
                        <summary>¿Se reserva toda la casa?</summary>
                        <p>Sí. Can Cruz se ofrece como casa completa para vuestro grupo.</p>
                    </details>
                    <details>
                        <summary>¿Cuántas personas pueden alojarse?</summary>
                        <p>La capacidad máxima es de ocho personas.</p>
                    </details>
                    <details>
                        <summary>¿La solicitud confirma automáticamente la estancia?</summary>
                        <p>No. Revisaremos la disponibilidad y recibiréis la confirmación después de enviar la solicitud.</p>
                    </details>
                    <details>
                        <summary>¿Cómo sé qué fechas están ocupadas?</summary>
                        <p>El calendario muestra las noches confirmadas como no disponibles y verifica de nuevo las fechas al enviar.</p>
                    </details>
                </div>
            </div>
        </section>

        <section class="closing-cta" aria-labelledby="closing-title">
            <div class="site-container closing-cta__inner">
                <p class="eyebrow">Vuestra estancia empieza aquí</p>
                <h2 id="closing-title">Encontrad unos días para estar juntos.</h2>
                <a href="#reserva" class="button button--light">Consultar disponibilidad</a>
            </div>
        </section>
    </main>

    <x-public-footer />
</body>
</html>
