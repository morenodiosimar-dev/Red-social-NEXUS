// =====================
// MÓDULO INICIAL
// =====================
document.addEventListener('DOMContentLoaded', () => {
    const modulo1 = document.getElementById('Modulo1');
    const modulo2 = document.getElementById('Modulodos');
    const duracionModulo = 3000;

    setTimeout(() => {
        if (modulo1) modulo1.classList.add('hidden');
        if (modulo2) modulo2.style.display = "block";
    }, duracionModulo);
});

// ============================
// RECUPERACIÓN DE CONTRASEÑA
// ============================
document.addEventListener('DOMContentLoaded', () => {
    const enlaceOlvide = document.querySelector('a[href="recuperar_password.php"]');
    const moduloLogin = document.getElementById('Modulodos');
    const formularioRecuperar = document.getElementById('form-recuperar');
    const moduloRecuperar = document.getElementById('Modulo1-recuperar');
    const moduloOriginal = document.getElementById('Modulo1');
    
    if (enlaceOlvide && moduloLogin && formularioRecuperar && moduloRecuperar) {
        enlaceOlvide.addEventListener('click', (e) => {
            e.preventDefault();
            moduloLogin.style.display = 'none';
            moduloOriginal.style.display = 'none';
            moduloRecuperar.style.display = 'block';
            formularioRecuperar.style.display = 'block';
        });
    }
    
    // Botón regresar
    const btnRegresarLogin = document.getElementById('btnRegresarLogin');
    if (btnRegresarLogin) {
        btnRegresarLogin.addEventListener('click', () => {
            formularioRecuperar.style.display = 'none';
            moduloRecuperar.style.display = 'none';
            moduloOriginal.style.display = 'block';
            moduloLogin.style.display = 'block';
        });
    }
});

// ============================
// CAMBIO A FORMULARIO REGISTRO
// ============================
document.addEventListener("DOMContentLoaded", () => {
    const btnRegistrarse = document.getElementById("btnregistrarse1");
    const moduloLogin = document.getElementById("Modulodos");
    const formularioRegistro = document.getElementById("form-registro");

    if (!btnRegistrarse) return;

    btnRegistrarse.addEventListener("click", () => {
        if (moduloLogin) moduloLogin.style.display = "none";
        if (formularioRegistro) {
            formularioRegistro.style.display = "flex";
            formularioRegistro.style.left = "0%";
        }
    });
});

// ============================
// VALIDACIONES REGEX
// ============================
function validarNombreApellido(valor) {
    return /^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/.test(valor.trim());
}

function validarCorreo(correo) {
    return /^[a-z0-9._%+-]+@(gmail\.com|hotmail\.com)$/.test(correo.toLowerCase());
}

function validarTelefono(valor) {
    return /^[0-9]+$/.test(valor.trim());
}

function validarContrasena(valor) {
    return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(valor);
}

function validarEdadMinima(fechaNacimiento) {
    if (!fechaNacimiento) return false;

    const hoy = new Date();
    const fechaNac = new Date(fechaNacimiento);

    // Calcular la edad
    let edad = hoy.getFullYear() - fechaNac.getFullYear();
    const mes = hoy.getMonth() - fechaNac.getMonth();

    // Ajustar si aún no ha cumplido años este año
    if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
        edad--;
    }

    return edad >= 12;
}


// ============================
// VALIDAR INICIO DE SESIÓN
// ============================
document.addEventListener("DOMContentLoaded", () => {
    const formLogin = document.querySelector("#Modulodos form");
    const usuario = document.getElementById("usuario");
    const pass = document.getElementById("contraseña");

    if (!formLogin) return;

    formLogin.addEventListener("submit", async (e) => {
        e.preventDefault();

        if (!usuario.value.trim() || !pass.value.trim()) {
            alert("Debe llenar todos los campos.");
            return;
        }

        const formData = new FormData();
        formData.append("correo", usuario.value.trim());
        formData.append("contrasena", pass.value.trim());

        try {
            const response = await fetch("login.php", {
                method: "POST",
                body: formData
            });

            const resultado = await response.json();

            if (resultado.success) {
                formLogin.reset();
                // ESTA ES LA CLAVE: Redirigir al archivo de la cuenta
                window.location.href = "cuenta.php";
            } else {
                alert(resultado.message);
            }

        } catch (error) {
            console.error("Error real en login:", error);
            alert("Error de conexión con el servidor.");
        }
    });
});

// ============================
// FUNCIONES AUXILIARES
// ============================
function aplicarEstilo(elemento, esValido) {
    elemento.style.border = esValido ? "" : "2px solid red";
}

function limpiarErrorAlEnfocar(inputElement, errorElement) {
    inputElement.addEventListener("focus", () => {
        inputElement.style.border = "";
        errorElement.textContent = "";
    });
}

