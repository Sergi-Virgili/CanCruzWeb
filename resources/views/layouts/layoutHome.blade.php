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
                        
                
                        {{-- <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                          <span class="navbar-toggler-icon"></span>
                        </button> --}}
                
                        
                       
                        <div class="main-slide"></div>
                        <div id="logo"><img src="img/logo3.png" alt="logo can cruz casa rural"></div>
                        @yield('content')
                    </div>
                    <!-- /#page-content-wrapper -->
                    <!-- Sidebar -->
                    <div class="bg-light border-right" id="sidebar-wrapper">
                     
                            <div class="list-group list-group-flush">
                            
                              <a href="#" class="list-group-item list-group-item-action bg-light">Dashboard</a>
                              <a href="#" class="list-group-item list-group-item-action bg-light">Shortcuts</a>
                              <a href="#" class="list-group-item list-group-item-action bg-light">Overview</a>
                              <a href="#" class="list-group-item list-group-item-action bg-light">Events</a>
                              <a href="#" class="list-group-item list-group-item-action bg-light">Profile</a>
                              <a href="#" class="list-group-item list-group-item-action bg-light">Status</a>
                            </div>
                          </div>
                  </div>
                  <!-- /#wrapper -->
                
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
  {{-- <header>
        
        
  <div class="main-slide"></div>
  <div id="logo"><img src="img/logo3.png" alt="logo can cruz casa rural"></div>
  
  
  
  </div>

  <nav>
        
    </nav>


  </header>
  

  <div class="container">
  <div class="row justify-content-center">
    
    <div class="col-3">
    <div class="bx-wh-shadow"></div>
    </div>

    <div class="col-5">
    <div class="bx-blue"></div>
    </div>

</div>
</div>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</div> --}}

 

 
</body>
</html>
