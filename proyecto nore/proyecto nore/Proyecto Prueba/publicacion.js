document.addEventListener('DOMContentLoaded', () => {
    const btnPublicarIcon = document.getElementById('btn-publicar'); 
    const inputFoto = document.getElementById('input-foto');
    const modalOverlay = document.getElementById('publicacion-modal-overlay'); 
    const mediaPreviewContainer = document.getElementById('media-contenedor'); 
    const captionTextarea = document.getElementById('c-textarea'); 
    const publishButton = document.getElementById('Publicar-boton');
    const cancelButton = document.getElementById('cancelar-boton');
    
    let currentFile = null; 

    // MOSTRAR Y OCULTAR MODAL
    const showForm = () => {
        modalOverlay.classList.remove('hidden');
        captionTextarea.focus(); 
    };

    const hideForm = () => {
        modalOverlay.classList.add('hidden'); 
        mediaPreviewContainer.innerHTML = ''; 
        captionTextarea.value = ''; 
        inputFoto.value = ''; 
        currentFile = null;

        const radioPersonal = document.querySelector('input[name="destino"][value="personal"]');
        if (radioPersonal) radioPersonal.checked = true;
        const selectInteres = document.getElementById('select-interes-contenido');
        if (selectInteres) selectInteres.value = '';
    };

    // FUNCIÓN PARA ENVIAR A LA BASE DE DATOS
    const handlePublish = async (file, caption) => {
        const destino = document.querySelector('input[name="destino"]:checked').value;
        const selectInteres = document.getElementById('select-interes-contenido');

        const formData = new FormData();
        formData.append('archivo', file);
        formData.append('caption', caption);
        formData.append('tipo_perfil', destino);

        if (destino === 'contenido') {
            const interesIdRaw = selectInteres ? (selectInteres.value || '') : '';
            const interesId = parseInt(interesIdRaw, 10);
            if (!interesId || Number.isNaN(interesId)) {
                alert('Selecciona un interés para publicar en Contenido');
                return;
            }
            formData.append('interes_id', String(interesId));
        }

        console.log('[Publicar] destino:', destino);
        console.log('[Publicar] select existe:', !!selectInteres);
        console.log('[Publicar] interes_id value:', selectInteres ? selectInteres.value : null);

        try {
            const response = await fetch('guardar_publicacion.php', {
                method: 'POST',
                body: formData
            });
            const res = await response.json();

            if (res.success) {
                alert(`✅ Publicado en perfil ${destino}`);
                location.reload(); 
            } else {
                const debugTxt = (res && res.debug) ? ("\n\nDEBUG:\n" + JSON.stringify(res.debug, null, 2)) : '';
                alert("Error: " + (res.error || 'Error desconocido') + debugTxt);
                console.log('[Publicar] respuesta error:', res);
            }
        } catch (error) {
            console.error("Error:", error);
            alert("Error al conectar con el servidor");
        }
    };

    // EVENTOS
// EVENTOS - Verifica que los elementos existan antes de asignar el click
if (btnPublicarIcon) {
    btnPublicarIcon.addEventListener('click', () => inputFoto.click());
}

if (inputFoto) {
    inputFoto.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (!file) return;

        currentFile = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            if (mediaPreviewContainer) {
                mediaPreviewContainer.innerHTML = file.type.startsWith('image/') 
                    ? `<img src="${e.target.result}" class="w-full rounded">`
                    : `<video src="${e.target.result}" controls class="w-full rounded"></video>`;
                showForm(); 
            }
        };
        reader.readAsDataURL(file);
    });
}

if (cancelButton) {
    cancelButton.addEventListener('click', hideForm);
}

if (publishButton) {
    publishButton.addEventListener('click', () => {
        if (!currentFile) return alert('Selecciona un archivo');
        handlePublish(currentFile, captionTextarea.value);
    });
}
const inputFotoPerfil = document.getElementById('input-foto-perfil');
if (inputFotoPerfil) {
    inputFotoPerfil.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('foto', file);

        const response = await fetch('actualizar_foto.php', {
            method: 'POST',
            body: formData
        });
        const res = await response.json();

        if (res.success) {
            const contenedor = document.getElementById('contenedor-foto-perfil');
            // Actualizamos el HTML para que sea una imagen que cubra todo
            contenedor.innerHTML = `<img src="${res.ruta}?t=${new Date().getTime()}" class="w-full h-full object-cover">`;
            
            // IMPORTANTE: Limpiar el input para permitir subir la misma foto o cambiarla de nuevo inmediatamente
            inputFotoPerfil.value = ""; 
            alert("Foto de perfil actualizada");
        }
    });
}
});
// REACCIÓN (LIKE)
async function reaccionar(postId) {
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('accion', 'reaccionar');

    const response = await fetch('interacciones.php', { method: 'POST', body: formData });
    const res = await response.json();
    
    const icon = document.getElementById(`heart-${postId}`);
    const count = document.getElementById(`count-${postId}`);
    
    count.innerText = res.total; // Actualiza el número (1, 2, 3...)

    if (res.status === 'added') {
        icon.setAttribute('name', 'heart');
        icon.style.color = 'red';
    } else {
        icon.setAttribute('name', 'heart-outline');
        icon.style.color = 'black';
    }
}