// ============================
// VALIDACIÓN REGISTRO
// ============================
document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.getElementById("regis");
    if (!formulario) return;

    const nombre = document.getElementById("name_");
    const apellido = document.getElementById("apellido");
    const correo = document.getElementById("correo");
    const telefono = document.getElementById("telefono");
    const contraseña = document.getElementById("contrasena");
    const fechaN = document.getElementById("FechaN");
    const sexo = document.getElementById("sexo");

    const errorName = document.getElementById("error-name");
    const errorApellido = document.getElementById("error-apellido");
    const errorCorreo = document.getElementById("error-correo");
    const errorTelefono = document.getElementById("error-telefono");
    const errorContrasena = document.getElementById("error-contrasena");
    const errorFecha = document.getElementById("error-fecha");
    const errorSexo = document.getElementById("error-sexo");

    const campos = [
        { input: nombre, error: errorName },
        { input: apellido, error: errorApellido },
        { input: correo, error: errorCorreo },
        { input: telefono, error: errorTelefono },
        { input: contraseña, error: errorContrasena },
        { input: fechaN, error: errorFecha },
        { input: sexo, error: errorSexo }
    ];

    campos.forEach(c => limpiarErrorAlEnfocar(c.input, c.error));

    formulario.addEventListener("submit", async (e) => {
        let valido = true;

        campos.forEach(c => {
            c.error.textContent = "";
            aplicarEstilo(c.input, true);
        });

        if (!validarNombreApellido(nombre.value)) {
            errorName.textContent = "Solo letras sin espacios.";
            valido = false;
        }

        if (!validarNombreApellido(apellido.value)) {
            errorApellido.textContent = "Solo letras sin espacios.";
            valido = false;
        }

        if (!validarCorreo(correo.value)) {
            errorCorreo.textContent = "Correo inválido.";
            valido = false;
        }

        if (!validarTelefono(telefono.value)) {
            errorTelefono.textContent = "Solo números.";
            valido = false;
        }

        if (!validarContrasena(contraseña.value)) {
            errorContrasena.textContent = "Contraseña insegura.";
            valido = false;
        }

        if (!fechaN.value) {
            errorFecha.textContent = "Seleccione fecha.";
            valido = false;
        } else if (!validarEdadMinima(fechaN.value)) {
            errorFecha.textContent = "Debes tener al menos 12 años para registrarte.";
            aplicarEstilo(fechaN, false);
            valido = false;
        }

        if (!sexo.value || sexo.value.includes("Seleccione")) {
            errorSexo.textContent = "Seleccione sexo.";
            valido = false;
        }

        // Si no es válido, prevenir el envío
        if (!valido) {
            e.preventDefault();
            return;
        }

        // Si es válido, dejar que el formulario se envíe normalmente
        // PHP hará la redirección automáticamente
        console.log("Formulario válido, enviando...");
    });

    const btnRegresar = document.getElementById("btnRegresar");
    if (btnRegresar) {
        btnRegresar.addEventListener("click", () => {
            formulario.reset();
            document.getElementById("form-registro").style.display = "none";
            document.getElementById("Modulodos").style.display = "block";
        });
    }
});

/// ============================
// CALENDARIO 
// ============================
document.addEventListener("DOMContentLoaded", () => {
    const inputFecha = document.querySelector("#FechaN");

    // Establecer la fecha máxima como hoy
    if (inputFecha) {
        const hoy = new Date();
        const año = hoy.getFullYear();
        const mes = String(hoy.getMonth() + 1).padStart(2, '0');
        const dia = String(hoy.getDate()).padStart(2, '0');
        inputFecha.setAttribute('max', `${año}-${mes}-${dia}`);
    }

    // Solo se ejecuta si encuentra el ID #FechaN en el HTML
    if (inputFecha && typeof flatpickr !== 'undefined') {
        flatpickr("#FechaN", {
            dateFormat: "Y-m-d",
            maxDate: "today",
            // Opcional: ponerlo en español
            locale: {
                firstDayOfWeek: 1
            }
        });
    }
});
// Lógica de búsqueda en tiempo real
document.addEventListener('DOMContentLoaded', () => {
    const inputBuscar = document.getElementById('input-buscar');
    const contenedorResultados = document.getElementById('resultados-busqueda');

    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', async () => {
            const query = inputBuscar.value.trim();
            
            if (query.length > 1) { // Buscar a partir de 2 letras
                const formData = new FormData();
                formData.append('query', query);
                
                try {
                    const response = await fetch('buscar_usuarios.php', {
                        method: 'POST',
                        body: formData
                    });
                    const html = await response.text();
                    contenedorResultados.innerHTML = html;
                } catch (error) {
                    console.error("Error buscando:", error);
                    contenedorResultados.innerHTML = '<p class="text-center text-gray-400 mt-10">Error al buscar personas...</p>';
                }
            } else {
                contenedorResultados.innerHTML = '<p class="text-center text-gray-400 mt-10">Escribe un nombre para buscar personas...</p>';
            }
        });
    } else {
        contenedorResultados.innerHTML = '<p class="text-center text-gray-400 mt-10">Escribe un nombre para buscar personas...</p>';
    }
});
// Mostrar módulo de recuperación
const linkOlvidaste = document.querySelector(".enlace a");
const moduloRecuperar = document.getElementById("form-recuperar");
const moduloLogin = document.getElementById("Modulodos");

