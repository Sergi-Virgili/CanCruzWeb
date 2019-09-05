@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">reservas</div>

                <div class="card-body">
                    
                   NUEVA RESERVA
                
                    @if ($errors->any())
                        <p>Complete todos los campos</p>
                    @endif
                    <form action='reserva' method='POST'>
                    {{ csrf_field() }}
                    <input type='text' placeholder= Nombre name='name' 
                        requiered value="{{ old('name') }}">

                    <input type='email' placeholder= Email name='email' value="{{ old('email') }}">
                    <input type='date'placeholder= Fecha name='date' value="{{ old('date') }}">
                    <input type='text' value='Comentario' name='message' value="{{ old('message') }}">
                    <input type='submit' value='RESERVAR'>

                   
                   
                   </form>
                      
                </div>
            </div>
        </div>
    </div>
</div>
@endsection