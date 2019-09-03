@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">reservas</div>

                <div class="card-body">
                    
                    @foreach ($reservas as $reserva)
                    <li>Reserva a Nombre de: {{ $reserva->name }}, 
                        Fecha: {{ $reserva->date }}, 
                        Mensaje: {{ $reserva->message }}, 
                        Email: {{ $reserva->email }}
                        <form action="reserva/{{$reserva->id}}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="submit" value="destruir pàra siempre">
                        </form>
                        
                        
                        
                        
                         </li>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection