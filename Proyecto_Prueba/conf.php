<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Index.html");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_completo = ($_SESSION['nombre'] ?? 'Usuario') . " " . ($_SESSION['apellido'] ?? '');

$conn = new mysqli("127.0.0.1", "root", "", "nexus_db", 3306);
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Configuración</title>
<link rel="stylesheet" href="conf.css">
<link rel="icon">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

</head>
<body class="bg-gray-100">

<div class="contenedor-conf ">

    <!-- Título -->
    <div class="titulo-conf ">
        <div class="conf ">
            <ion-icon name="chevron-back-outline" onclick="window.history.back()" class="devolver cursor-pointer" id="btn-devolver"></ion-icon>
            <div class="NomConf">Configuración</div>
        </div>
    </div>

    <div class="info-conf">
        <div class="select-conf ">
            <h2 class="text-lg font-bold mb-2">Cuenta</h2>
             <!-- Modulo de historial -->
            <h3 class="text-md font-bold mb-2">Tu Actividad</h3>
             <!-- Historial de notificaciones  -->
        <button class="noti list-item" id="btn-notificaciones"> Notificación</button>

<div id="notificaciones-section" class="mt-4 hidden">
    <h3 class="text-md font-bold mb-2">Historial de notificaciones</h3>
    <div id="listaNotificaciones"
     class="border rounded-xl p-2 max-h-96 overflow-y-auto text-sm text-gray-700 bg-white shadow-sm space-y-2">
</div>

</div>
            <button class="megustas list-item " id="Megustas">Me gustas</button>
             <!-- Sección de reacciones (inicialmente oculta) -->
            <div id="reacciones-section" class="mt-4 hidden">
                <h3 class="text-md font-bold mb-2">Historial de Reacciones</h3>

                <!-- Filtro de fechas -->
                <div class="mb-4 flex gap-2 items-center">
                    <input type="text" id="fecha-inicio" class="border rounded px-2 py-1" placeholder="Desde">
                    <input type="text" id="fecha-fin" class="border rounded px-2 py-1" placeholder="Hasta">
                    <button id="buscarReacciones" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Buscar</button>
                </div>

                <!-- Botones de filtro -->
                <div class="mb-4 flex gap-2">
                    <button id="misReacciones" class="bg-gray-200 px-3 py-1 rounded hover:bg-gray-300">Mis reacciones</button>
                    <button id="reaccionesAMi" class="bg-gray-200 px-3 py-1 rounded hover:bg-gray-300">Me han Reaccionado</button>
                </div>

                <!-- Lista -->
                <div id="lista-reacciones" class="border rounded p-2 max-h-96 overflow-y-auto text-sm text-gray-700">
                    <p class="text-gray-500">Selecciona una fecha y haz clic en "Buscar" para ver tu historial de reacciones.</p>
                </div>
            </div>

            <button class="coment list-item" id="btn-abrir-comentarios">Comentarios</button>

<div id="comentarios-section" class="mt-4 hidden">
    <h3 class="text-md font-bold mb-2">Historial de Comentarios</h3>

    <div class="mb-4 flex gap-2 items-center">
        <input type="text" id="coment-fecha-inicio" class="border rounded px-2 py-1" placeholder="Desde">
        <input type="text" id="coment-fecha-fin" class="border rounded px-2 py-1" placeholder="Hasta">
        <button id="buscarComentarios" class="bg-purple-500 text-white px-3 py-1 rounded hover:bg-purple-600">Buscar</button>
    </div>

    <div class="mb-4 flex gap-2">
        <button id="misComentarios" class="bg-gray-200 px-3 py-1 rounded hover:bg-gray-300">Mis comentarios</button>
        <button id="comentariosAMi" class="bg-gray-200 px-3 py-1 rounded hover:bg-gray-300">Me han comentado</button>
    </div>

    <div id="lista-comentarios" class="border rounded p-2 max-h-96 overflow-y-auto text-sm text-gray-700">
        <p class="text-gray-500">Selecciona fechas y haz clic en "Buscar" para ver tu historial de comentarios.</p>
    </div>
</div>

            <button class="prefcont list-item" id="btn-preferencias">Publicaciones</button>
          <div id="preferencias-section" class="mt-4 hidden bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <h4 class="text-md font-bold mb-4">Historial de Publicaciones</h4>
    <div class="flex border-b"> 
        <button id="btn-pref-personal" onclick="cargarPreferencias('personal')" class="flex-1 py-3 text-sm font-bold border-b-2 border-purple-600 text-purple-600 bg-purple-50">Personal</button>
        <button id="btn-pref-contenido" onclick="cargarPreferencias('contenido')" class="flex-1 py-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:bg-gray-50">Contenido</button>
    </div>
    <div id="lista-preferencias-contenido" class="max-h-[400px] overflow-y-auto divide-y divide-gray-100"></div>
</div>           
        </div>

        <!-- Privacidad -->
     <div class="select-conf w">    
            <h2 class="text-lg font-bold mb-2">Privacidad</h2>
            
            <button class="cambNom list-item flex justify-between items-center" id="btn-abrir-editor"> Cambiar nombre de usuario</button>
            <div id="plantilla-editar-perfil" class="hidden mt-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="mb-4">
                <label class="text-[10px] font-bold text-purple-500 uppercase">Perfil de:</label>
             <div class="flex items-center gap-2 mt-1">
                <span class="text-lg font-bold text-gray-700">
                    <?php echo ($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''); ?>
                </span>
             </div>
               </div>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
    <div class="flex flex-col gap-1">
        <input type="text" id="nuevo-nombre"  placeholder="Nombre" 
               class="p-2 bg-gray-50 border border-gray-100 rounded-lg outline-none focus:border-purple-400 text-sm transition-colors">
    </div>
    <div class="flex flex-col gap-1"><input type="text" id="nuevo-apellido" 
               placeholder="Apellido" 
               class="p-2 bg-gray-50 border border-gray-100 rounded-lg outline-none focus:border-purple-400 text-sm transition-colors">
    </div>
   </div>

<button onclick="guardarCambiosPerfil()" class="w-full bg-purple-600 text-white py-2 rounded-lg font-bold hover:bg-purple-700 transition-colors">
    Guardar Cambios
</button>
        </div>

<button class="cambCont list-item" id="btn-abrir-pass">Cambiar contraseña</button>
<div id="plantilla-editar-pass" class="hidden mt-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
   <div class="mb-4">
                <label class="text-[10px] font-bold text-purple-500 uppercase">Perfil de:</label>
             <div class="flex items-center gap-2 mt-1">
                <span class="text-lg font-bold text-gray-700">
                    <?php echo ($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''); ?>
                </span>
             </div>
                        <h4>La contraseña debe tener al menos 8 caracteres e incluir la combinación de numeros, letras y caracteres especiales (!$@%). </h4>
               </div>
    <div class="flex flex-col md:flex-row gap-4">
        
        <input type="password" id="pass-actual" placeholder="Contraseña actual" class= "w-full p-2 bg-gray-50 border border-gray-100 rounded-lg outline-none focus:border-purple-400 text-sm transition-colors">
        <input type="password" id="pass-nueva" placeholder="Nueva contraseña" class="w-full p-2 bg-gray-50 border border-gray-100 rounded-lg outline-none focus:border-purple-400 text-sm transition-colors">
        <input type="password"  id="pass-repetir" placeholder="Repetir nueva contraseña" class="w-full p-2 bg-gray-50 border border-gray-100 rounded-lg outline-none focus:border-purple-400 text-sm transition-colors">

 <button onclick="actualizarPassword()" 
    class="w-full md:w-32 bg-purple-600 text-white py-2 md:py-1 px-2 rounded-md text-sm font-semibold hover:bg-purple-700 transition-colors mt-2 md:mt-0">
    Cambiar Contraseña</button>

    </div>
   
</div>

<button class="cambCorreo list-item" id="btn-cam-c">Cambiar correo electrónico</button>
<div id="plantilla-editar-correo" class="hidden mt-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
     <div class="mb-4">
                <label class="text-[10px] font-bold text-purple-500 uppercase">Perfil de:</label>
             <div class="flex items-center gap-2 mt-1">
                <span class="text-lg font-bold text-gray-700">
                    <?php echo ($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''); ?>
                </span>
             </div>
               </div>
    <div class="flex flex-col gap-3">
        <input  type="email"  id="correo-nuevo" placeholder="Nuevo correo electrónico" class="p-2 bg-gray-50 border border-gray-100 rounded-lg outline-none focus:border-purple-400 text-sm transition-colors"  >

        <button 
            onclick="actualizarCorreo()"
            class="w-full bg-purple-600 text-white py-2 rounded-lg font-bold hover:bg-purple-700 transition-colors">
            Actualizar Correo
        </button>
    </div>
</div>

   </div>
        <!-- Chats -->
        <div class="select-conf w">
            <h2 class="text-lg font-bold mb-2">Chats</h2>
            <button class="histchats list-item w-full text-left px-2 py-1 rounded hover:bg-gray-100"
            onclick="window.location.href='http://localhost/chat/historial_personal.php'">
    Chats
        </div>

        <hr class="separator">

        <!-- Normas -->
        <div class="select-conf bg-white p-4 rounded shadow">
            <h2 class="text-lg ">Normas Comunitarias</h2>
            <h4 class="text-sm ">
                Prohibido contenido que promueva el odio, la violencia, el acoso, la humillación o la intimidación. No se permite compartir información privada sin consentimiento para chantaje o acoso.
                Se prohíben las amenazas a la integridad física y las actividades que fomenten la autolesión. Integridad y Autenticidad de la Cuenta: No suplantar identidades ni crear cuentas falsas. 
                No vender, licenciar o comprar cuentas o datos. Evitar comportamientos de bot, como publicar con demasiada frecuencia. Actividades Ilegales y Peligrosas: No se tolera el apoyo al terrorismo,
                crimen organizado o grupos de odio. Prohibida la venta de armas, drogas y ofertas de servicios sexuales. Restricciones sobre productos de tabaco y suplementos inseguros. Propiedad Intelectual 
                y Contenido: Respetar los derechos de autor; obtener permiso para republicar contenido y dar crédito al autor original. Contenido explícito debe ser advertido o retirado; se favorece la información
                periodística verídica.
            </h4>
        </div>

    </div>
</div>

<script>
    
/* === FUNCIÓN CENTRAL PARA CERRAR OTROS MÓDULOS === */
function cerrarOtrosModulos(idExcepcion) {
    const modulos = [
        'notificaciones-section',
        'reacciones-section',
        'comentarios-section',
        'preferencias-section',
        'plantilla-editar-perfil',
        'plantilla-editar-pass',
        'plantilla-editar-correo'
    ];

    modulos.forEach(id => {
        if (id !== idExcepcion) {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        }
    });

    // Resetear flecha de editar perfil si no es la excepción
    const flecha = document.querySelector('#btn-abrir-editor ion-icon'); 
}

/* === MÓDULO DE NOTIFICACIONES === */
const btnNoti = document.getElementById('btn-notificaciones');
const seccionNoti = document.getElementById('notificaciones-section');
const listaNoti = document.getElementById('listaNotificaciones');

btnNoti.addEventListener('click', () => {
    cerrarOtrosModulos('notificaciones-section'); // <--- NUEVO
    const estaOculto = seccionNoti.classList.toggle('hidden');

    if (!estaOculto) {
        cargarNotificaciones();
    }
});

