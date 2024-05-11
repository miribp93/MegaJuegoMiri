

{{-- user: admin     pwd: 111111 --}}





{{-- Usamos la vista app como plantilla --}}
@extends('app')

{{-- Sección aporta el título de la página --}}
@section('title', 'Formulario login')

{{-- Sección para el autoregistro de usuarios --}}
@section ('navbar')
<li class='nav-item'>
    <a class='nav-link' aria-current='page' href='index.php?botonprocloginadmin'>Volver</a>
</li>
@endsection

{{-- Sección muestra el formulario de administración --}}
@section('content')

<div class="container">
    
    <h2 class="text-center">Panel Administrador</h2>
    
    <form method="POST" action="index.php" id='formadmin'>
        
        <div class="mb-3">
                    <div class="col-md-8 col-md-offset-4">
                        <input type="submit" class="btn btn-light" id="botonListaUsuarios" name="botonListaUsuarios" value="Lista de Usuarios">
                    </div>                                   
                </div>
        
        
    </form>
    
    @if (isset($listaUsuarios))

    <div class="row">
        <div class="col-12">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Partidas</th>
                    <th scope="col">Puntuación</th>
                </tr>
            </thead>
        
            <tbody>
            
                    @foreach($panelUsuarios as $usuario)
                    <tr>
                        <th scope="row">{{$loop->iteration}}</td>
                        <td>{{$usuario[0]}}</td>
                        <td>{{$usuario[1]}}</td>
                        <td>{{$usuario[2]}}</td>
                    </tr>
                    @endforeach
                   
                    
                @else
                                
            </tbody>
        </table>
        </div>
    </div>
    @endif
</div>
@endsection