// ENFOCAR INPUT
function enfocarComentario(postId) {
    document.getElementById(`input-com-${postId}`).focus();
}

let enviandoComentario = false;

async function enviarComentario(postId) {
    if (enviandoComentario) return;

    const input = document.getElementById(`input-com-${postId}`);
    const texto = input.value.trim();
    if (!texto) return;

    try {
        enviandoComentario = true;
        input.disabled = true;

        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('accion', 'comentar');
        formData.append('contenido', texto);

        const response = await fetch('interacciones.php', { method: 'POST', body: formData });
        const res = await response.json();

        if (res.status === 'success') {
            const lista = document.getElementById(`comentarios-lista-${postId}`);
            if (lista) {
                const nuevoDiv = document.createElement('div');
                // IMPORTANTE: Asignar el ID exacto que espera la función eliminarElemento
                nuevoDiv.id = `comentario-${res.id}`; 
                nuevoDiv.className = 'flex justify-between items-start py-1';

                nuevoDiv.innerHTML = `
                    <p class="text-sm">
                        <span class="font-bold">${res.nombre}:</span> 
                        <span>${texto}</span>
                    </p>
                    <button onclick="eliminarElemento(${res.id}, 'comentario')" 
                            class="text-red-500 hover:text-red-700 ml-2">
                        <ion-icon name="trash-outline" style="font-size: 16px;"></ion-icon>
                    </button>
                `;
                lista.appendChild(nuevoDiv);
            }
            input.value = '';
        }
    } catch (error) {
        console.error("Error:", error);
    } finally {
        enviandoComentario = false;
        input.disabled = false;
        input.focus();
    }
}
// Función para reaccionar desde el MODAL (Notificaciones/Detalle)
window.reaccionarModal = async function(postId) {
    const icon = document.getElementById(`modal-heart-${postId}`);
    const countEl = document.getElementById(`modal-count-${postId}`);

    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('accion', 'reaccionar');

    try {
        const response = await fetch('interacciones.php', { method: 'POST', body: formData });
        const res = await response.json();

        if (res.status === 'added') {
            icon.setAttribute('name', 'heart');
            icon.style.color = 'red';
        } else {
            icon.setAttribute('name', 'heart-outline');
            icon.style.color = 'black';
        }

        if (countEl) {
            countEl.innerText = `${res.total} reacciones`;
        }
    } catch (error) {
        console.error("Error en la reacción:", error);
    } // <-- AQUÍ FALTABA CERRAR EL TRY
}; // <-- Y AQUÍ SE CIERRA LA FUNCIÓN
window.enviarComentarioModal = async function(postId) {
    const input = document.getElementById(`modal-input-com-${postId}`);
    if (!input) return;

    const texto = input.value.trim();
    if (!texto) return;

    input.disabled = true;

    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('accion', 'comentar');
    formData.append('contenido', texto);

    try {
        const response = await fetch('interacciones.php', { method: 'POST', body: formData });
        const res = await response.json();

        if (res.status === 'success') {
            // Buscamos la lista donde deberían salir los comentarios
            const lista = document.getElementById(`comentarios-lista-${postId}`);

            if (lista) {
                // Opción A: Dibujarlo manualmente (lo que estaba fallando)
                const nuevoDiv = document.createElement('div');
                nuevoDiv.className = 'flex justify-between items-start py-1 border-b border-gray-50';
                nuevoDiv.innerHTML = `
                    <p class="text-sm">
                        <span class="font-bold text-black">${res.nombre}:</span> 
                        <span class="text-gray-800">${texto}</span>
                    </p>
                `;
                lista.appendChild(nuevoDiv);
                input.value = '';
                lista.scrollTop = lista.scrollHeight;
            } 
            
            // ¡ESTO ES LO IMPORTANTE! 
            // Independientemente de si lo dibujó arriba o no, llamamos a la función 
            // que carga los datos del post para que el modal se actualice solo.
            if (typeof abrirDetalle === 'function') {
                abrirDetalle(postId);
            } else {
                // Si abrirDetalle no existe en este archivo, recargamos la página
                location.reload();
            }
        }
    } catch (e) {
        console.error("Error al publicar:", e);
    } finally {
        input.disabled = false;
    }
};
// FORZAR QUE LA FUNCIÓN SEA GLOBAL
let intervaloDetalle = null;