function cargarNotificaciones() {
    listaNoti.innerHTML =
        '<p class="text-center text-blue-500 p-4 animate-pulse">Cargando...</p>';

    fetch('obtener_notificaciones.php')
        .then(res => res.json()) 
        .then(data => {
            dibujarNotificaciones(data.lista);
        })
        .catch(err => {
            console.error(err);
            listaNoti.innerHTML =
                '<p class="text-red-500 text-center p-4">Error al cargar.</p>';
        });
}

function dibujarNotificaciones(lista) {
    listaNoti.innerHTML = '';

    if (!lista || lista.length === 0) {
        listaNoti.innerHTML =
            '<p class="text-gray-400 text-center p-6">No tienes notificaciones.</p>';
        return;
    }

    lista.forEach(n => {

        let texto = '';
        let emoji = '🔔';

        if (n.tipo === 'comentario') {
            texto = 'comentó tu publicación';
            emoji = '💬';
        } else if (n.tipo === 'reaccion') {
            texto = 'reaccionó a tu publicación';
            emoji = '❤️';
        } else if (n.tipo === 'seguir') {
            texto = 'empezó a seguirte';
            emoji = '➕';
        }

        const div = document.createElement('div');
        div.className = `
            flex gap-4 p-3 rounded-lg cursor-pointer
            ${n.leido == 0 ? 'bg-purple-50' : 'bg-white'}
            hover:bg-gray-50 transition
        `;

        div.innerHTML = `
   <div class="relative w-[48px] h-[48px] min-w-[48px] rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">

    <!-- PLACEHOLDER (SIEMPRE PRESENTE) -->
    <span class="text-gray-400 text-lg">👤</span>

    <!-- FOTO REAL -->
    <img
        src="${n.foto_perfil}"
        width="48"
        height="48"
        class="absolute inset-0 w-full h-full object-cover rounded-full opacity-0 transition-opacity duration-200"
        loading="lazy"
        onload="this.style.opacity='1'"
        onerror="this.remove()">
</div>


            <!-- TEXTO -->
            <div class="flex-1">
                <p class="text-sm text-gray-800 leading-snug">
                    <span class="font-bold">${n.nombre} ${n.apellido}</span>
                    ${texto}
                </p>
                <p class="text-xs text-gray-400 mt-1">🕒 ${n.fecha}</p>
            </div>

            <!-- PUNTO NO LEÍDO -->
            ${n.leido == 0 ? '<span class="w-2 h-2 bg-purple-500 rounded-full mt-2"></span>' : ''}
        `;

        listaNoti.appendChild(div);
    });
}

    
/* === CONFIGURACIÓN DE REACCIONES === */
const lista = document.getElementById('lista-reacciones');
const fechaInicio = document.getElementById('fecha-inicio');
const fechaFin = document.getElementById('fecha-fin');
const buscarBtn = document.getElementById('buscarReacciones');
const misBtn = document.getElementById('misReacciones');
const aMiBtn = document.getElementById('reaccionesAMi');
const seccionReacciones = document.getElementById('reacciones-section');
const btnMegustas = document.getElementById('Megustas');

let filtroTipo = 'mis';

/* 1. Inicializar Calendarios (Bloqueo de fechas futuras) */
const fpConfig = { 
    altInput: true, 
    altFormat: "F j, Y", 
    dateFormat: "Y-m-d",
    maxDate: "today", // No permite seleccionar días después de hoy
    disableMobile: true
};

const fpInicio = flatpickr("#fecha-inicio", fpConfig);
const fpFin = flatpickr("#fecha-fin", fpConfig);

/* 2. Control del Botón Principal (Abrir/Cerrar) */
btnMegustas.addEventListener('click', () => {
    cerrarOtrosModulos('reacciones-section'); // <--- NUEVO
    const estaOculto = seccionReacciones.classList.toggle('hidden');

    if (estaOculto) {
        lista.innerHTML = '';
        fpInicio.clear();
        fpFin.clear();
    } else {
        lista.innerHTML = `
            <div class="text-center p-6">
                <p class="text-gray-500">📅 Selecciona un rango de fechas para ver las reacciones.</p>
            </div>`;
    }
});

