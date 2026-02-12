<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <title>Document</title>
    <link rel="Stylesheet" href="index.css">
    <link rel="icon" href="logo.jpeg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
    
    <div class="Modulouno" id="Modulo1">
    <div class="Inicio_logo">
        <img src="logo.jpeg" alt="Logo"> 
    </div>
    <div class="Nombre_red">
        <h1> NEXUS </h1>
    </div>
    </div>


   <div class="informacioninicio">
    <div class="iniciosesion" id="Modulodos">
        <form action="#">
           <div class="logo_principal">
        <img src="logo.jpeg" alt="Logo"> 
           </div>
         <div class="title">Inicio de Sesion</div> 
          <div class="Modulo2_input">
            <ion-icon name="person-outline"></ion-icon>
            <input type="email" name="usuario" id="usuario" placeholder="Usuario" required>

           </div>
        <div class="Modulo2_input">
            <ion-icon name="lock-closed-outline"></ion-icon>
            <input type="password" name="contraseña" id="contraseña" placeholder="Contraseña"required>

        </div>
        <div class="enlace">
          <a href="recuperar_password.php">¿Olvidaste la contraseña? 
                    </a>
                    </div>
    
        <button class="Boton" type="submit">Iniciar Sesion</button>
        
        </form>
        <button class="Boton" id="btnregistrarse1">Registrarse</button>
    </div>
        
      <div class="registro" id="form-registro" style="display: none;">
            <div class="title">Registro de Usuario</div> 
            
            <form action="registrar.php" method="POST" id="regis">
           <div class="form-regis">
            <div class="Registrarse-input"> 
            <ion-icon name="person-outline"></ion-icon>
            <input type="text" id="name_" placeholder="Nombre"  name="nombre"  minlength="3" maxlength="20" required>
            <small class="error-msg" id="error-name"></small>
        </div>


            <div class="Registrarse-input"> 
                <ion-icon name="person-outline"></ion-icon>
                <input type="text" placeholder="Apellido" id="apellido" name="apellido" required>
                            <small class="error-msg" id="error-apellido"></small>
            </div>


            <div class="Registrarse-input">
                <input type="date" id="FechaN" name="FechaN" placeholder="Fecha de nacimiento" required>
                <small class="error-msg" id="error-fecha" ></small>
            </div>


            <div class="Registrarse-input"> 
                 
                <select id="sexo" name="sexo">
                    
                    <option disabled selected=""> Seleccione el sexo</option> 
                    <option> Femenino </option> 
                    <option> Masculino </option>
                    </select> 
                    <ion-icon name="male-female-outline"></ion-icon>
                    <small class="error-msg" id="error-sexo"></small>
                    </div>

            <div class="Registrarse-input">
                <ion-icon name="mail-outline"></ion-icon>
                <input type="text" placeholder="Correo electronico" id="correo"  name="correo" required>
                <small class="error-msg" id="error-correo"></small>

            </div>
            
            <div class="Registrarse-input">
                <ion-icon name="call-outline"></ion-icon>
                <input type="number" placeholder="Telefono" id="telefono" name="telefono" required>
                <small class="error-msg" id="error-telefono"></small>

            </div>
            
            <div class="Registrarse-input">
                <ion-icon name="lock-closed-outline"></ion-icon>
                <input type="password" placeholder="Contraseña" id="contrasena" name="contrasena" required>
                <small class="error-msg" id="error-contrasena"></small>
            </div>
            

           </div>


            <div class="contenedor-boton">
            <button for="name"class="BotonR" type="submit" id="btnRegistrarse">Registrarse</button>
            </div>
            <div class="contenedor-boton">
            <button class="BotonRe" id="btnRegresar">Regresar</button>
            </div>

         </form>
    </div>
    
    <div class="iniciosesion" id="form-recuperar" style="display: none;">
    <div class="title">Recuperar Contraseña</div>
    
    <div id="paso-correo" style="width: 100%;">
        <p class="texto-ayuda">Introduce tu correo para validar tu cuenta</p>
        <div class="Modulo2_input">
            <ion-icon name="mail-outline"></ion-icon>
            <input type="email" id="correo-recuperar" placeholder="Correo electrónico">
        </div>
        <button class="Boton" id="btnVerificarCorreo">Continuar</button>
    </div>

    <div id="paso-nueva-pass" style="display: none; width: 100%;">
    <p class="texto-ayuda" id="mensaje-bienvenida">Ingresa tu nueva contraseña</p>
    
    <div class="Modulo2_input">
        <ion-icon name="lock-closed-outline"></ion-icon>
        <input type="password" id="nueva-pass" placeholder="Nueva Contraseña">
    </div>
    <div class="Modulo2_input">
        <ion-icon name="lock-closed-outline"></ion-icon>
        <input type="password" id="confirmar-pass" placeholder="Confirmar Contraseña">
    </div>
    <button class="Boton" id="btnCambiarPass">Actualizar</button>
</div>

    <div class="contenedor-boton">
        <button class="BotonRe" id="btnRegresarLogin">Regresar</button>
    </div>
</div>
 </div>



   
    <script src="scripts.js">
    </script>
    <script  type = "module"  src = " https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js " > </script> <script nomodule  src  = " https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js " ></script>
</body>
</html>