if (linkOlvidaste && moduloRecuperar && moduloLogin) {
    linkOlvidaste.addEventListener("click", (e) => {
        e.preventDefault();
        moduloLogin.style.display = "none";
        moduloRecuperar.style.display = "flex";
        moduloRecuperar.style.left = "0%";
    });
}

// Regresar al login
const btnRegresarLogin = document.getElementById("btnRegresarLogin");
if (btnRegresarLogin && moduloRecuperar && moduloLogin) {
    btnRegresarLogin.addEventListener("click", () => {
        moduloRecuperar.style.display = "none";
        moduloLogin.style.display = "block";
    });
}


// Mostrar módulo de recuperación



if (linkOlvidaste && moduloRecuperar && moduloLogin) {
    linkOlvidaste.addEventListener("click", (e) => {
        e.preventDefault();
        moduloLogin.style.display = "none";
        moduloRecuperar.style.display = "flex";
    });
}

// Regresar al login
if (btnRegresarLogin && moduloRecuperar && moduloLogin) {
    btnRegresarLogin.addEventListener("click", () => {
        moduloRecuperar.style.display = "none";
        moduloLogin.style.display = "block";
        // Resetear pasos
        const pasoCorreo = document.getElementById("paso-correo");
        const pasoNuevaPass = document.getElementById("paso-nueva-pass");
        if (pasoCorreo) pasoCorreo.style.display = "block";
        if (pasoNuevaPass) pasoNuevaPass.style.display = "none";
    });
}

// Lógica de "Continuar" (Verificar Correo)
// Lógica de "Continuar" (Verificar Correo)
const btnVerificarCorreo = document.getElementById("btnVerificarCorreo");
if (btnVerificarCorreo) btnVerificarCorreo.addEventListener("click", async () => {
    const correoInput = document.getElementById("correo-recuperar");
    if (!correoInput) return;
    const correo = correoInput.value.trim();

    if (!validarCorreo(correo)) {
        alert("Ingrese un correo válido");
        return;
    }

    const fd = new FormData();
    fd.append('accion', 'verificar_correo');
    fd.append('correo', correo);

    try {
        const res = await fetch('recuperacion_be.php', { method: 'POST', body: fd });
        const json = await res.json();

        if (json.success) {
            // 1. Insertamos el nombre en el mensaje
            const mensaje = document.getElementById("mensaje-bienvenida");
            mensaje.innerText = `Puedes cambiar tu contraseña, ${json.nombre}`;
            mensaje.style.color = "#00ff00"; // Opcional: ponerlo en verde para resaltar éxito

            // 2. CAMBIO VISUAL DE MÓDULOS
            document.getElementById("paso-correo").style.display = "none";
            document.getElementById("paso-nueva-pass").style.display = "block";

            console.log("Cambiando a módulo de nueva contraseña");
        } else {
            alert(json.message);
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Error de conexión con el servidor");
    }
});

// Lógica de "Actualizar" (Cambiar Contraseña)
const btnCambiarPass = document.getElementById("btnCambiarPass");
if (btnCambiarPass) btnCambiarPass.addEventListener("click", async () => {
    const passEl = document.getElementById("nueva-pass");
    const confEl = document.getElementById("confirmar-pass");
    const correoEl = document.getElementById("correo-recuperar");
    if (!passEl || !confEl || !correoEl) return;

    const pass = passEl.value;
    const conf = confEl.value;
    const correo = correoEl.value;

    if (pass !== conf) return alert("Las contraseñas no coinciden");
    if (!validarContrasena(pass)) return alert("La contraseña debe tener min. 8 caracteres, Mayúscula, Minúscula y Número.");

    const fd = new FormData();
    fd.append('accion', 'actualizar_pass');
    fd.append('correo', correo);
    fd.append('pass', pass);

    try {
        const res = await fetch('recuperacion_be.php', { method: 'POST', body: fd });
        const json = await res.json();

        if (json.success) {
            alert("✅ Contraseña actualizada correctamente");
            window.location.reload(); // Vuelve al inicio
        } else {
            alert(json.message);
        }
    } catch (error) {
        alert("Error al actualizar");
    }
});