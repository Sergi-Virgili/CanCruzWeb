@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">reservas</div>

                <div class="card-body">
                    
                   NUEVA RESERVA

                   <form action='reserva' method='POST'>
                  
                   <input type='text' placeholder= Nombre name='name'>
                   <input type='email' placeholder= Email name='email'>
                   <input type='date'placeholder= Fecha name='date'>
                   <input type='text' value='Comentario' name='commentary'>
                   <input type='submit' value='RESERVAR'>

                   
                   
                   </form>
                      
                </div>
            </div>
        </div>
    </div>
</div>
@endsection