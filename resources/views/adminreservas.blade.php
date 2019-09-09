@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">reservas</div>

                <div class="card-body">
                    <ul class="list-group">
                    @foreach ($reservas as $reserva)
                    <li class="list-group-item">Reserva a Nombre de: {{ $reserva->name }}, 
                        Fecha: {{ $reserva->date }}, 
                        Mensaje: {{ $reserva->message }}, 
                        Email: {{ $reserva->email }}
                            <form action="reserva/{{$reserva->id}}" method="POST">
                                @csrf
                                @method('DELETE')
                            <input type="submit" value="x" class="btn btn-danger">
                            </form>
                            <form action="reserva/{{$reserva->id}}" method="POST">
                                @csrf
                                @method('PUT')
                            <input type="submit" value="edit" class="btn btn-dark">
                            </form>
                        </li>
                        
                    @endforeach
                    </ul>

                </div>
                
            </div>
            <form action="nueva-reserva" method="GET">
                {{ csrf_field() }}
                <input type="submit" value="Nueva Reserva" class="btn btn-dark">
            </form>
        </div>
    </div>
</div>
@endsection