@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Reservas</div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach ($reservas as $reserva)
                        <div>
                            <li class="list-group-item">Reserva a Nombre de: {{ $reserva->name }}
                                <br>
                                Fecha Entrada: {{ $reserva->entry_date }}
                                <br>
                                Fecha Salida: {{ $reserva->out_date }}
                                <br>
                                Mensaje: {{ $reserva->message }}
                                <br>
                                Email: {{ $reserva->email }}
                        </div>
                        <div class="d-flex">
                            <form action="reserva/{{$reserva->id}}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input type="submit" value="Delete" class="btn btn-danger ml-auto">
                            </form>
                            <form action="reserva/{{$reserva->id}}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="submit" value="Edit" class="btn btn-secondary">
                            </form>
                                <button type="button" class="btn btn-success">
                
                                    <a href='confirm-reserva/{{$reserva->id}}'>Confirm</a>
                                </button>
                        </div>
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