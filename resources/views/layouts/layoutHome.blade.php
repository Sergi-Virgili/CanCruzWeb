<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.2/css/all.css">
    <title>Can Cruz - Casa Rural
    </title>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    </head>
    
    <body>

            <div class="d-flex" id="wrapper">

                    
                    <!-- /#sidebar-wrapper -->
                
                    <!-- Page Content -->
                    <div id="page-content-wrapper">
                
                        <i class="fas fa-bars" id="menu-toggle"></i>
                        
                
                
                        
                       
                        <div class="main-slide" id="home"></div>
                        <div id="logo"><img src="img/logo3.png" alt="logo can cruz casa rural"></div>
                        
                        <div class="container">
                          <div class="row justify-content-center">
                            <div class="col-lg-3 col-md-5 col-sm-7 col-10   bx-wh-shadow">
                            </div>
                            <div class="col-lg-6 col-md-8 col-sm-10 col-11  bx-blue">
                              @yield('content')
                            </div>
                          </div>
                      </div>

                        <section class="container" id="about">
                          <h3 class="row justify-content-center mb-4 ">Sobre Nosotros</h3>
                          <div class="row justify-content-center">
                          <p class="about">Enmarcada por el Parc Natural del Montseny, reserva de la Biosfera por la UNESCO,  la Masía Can Cruz cuenta con una ubicación y vistas privilegiadas que proporcionan el ambiente ideal para quienes buscan bienestar en la naturaleza.</p>
                          <p class="about"> Lorem ipsum dolor sit amet consectetur adipisicing elit. Rem neque totam, sunt, fugit fugiat odit deserunt eius veritatis debitis assumenda at magni deleniti quos beatae, et accusantium consequuntur itaque minima?</p>
                        </div>
                        </section>

                        {{-- FICHAS --}}
                        <section class="container" id="gastronomia">
                          <div class="row  justify-content-center">
                            <div class="col-md-4 card gastronomia">
                              <h3>Gastronomía</h3>
                              <h4>BIO SLOW FOOD</h4>
                              <p>El restaurant de slow food ofrece deliciosos platos frescos y bio con productos de cosecha propia, opciones vegetarianas y veganas incluidas.</p>
                              <a href="#">Ver más</a>
                            </div>
                            <div class="col-md-8 ">
                              <img class="img-fluid" src="img/capa5.jpg" alt="">
                            </div>
                          </div>

                        </section>
                        <section class="container" id="suites">
                          <div class="row habitaciones">
                            <div class="col-md-4 card gastronomia">
                              <h3>Habitaciones y Suites</h3>
                              <h4>LUJO Y COMFORT</h4>
                              <p>Nuestra Masía dispone de lujosas suites, un gran jardín con piscina outdoor, golf y un organic spa. </p>
                              <a href="#">Ver más</a>
                            </div>
                            <div class="col-md-8 ">
                                <img class="img-fluid" src="img/habita.jpg" alt="">
                              </div>
                            
                          </div>
                        
                        </section>
                        {{-- EXPERIENCIAS --}}
                        <div class="experiencias">
                        <section class="experiencias container" id="experiencias">
                            <h3>Experiencias</h3>
                            <div class="row justify-content-center ">
                                <div class="col-md-4">
                                    <img class="img-fluid mb-2" src="img/spa.jpg" alt="">
                                    
                                </div>
                                <div class="col-md-4">
                                    <img class="img-fluid mb-2" src="img/huerta.jpg" alt="">
                                </div>
                                <div class="col-md-4">
                                    <img class="img-fluid mb-2" src="img/spa.jpg" alt="">
                                </div>
                              </div>
                        </section>
                      </div>
                        <footer>
                          <div class="container">
                            ehis is footer
                            <div class="row justify-content-center">
                            {{-- <i class="fab fa-facebook-f rs"></i> --}}
                            <i class="fab fa-tripadvisor rs"></i>
                            <i class="fab fa-instagram rs"></i>
                          </div>
                          </div>
                        </footer>
                    </div>
                    <!-- /#page-content-wrapper -->
                    <!-- Sidebar -->
                    <div class="bg-light border-right fixed" id="sidebar-wrapper">
                     
                            <div class="list-group list-group-flush menu-list">
                            
                              <a href="#home" class="list-group-item list-group-item-action bg-light">Home</a>
                              <a href="#about" class="list-group-item list-group-item-action bg-light">Sobre Nosotros</a>
                              <a href="#home" class="list-group-item list-group-item-action bg-light">Reserva</a>
                              <a href="#gastronomia" class="list-group-item list-group-item-action bg-light">Gastronomía</a>
                              <a href="#suites" class="list-group-item list-group-item-action bg-light">Siutes</a>
                              <a href="#experiencias" class="list-group-item list-group-item-action bg-light">Experiencias</a>
                              <a href="#contacto" class="list-group-item list-group-item-action bg-light">Contacto</a>
                             
                            </div>
                          </div>
                  </div>
                  <!-- /#wrapper -->
                


            {{-- <section class="row">
                <div class="col-md-5 ficha_texto">
                    Lorem ipsum, dolor sit amet consectetur adipisicing elit. Vitae eius quia doloribus consequatur asperiores ex recusandae eveniet animi, facere corporis temporibus similique quas deleniti, inventore fugiat rem soluta quos qui.
                </div>
            </section> --}}

            
                  <!-- Bootstrap core JavaScript -->
                  <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
                  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
                
                  <!-- Menu Toggle Script -->
                  <script>
                    $("#menu-toggle").click(function(e) {
                      e.preventDefault();
                      $("#wrapper").toggleClass("toggled");
                    });
                  </script>

 

 
</body>
</html>