/* 3. Eventos de Filtros y Búsqueda */
misBtn.addEventListener('click', () => { 
    filtroTipo = 'mis'; 
    filtrarReacciones(); 
});

aMiBtn.addEventListener('click', () => { 
    filtroTipo = 'ami'; 
    filtrarReacciones(); 
});

buscarBtn.addEventListener('click', filtrarReacciones);

/* 4. Función de Filtrado con Validación */
function filtrarReacciones() {
    const inicio = fechaInicio.value;
    const fin = fechaFin.value;

    // VALIDACIÓN: Si el usuario no ha tocado el calendario, no hace el fetch
    if (!inicio || !fin) {
        lista.innerHTML = `
            <p class="text-orange-500 text-center p-4 bg-orange-50 rounded-lg border border-orange-200">
                ⚠️ Por favor, selecciona una fecha de <b>Inicio</b> y una de <b>Fin</b>.
            </p>`;
        return; 
    }

    lista.innerHTML = '<p class="text-center text-blue-500 p-4 font-medium animate-pulse">Buscando reacciones...</p>';

    fetch(`./reacciones_usuario.php?tipo=${filtroTipo}&inicio=${inicio}&fin=${fin}`)
    .then(res => {
        if (!res.ok) throw new Error("Error en el servidor");
        return res.json();
    })
    .then(response => {
        if (response.status === "success") {
            mostrarReacciones(response.data);
        } else {
            lista.innerHTML = `<p class="text-red-500 text-center p-4">${response.message}</p>`;
        }
    })
    .catch(err => {
        lista.innerHTML = '<p class="text-red-500 text-center p-4">Error crítico de conexión.</p>';
        console.error(err);
    });
}

/* 5. Función para Dibujar en Pantalla */
function mostrarReacciones(reacciones) {
    lista.innerHTML = '';
    
    if (!reacciones || reacciones.length === 0) {
        lista.innerHTML = '<p class="text-gray-500 text-center p-6 italic">No se encontraron reacciones en este rango de fechas.</p>';
        return;
    }

    reacciones.forEach(r => {
        const div = document.createElement('div');
        div.className = 'p-3 border-b last:border-0 flex items-center gap-4 hover:bg-gray-50 transition-colors';

        const nombre = filtroTipo === 'mis' 
            ? `${r.nombre_objetivo} ${r.apellido_objetivo}` 
            : `${r.nombre_usuario || 'Usuario'} ${r.apellido_usuario || ''}`;

        const textoAccion = filtroTipo === 'mis' ? 'Le diste ❤️ a' : 'le dio ❤️ a';
        const caption = (r.publicacion && r.publicacion.trim() !== "") ? r.publicacion : "una publicación";

        div.innerHTML = `
            <div class="relative">
                <img src="${r.ruta_archivo}" class="w-14 h-14 object-cover rounded-md shadow-sm" onerror="this.src='placeholder.png'">
                <span class="absolute -bottom-1 -right-1 text-xs">❤️</span>
            </div>
            <div class="flex-1 text-sm">
                <p class="text-gray-800">
                    <span class="font-bold">${filtroTipo === 'ami' ? nombre : 'Tú'}</span> 
                    ${textoAccion} 
                    <span class="font-semibold text-blue-600">"${caption}"</span>
                </p>
                <p class="text-xs text-gray-400 mt-1">🕒 ${r.fecha}</p>
            </div>
        `;
        lista.appendChild(div);
    });
}


/* === MÓDULO DE COMENTARIOS === */
const seccionCom = document.getElementById('comentarios-section');
const listaCom = document.getElementById('lista-comentarios');
let filtroCom = 'mis';

const fpComI = flatpickr("#coment-fecha-inicio", { maxDate: "today", altInput: true, dateFormat: "Y-m-d" });
const fpComF = flatpickr("#coment-fecha-fin", { maxDate: "today", altInput: true, dateFormat: "Y-m-d" });

document.getElementById('btn-abrir-comentarios').addEventListener('click', () => {
    cerrarOtrosModulos('comentarios-section'); // <--- NUEVO
    const isHidden = seccionCom.classList.toggle('hidden');
    if (isHidden) {
        listaCom.innerHTML = '<p class="text-gray-500">Selecciona fechas para ver comentarios.</p>';
        fpComI.clear();
        fpComF.clear();
    }
});

// Filtros
document.getElementById('misComentarios').addEventListener('click', () => { 
    filtroCom = 'mis'; 
    buscarComentarios(); 
});

document.getElementById('comentariosAMi').addEventListener('click', () => { 
    filtroCom = 'ami'; 
    buscarComentarios(); 
});

document.getElementById('buscarComentarios').addEventListener('click', buscarComentarios);

function buscarComentarios() {
    // Obtenemos las fechas directamente por ID
    const ini = document.getElementById('coment-fecha-inicio').value;
    const fin = document.getElementById('coment-fecha-fin').value;

    if (!ini || !fin) {
        listaCom.innerHTML = '<p class="text-orange-500 text-center p-4 italic">⚠️ Selecciona el rango de fechas.</p>';
        return;
    }

    listaCom.innerHTML = '<p class="text-center p-4 animate-pulse">Buscando comentarios...</p>';

    fetch(`./comentarios_usuario.php?tipo=${filtroCom}&inicio=${ini}&fin=${fin}`)
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            dibujarListaComentarios(res.data);
        } else {
            listaCom.innerHTML = `<p class="text-red-500 text-center p-4">${res.message}</p>`;
        }
    })
    .catch(err => {
        console.error(err);
        listaCom.innerHTML = '<p class="text-red-500 text-center p-4">Error de conexión.</p>';
    });
}
function dibujarListaComentarios(datos) {
    listaCom.innerHTML = '';
    
    if (datos.length === 0) {
        listaCom.innerHTML = `
            <div class="flex flex-col items-center justify-center p-8 text-gray-400">
                <ion-icon name="chatbubble-ellipses-outline" style="font-size: 48px;"></ion-icon>
                <p class="mt-2">No hay comentarios en este rango.</p>
            </div>`;
        return;
    }

    datos.forEach(c => {
        const div = document.createElement('div');
        div.className = 'p-4 border-b border-gray-100 flex items-start gap-4 hover:bg-gray-50 transition-all bg-white';
        
        const nombre = filtroCom === 'mis' 
            ? `${c.nombre_objetivo} ${c.apellido_objetivo}` 
            : `${c.nombre_usuario} ${c.apellido_usuario}`;

        const accionHeader = filtroCom === 'mis' 
            ? `<span class="text-purple-600 font-semibold text-xs uppercase tracking-wider">Tu comentario a ${nombre}</span>`
            : `<span class="text-blue-600 font-semibold text-xs uppercase tracking-wider">${nombre} te comentó</span>`;

        div.innerHTML = `
            <div class="flex-shrink-0">
                <img src="${c.ruta_archivo}" class="w-12 h-12 object-cover rounded-lg shadow-sm ring-1 ring-black/5" onerror="this.src='placeholder.png'">
            </div>
            <div class="flex-1 min-w-0">
                ${accionHeader}
                <p class="text-gray-700 text-sm mt-1 leading-relaxed break-words">
                    ${c.contenido ? c.contenido : '<span class="text-gray-300 italic">Comentario vacío</span>'}
                </p>
                <div class="flex items-center gap-2 mt-2">
                    <ion-icon name="time-outline" class="text-gray-400"></ion-icon>
                    <span class="text-[10px] text-gray-400 font-medium">${c.fecha}</span>
                </div>
            </div>
        `;
        listaCom.appendChild(div);
    });
}