window.abrirDetalle = async function(postId) {
    const modal = document.getElementById('modal-detalle');
    const contenedor = document.getElementById('contenido-detalle');
    
    if (!modal) return;

    // Mostrar modal
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Bloquea scroll de fondo

    try {
        const response = await fetch('obtener_detalle.php?id=' + postId);
        const html = await response.text();
        contenedor.innerHTML = html;
        
        // Reiniciar scroll del contenedor interno al tope
        contenedor.scrollTop = 0;
    } catch (e) {
        contenedor.innerHTML = '<p>Error al cargar.</p>';
    }
};

window.cerrarModalDirecto = function() {
    const modal = document.getElementById('modal-detalle');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Libera scroll de fondo
    }
};

// Actualiza también esta función para que use la misma lógica
window.cerrarDetalle = function(e) {
    if (e.target.id === 'modal-detalle') {
        window.cerrarModalDirecto();
    }
};
window.eliminarElemento = async function(id, tipo) {
    // 1. Mensaje de confirmación según el tipo
    const mensaje = tipo === 'publicacion' ? "¿Eliminar TODA la publicación?" : "¿Eliminar este comentario?";
    if (!confirm(mensaje)) return;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('tipo', tipo);

    try {
        const res = await fetch('eliminar.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            // --- LÓGICA DE BORRADO MULTI-ZONA ---
            
            // Definimos el selector: #comentario-123 o #publicacion-123
            const selector = tipo === 'comentario' ? `#comentario-${id}` : `#publicacion-${id}`;
            
            // Buscamos TODOS los elementos que coincidan en toda la página
            const elementosEnPantalla = document.querySelectorAll(selector);

            if (elementosEnPantalla.length > 0) {
                elementosEnPantalla.forEach(el => {
                    // Añadimos una animación rápida de salida
                    el.style.transition = "all 0.3s ease";
                    el.style.opacity = "0";
                    el.style.transform = "scale(0.9)";
                    
                    // Lo eliminamos físicamente del HTML tras la animación
                    setTimeout(() => el.remove(), 300);
                });
            }

            // 2. Si borraste una publicación desde el modal, lo cerramos
            if (tipo === 'publicacion') {
                const modal = document.getElementById('modal-detalle');
                if (modal) modal.classList.add('hidden');
                
                // Si tienes una función específica para cerrar, úsala:
                if (typeof cerrarDetalle === 'function') {
                     // Solo si necesitas limpiar estados adicionales
                }
            }

            console.log(`✅ ${tipo} eliminado visualmente de todos los contenedores.`);

        } else {
            alert("Error: " + (data.error || "No tienes permisos para eliminar esto."));
        }
    } catch (e) {
        console.error("Error en la petición:", e);
        alert("Error de conexión con el servidor.");
    }
};
async function mandarRepublicacion(id) {
    console.log("Intentando enviar post ID:", id); // Esto DEBE salir en consola
    
    if (!confirm("¿Deseas compartir esto en tu muro?")) return;

    let formData = new FormData();
    formData.append('post_id', id);

    try {
        let respuesta = await fetch('republicar.php', {
            method: 'POST',
            body: formData
        });

        let resultado = await respuesta.json();
        console.log("Respuesta del servidor:", resultado);

        if (resultado.success) {
            alert("✅ ¡Republicado con éxito!");
            window.location.href = "Perfil.php"; // Te manda directo a ver tu logro
        } else {
            alert("❌ Error: " + resultado.error);
        }
    } catch (e) {
        console.error("Fallo la conexión:", e);
        alert("No se pudo conectar con el servidor.");
    }
}

window.deslizarCarrusel = function(direccion) {
    const carrusel = document.getElementById('carrusel-fotos');
    if (carrusel) {
        // Desplazamos el 80% del ancho visible para que se sienta fluido
        const desplazamiento = carrusel.clientWidth * 0.8; 
        carrusel.scrollBy({
            left: direccion * desplazamiento,
            behavior: 'smooth'
        });
    } else {
        console.error("No se encontró el elemento #carrusel-fotos");
    }
};
window.eliminarRepublicacion = async function(repostId) {
    if (!confirm("¿Deseas quitar esta republicación de tu perfil?")) return;

    const formData = new FormData();
    formData.append('id', repostId);

    try {
        const res = await fetch('eliminar_republicacion.php', { 
            method: 'POST', 
            body: formData 
        });
        const data = await res.json();
        
        if (data.success) {
            alert("✅ Republicación eliminada");
            location.reload(); 
        } else {
            alert("❌ Error: " + (data.error || "No se pudo eliminar"));
        }
    } catch (e) { 
        alert("Fallo la conexión con el servidor"); 
    }
};