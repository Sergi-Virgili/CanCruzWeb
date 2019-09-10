@extends('layouts.app')

@section('content')

    
        <section class="container">
            
            <h3>Reservas</h3>
                
                    <ul class="row align-items-left">
                    @foreach ($reservas as $reserva)
                    <li class=" card col-md-5 m-1 p-4">
                        <div>
                            Reserva a Nombre de: {{ $reserva->name }}
                            <br> 
                            Fecha Entrada: {{ $reserva->entry_date }}
                            <br>
                            Fecha Salida: {{ $reserva->out_date }}
                            <br>
                            Mensaje: {{ $reserva->message }}
                            <br>
                            Email: {{ $reserva->email }}
                        </div>
                        <div>
                            <form action="reserva/{{$reserva->id}}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input type="submit" value="x" class=" admin_delete_reserva" onclick="alert('Estas seguro de eliminar la reserva')">
                            </form>
                            <form action="reserva/{{$reserva->id}}" method="POST" >
                                @csrf
                                @method('PUT')
                                <input type="submit" value="Edit" class="btn btn-dark">
                            </form>
                        </div>
                    </li>
                        
                    @endforeach
                    </ul>
                
            
            <form action="nueva-reserva" method="GET">
                {{ csrf_field() }}
                <input type="submit" value="Nueva Reserva" class="btn btn-dark">
            </form>
        </section>
    

@endsection