/* === MÓDULO DE PREFERENCIAS (ESTILO PERFIL) === */
document.addEventListener('DOMContentLoaded', () => {
    const btnPref = document.getElementById('btn-preferencias');
    const seccionPref = document.getElementById('preferencias-section');
    
    if (btnPref && seccionPref) {
        btnPref.onclick = function() {
            cerrarOtrosModulos('preferencias-section'); // <--- NUEVO
            const estaOculto = seccionPref.classList.toggle('hidden');
            
            if (!estaOculto) {
                cargarPreferencias('personal');
            }
        };
    }
});

// Función para cargar los datos (afuera del DOMContentLoaded para que sea global)
function cargarPreferencias(tipo) {
    const lista = document.getElementById('lista-preferencias-contenido');
    const btnP = document.getElementById('btn-pref-personal');
    const btnC = document.getElementById('btn-pref-contenido');

    if(!lista) return;
    lista.innerHTML = '<p class="text-center p-10 text-purple-600 animate-pulse">Cargando ' + tipo + '...</p>';

    // Estilo de los botones (Cual está activo)
    if(tipo === 'personal') {
        btnP.className = "flex-1 py-3 text-sm font-bold border-b-2 border-purple-600 text-purple-600 bg-purple-50";
        btnC.className = "flex-1 py-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:bg-gray-50";
    } else {
        btnC.className = "flex-1 py-3 text-sm font-bold border-b-2 border-purple-600 text-purple-600 bg-purple-50";
        btnP.className = "flex-1 py-3 text-sm font-bold border-b-2 border-transparent text-gray-500 hover:bg-gray-50";
    }

    fetch(`preferencias_usuario.php?tipo=${tipo}`)
    .then(res => res.json())
    .then(res => {
        lista.innerHTML = '';
        if (res.data.length === 0) {
            lista.innerHTML = `<p class="text-center p-10 text-gray-400 italic">No hay nada en ${tipo}.</p>`;
            return;
        }

        res.data.forEach(p => {
            const div = document.createElement('div');
            div.className = 'p-4 flex items-center justify-between border-b border-gray-100';
            div.innerHTML = `
                <div class="flex items-center gap-4">
                    <img src="${p.ruta_archivo}" class="w-16 h-16 object-cover rounded-lg border">
                    <div>
                        <span class="text-[10px] font-bold text-purple-400 uppercase">${tipo}</span>
                        <p class="text-sm font-semibold text-gray-700">${p.fecha}</p>
                    </div>
                </div>
            `;
            lista.appendChild(div);
        });
    })
    .catch(err => {
        console.error(err);
        lista.innerHTML = '<p class="text-red-500 text-center p-6">Error al cargar.</p>';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const btnAbrir = document.getElementById('btn-abrir-editor');
    const plantilla = document.getElementById('plantilla-editar-perfil');

    // Inputs
    const inputNombre = document.getElementById('nuevo-nombre');
    const inputApellido = document.getElementById('nuevo-apellido');

    if(btnAbrir) {
        btnAbrir.onclick = function() {
            cerrarOtrosModulos('plantilla-editar-perfil'); // <--- NUEVO
            const isHidden = plantilla.classList.toggle('hidden');
            // flecha.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(180deg)'; // Se comenta porque al parecer no hay flecha definida
        };
    }

    // Función para validar en tiempo real
    const validarInput = (input) => {
        // Expresión: Solo letras (incluye acentos y eñes), sin números ni caracteres especiales
        const regex = /^[a-zA-ZÀ-ÿ]+$/; 
        const valor = input.value.trim();

        if (valor !== "" && regex.test(valor)) {
            // Es válido
            input.classList.remove('border-red-500', 'bg-red-50');
            input.classList.add('border-green-400');
            return true;
        } else {
            // Es inválido
            input.classList.remove('border-green-400');
            input.classList.add('border-red-500', 'bg-red-50');
            return false;
        }
    };

    // Escuchar cuando el usuario escribe
    inputNombre.addEventListener('input', () => validarInput(inputNombre));
    inputApellido.addEventListener('input', () => validarInput(inputApellido));
});

async function guardarCambiosPerfil() {
    const inputNom = document.getElementById('nuevo-nombre');
    const inputApe = document.getElementById('nuevo-apellido');
    
    const nom = inputNom.value.trim();
    const ape = inputApe.value.trim();

    // Re-validar antes de enviar
    const regex = /^[a-zA-ZÀ-ÿ]+$/;

    if(!regex.test(nom) || !regex.test(ape)) {
        alert("El nombre y apellido solo pueden contener letras (sin espacios ni números).");
        return; // BLOQUEA EL ENVÍO
    }

    const formData = new FormData();
    formData.append('nombre', nom);
    formData.append('apellido', ape);

    try {
        const response = await fetch('actualizar_cuenta_completa.php', {
            method: 'POST',
            body: formData
        });
        const res = await response.json();

        if(res.status === 'success') {
            alert("¡Nombre y Apellido actualizados correctamente!");
            window.location.reload(); 
        } else {
            alert("Error: " + res.message);
        }
    } catch (e) {
        console.error("Error en la conexión:", e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const btnAbrirPass = document.getElementById('btn-abrir-pass');
    const plantillaPass = document.getElementById('plantilla-editar-pass');

    if (btnAbrirPass && plantillaPass) {
        btnAbrirPass.addEventListener('click', () => {
            cerrarOtrosModulos('plantilla-editar-pass'); // <--- NUEVO
            plantillaPass.classList.toggle('hidden');
        });
    }
});

function validarPassword(password) {
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/;
    return regex.test(password);
}


async function actualizarPassword() {
    const actual = document.getElementById('pass-actual').value.trim();
    const nueva = document.getElementById('pass-nueva').value.trim();
    const repetir = document.getElementById('pass-repetir').value.trim();

    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/;

    if (!actual || !nueva || !repetir) {
        alert("⚠️ Todos los campos son obligatorios");
        return;
    }

    if (!regex.test(nueva)) {
        alert("❌ La nueva contraseña no cumple los requisitos");
        return;
    }

    if (nueva !== repetir) {
        alert("❌ Las contraseñas no coinciden");
        return;
    }

    const formData = new FormData();
    formData.append('actual', actual);
    formData.append('nueva', nueva);

    try {
        const res = await fetch('cambiar_password.php', {
            method: 'POST',
            body: formData
        });

        const text = await res.text();
        console.log('RESPUESTA RAW:', text);

        const data = JSON.parse(text);

        if (data.success) {
            alert("✅ Contraseña actualizada correctamente");
            document.getElementById('plantilla-editar-pass').classList.add('hidden');
        } else {
            alert("❌ " + data.message);
        }

    } catch (error) {
        console.error(error);
        alert("❌ Error de conexión");
    }
}





// ABRIR / CERRAR PANEL DE CORREO
document.getElementById('btn-cam-c').addEventListener('click', () => {
    cerrarOtrosModulos('plantilla-editar-correo'); // <--- NUEVO
    document.getElementById('plantilla-editar-correo').classList.toggle('hidden');
});

// FUNCIÓN PARA ACTUALIZAR CORREO
async function actualizarCorreo() {
    const correo = document.getElementById('correo-nuevo').value.trim();

    // VALIDACIÓN: solo gmail o hotmail
    const regexCorreo = /^[a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.com)$/;

    if (!regexCorreo.test(correo)) {
        alert("❌ Solo se permiten correos @gmail.com o @hotmail.com");
        return;
    }

    const formData = new FormData();
    formData.append('correo', correo);

    try {
        const res = await fetch('actualizar_correo.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        if (data.status === 'success') {
            alert("✅ Correo actualizado correctamente");
            window.location.reload();
        } else {
            alert("❌ " + data.message);
        }

    } catch (err) {
        console.error(err);
        alert("❌ Error de conexión");
    }
}


</script>



<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
</body>
</html>
