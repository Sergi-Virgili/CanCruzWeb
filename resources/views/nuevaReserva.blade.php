@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Realizar Nueva Reserva</div>
                <div class="card-body">
                    @if ($errors->any())
                        <p>Complete todos los campos</p>
                    @endif
                    <form action='/reserva' method='POST'>
                        {{ csrf_field() }}
                        <input type='text' placeholder= Nombre name='name' requiered value="{{$reserva->name}}">
                        <input type='email' placeholder= Email name='email' value="{{$reserva->email}}">
                        <input type='date' placeholder= Fecha name='entry_date' value="{{$reserva->entry_date}}">
                        <input type='date' placeholder= Fecha name='out_date' value="{{$reserva->out_date}}">
                        <input type='text' value='Comentario' name='message' value="{{$reserva->message}}"><br>
                        <div class="d-flex">
                            <input type='submit' value='Enviar Reserva' class="btn btn-success ml-auto">
                        </div>
                        @if (!Auth::guest())
                            <div class="d-flex">
                                <a href="/reserva" class="btn btn-primary ml-auto">Listado de Reservas</a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection