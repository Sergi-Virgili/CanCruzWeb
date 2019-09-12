{{-- @extends('layouts.app') --}}

@extends('layouts.layoutHome')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div >
                <div >
                    @if ($errors->any())
                        <p class="errors">Complete todos los campos</p>
                    @endif
                    <form action='reserva' method='POST' class=formhome>
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-lg-6 col-sm-12 ">
                                <input type='text' class='formblue' placeholder='Nombre' name='name' requiered value="{{ old('name') }}">
                            </div>
                            <div class="col-lg-5 col-sm-12 ">
                                <input type='email' class='formblue' placeholder='Email' name='email' value="{{ old('email') }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 col-6 " >
                                <input type='date' class='formblue' placeholder="Fecha" name='entry_date' value="{{ old('entry_date') }}">
                            </div>
                            <div class="col-lg-6 col-sm-6 col-6 " >
                                 <input type='date' class='formblue' placeholder="Fecha"  name='out_date' value="{{ old('out_date') }}">
                            </div>
                            <div class="col-lg-4 col-sm-12 " >
                                 <input type='text' maxlength="4" size="4" class='formblue' placeholder='#' name='message' value="{{ old('message') }}"><br>
                            </div>
                        </div>
                        <div class="d-flex row justify-content-center ">
                            <input type='submit' value='Confirmar Reserva' class="btn btn-success ml-auto submit">
                        </div>
                        @if (!Auth::guest())
                            <div class="d-flex">
                                    <a href="/reserva" class="btn btn-primary ml-auto">Listado de Reservas</a>
                            </div>
                        @endif
                    </form>
        </div>
    </div>
 </div></div></div>
 @endsection