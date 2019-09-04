@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">reservas</div>

                <div class="card-body">
                    
                   NUEVA RESERVA

                   <form action='reserva/create' method='post'>
                   <input type='text'>
                   <input type='submit' value='ENVIAR'>

                   
                   
                   </form>
                      
                </div>
            </div>
        </div>
    </div>
</div>
@endsection