<template>
  <main>
    <div class="contenedor__todo">
      
      <button class="theme-toggle-button" @click="toggleTheme" title="Cambiar Tema">
          <i :class="isDarkTheme ? 'fas fa-sun' : 'fas fa-moon'"></i>
      </button>

      <div class="caja__trasera">
        <div 
          class="caja__trasera-login"
          :style="{ opacity: isRegistering ? 1 : (isLargeScreen ? 0 : 1), display: isLargeScreen ? 'block' : (isRegistering ? 'block' : 'none') }"
        >
          <h3>¿Ya tienes una cuenta?</h3>
          <p>Inicia sesión para entrar en la página</p>
          <button id="btn__iniciar-sesion" @click="iniciarSesion">Iniciar Sesión</button>
        </div>

        <div 
          class="caja__trasera-register"
          :style="{ opacity: isRegistering ? (isLargeScreen ? 0 : 1) : 1, display: isLargeScreen ? 'block' : (!isRegistering ? 'block' : 'none') }"
        >
          <h3>¿Aún no tienes una cuenta?</h3>
          <p>Regístrate para que puedas iniciar sesión</p>
          <button id="btn__registrarse" @click="register">Registrarse</button>
        </div>
      </div>

      <div 
        class="contenedor__login-register"
        :style="{ left: containerLeft }"
      >
        
        <form @submit.prevent="handleLogin" class="formulario__login" v-show="!isRegistering">
          <h2>Iniciar Sesión</h2>

          <input type="text" v-model="loginForm.username" placeholder="Usuario" required>

          <div class="input-con-icono">
            <input
              :type="loginForm.passwordVisible ? 'text' : 'password'"
              v-model="loginForm.password"
              placeholder="Contraseña"
              required
            >
            <i
              :class="['fa-solid', loginForm.passwordVisible ? 'fa-eye-slash' : 'fa-eye']"
              @click="loginForm.passwordVisible = !loginForm.passwordVisible"
            ></i>
          </div>

          <button type="submit" :disabled="loginLoading" :style="{ opacity: loginLoading || registerLoading ? 0.7 : 1, cursor: loginLoading || registerLoading ? 'not-allowed' : 'pointer' }">
            <i v-if="loginLoading" class="fas fa-spinner fa-spin"></i>
            {{ loginLoading ? 'Verificando...' : 'Entrar' }}
          </button>
        </form>

        <form @submit.prevent="handleRegister" class="formulario__register" v-show="isRegistering">
          <h2>Registrarse</h2>
          <input type="text" v-model="registerForm.name" placeholder="Nombre completo" required>
          <input type="text" v-model="registerForm.username" placeholder="Usuario" required>
          <input type="email" v-model="registerForm.email" placeholder="Correo electrónico" required>

          <div class="input-con-icono">
            <input
              :type="registerForm.passwordVisible ? 'text' : 'password'"
              v-model="registerForm.password"
              placeholder="Contraseña"
              required
            >
            <i
              :class="['fa-solid', registerForm.passwordVisible ? 'fa-eye-slash' : 'fa-eye']"
              @click="registerForm.passwordVisible = !registerForm.passwordVisible"
            ></i>
          </div>

          <div class="input-con-icono">
            <input
              :type="registerForm.passwordConfirmVisible ? 'text' : 'password'"
              v-model="registerForm.password_confirmation"
              placeholder="Confirmar Contraseña"
              required
            >
            <i
              :class="['fa-solid', registerForm.passwordConfirmVisible ? 'fa-eye-slash' : 'fa-eye']"
              @click="registerForm.passwordConfirmVisible = !registerForm.passwordConfirmVisible"
            ></i>
          </div>

          <input type="password" v-model="registerForm.auth_code" placeholder="Código de Autorización" required>

          <button type="submit" :disabled="registerLoading">
            <i v-if="registerLoading" class="fas fa-spinner fa-spin"></i>
            {{ registerLoading ? 'Registrando...' : 'Registrarse' }}
          </button>
        </form>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useRouter } from 'vue-router';
// Importa SweetAlert2 para mensajes de error
import Swal from 'sweetalert2';

// --- VARIABLES REACTIVAS Y ESTADO ---
const router = useRouter();
const isRegistering = ref(false);
const isLargeScreen = ref(window.innerWidth > 850);
const isDarkTheme = ref(false);

const loginLoading = ref(false);
const registerLoading = ref(false);
const API_URL = 'http://127.0.0.1:8000/api';

const loginForm = reactive({
    username: '',
    password: '',
    passwordVisible: false,
});

const registerForm = reactive({
    name: '',
    username: '',
    password: '',
    password_confirmation: '',
    auth_code: '',
    passwordVisible: false,
    passwordConfirmVisible: false,
});

// --- LÓGICA DE TEMA (Modo Claro/Oscuro) ---
// (Mantenida como estaba)

const checkTheme = () => {
    const currentTheme = localStorage.getItem('theme') || 'light';
    isDarkTheme.value = currentTheme === 'dark';
    document.body.className = `theme-${currentTheme}`;
};

const toggleTheme = () => {
    const newTheme = isDarkTheme.value ? 'light' : 'dark';
    localStorage.setItem('theme', newTheme);
    checkTheme();
};


// --- LÓGICA DE INTERFAZ (SLIDER) ---


const containerLeft = computed(() => {
    if (isLargeScreen.value) {
        return isRegistering.value ? '410px' : '10px';
    }
    return '0px';
});

const anchoPage = () => {
    isLargeScreen.value = window.innerWidth > 850;
};

const iniciarSesion = () => {
    isRegistering.value = false;
    if (!isLargeScreen.value) {
        document.querySelector('.caja__trasera-register').style.display = 'block';
        document.querySelector('.caja__trasera-login').style.display = 'none';
    }
};

const register = () => {
    isRegistering.value = true;
    if (!isLargeScreen.value) {
        document.querySelector('.caja__trasera-register').style.display = 'none';
        document.querySelector('.caja__trasera-login').style.display = 'block';
    }
};


// --- LÓGICA DE AUTENTICACIÓN (API CALLS) ---

const checkAuth = async () => {
    const token = localStorage.getItem('auth_token');
    if (!token) return;

    try {
        const response = await fetch(`${API_URL}/check-auth`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
        });

        if (response.ok) {
            router.push('/inicio');
        } else {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
        }
    } catch (error) {
        console.error('Sesión no válida:', error);
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
    }
};

// 2. Manejo del Login con SweetAlert2 para errores
const handleLogin = async () => {
    loginLoading.value = true;
    try {
        const payload = {
            username: loginForm.username,
            password: loginForm.password,
        };

        const response = await fetch(`${API_URL}/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
            // Muestra mensaje de error específico para credenciales
            Swal.fire({
                icon: 'error',
                title: 'Error de Credenciales',
                text: data.message || 'Credenciales incorrectas. Por favor, inténtelo de nuevo.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        localStorage.setItem('auth_token', data.access_token);
        localStorage.setItem('user', JSON.stringify(data.user));

        router.push('/inicio');

    } catch (error) {
        console.error('Error de Login:', error);
        // Muestra error general de conexión/servidor
        Swal.fire({
            icon: 'error',
            title: 'Error de Conexión',
            text: 'No se pudo conectar con el servidor de autenticación.',
            confirmButtonText: 'Aceptar'
        });
    } finally {
        loginLoading.value = false;
    }
};

// 3. Manejo del Registro con validación de 8 dígitos y SweetAlert2
const handleRegister = async () => {
    registerLoading.value = true;
    try {
        // Validación local: Contraseña de 8 dígitos
        if (registerForm.password.length < 8) {
            throw new Error('La contraseña debe tener al menos 8 caracteres.');
        }
        if (registerForm.password !== registerForm.password_confirmation) {
             throw new Error('Las contraseñas no coinciden.');
        }

        const response = await fetch(`${API_URL}/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(registerForm),
        });

        const data = await response.json();

        if (!response.ok) {
            const errors = data.errors || {};
            let errorMessage = data.message || 'Error en el registro';

            for (const field in errors) {
                // Mensaje detallado del error de Laravel (ej: username ya existe)
                errorMessage += `\n- ${errors[field].join(', ')}`;
            }
            
            throw new Error(errorMessage);
        }

        // Éxito
        Swal.fire({
            icon: 'success',
            title: 'Registro Exitoso',
            text: 'Usuario creado. ¡Ahora puedes iniciar sesión!',
            confirmButtonText: 'Aceptar'
        });
        
        iniciarSesion();

        // Limpiar formulario de registro
        Object.assign(registerForm, {
            name: '', username: '', password: '', password_confirmation: '', auth_code: '',
            passwordVisible: false, passwordConfirmVisible: false
        });

    } catch (error) {
        console.error('Error de Registro:', error);
        
        // Muestra el error de validación local o del backend
        Swal.fire({
            icon: 'error',
            title: 'Error de Registro',
            text: error.message,
            confirmButtonText: 'Aceptar'
        });
    } finally {
        registerLoading.value = false;
    }
};


// --- CICLO DE VIDA ---

onMounted(() => {
    // Inicializa el manejo de la pantalla
    window.addEventListener("resize", anchoPage);
    // Ejecuta la verificación de autenticación
    checkAuth();
    // Carga y aplica el tema
    checkTheme();

    // Si estás en pantalla pequeña, fuerza la vista de login al cargar
    if (window.innerWidth <= 850) {
        nextTick(() => {
            document.querySelector('.caja__trasera-login').style.display = 'none';
        });
    }
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", anchoPage);
});
</script>
<style scoped>
/* --- Estilos del Componente --- */

main {
    width: 100%;
    padding: 20px;
    margin: auto;
    margin-top: 100px;
}

.contenedor__todo {
    width: 100%;
    max-width: 800px;
    margin: auto;
    position: relative;
}

/* --- Botón de Cambio de Tema (CÍRCULO MINIMALISTA) --- */
.theme-toggle-button {
    /* Posición FIJA en el viewport, evita el scroll indeseado */
    position: fixed; 
    top: 20px; 
    right: 20px; 
    z-index: 1000; 
    
    /* Estilos del botón circular */
    width: 40px; 
    height: 40px; 
    border-radius: 50%; 
    cursor: pointer;
    transition: all 0.3s ease;
    
    /* Colores: Invertidos para que se vea en el fondo negro */
    background-color: var(--color-form-background); 
    border: 1px solid var(--color-input-border); 
    padding: 0; 
    
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Hover/Focus */
.theme-toggle-button:hover {
    box-shadow: 0 0 8px var(--color-primary);
}

/* ÍCONO (SOL/LUNA) */
.theme-toggle-button i {
    font-size: 1.1rem;
    
    /* Color basado en el tema para alto contraste */
    color: var(--color-primary); 
    transition: color 0.3s ease;
}

/* ------------------------------------------- */
/* --- ESTILOS DE LOGIN/REGISTRO --- */
/* ------------------------------------------- */

/* Caja trasera */
.caja__trasera {
    width: 100%; padding: 10px 20px; display: flex; justify-content: center;
    backdrop-filter: blur(10px); background-color: var(--color-secondary); border-radius: 15px;
}

.caja__trasera div {
    margin: 80px 40px; color: var(--color-background); transition: all 500ms; text-align: center;
}

.caja__trasera div h3 {
    color: var(--color-text); margin-left: 25px; font-weight: 500; font-size: 24px;
}

.caja__trasera button {
    padding: 10px 40px; border: 2px solid var(--color-text); font-size: 14px; background: transparent;
    font-weight: 600; cursor: pointer; color: var(--color-text); outline: none; transition: all 300ms;
    border-radius: 5px;
}

.caja__trasera button:hover {
    background: var(--color-button-hover); color: var(--color-button-text-hover);
}


/* Formularios */
.contenedor__login-register {
    display: flex; align-items: center; width: 100%; max-width: 400px; 
    position: absolute; 
    top: 200px; 
    transition: left 500ms cubic-bezier(0.175, 0.885, 0.320, 1.275);
}

.contenedor__login-register form {
    width: 100%; padding: 60px 20px; background: var(--color-form-background); position: absolute;
    border-radius: 15px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.contenedor__login-register form h2 {
    font-size: 28px; text-align: center; margin-bottom: 15px; color: var(--color-text); 
}

.contenedor__login-register form input {
    width: 100%; margin-top: 15px; padding: 12px; border: 1px solid var(--color-input-border);
    background: var(--color-input-background); font-size: 16px; outline: none; border-radius: 5px;
    color: var(--color-text);
}

/* Esto asegura que el placeholder también cambie de color */
.contenedor__login-register form input::placeholder {
    color: var(--color-text); opacity: 0.7;
}


.contenedor__login-register form button {
    padding: 12px 40px; margin-top: 30px; border: none; font-size: 16px; background: var(--color-primary);
    font-weight: 600; cursor: pointer; color: var(--color-background); outline: none; border-radius: 5px;
    width: 100%;
}

/* Estilo del ojo para la contraseña */
.input-con-icono { position: relative; }
.input-con-icono input { width: 100%; padding-right: 40px; }
.input-con-icono i {
    position: absolute; top: calc(50% + 7.5px); right: 15px; transform: translateY(-50%);
    cursor: pointer; color: var(--color-text);
}

/* --- El resto del CSS (Responsive, etc.) --- */
.caja__trasera div p,
.caja__trasera button {
    margin-top: 20px;
    color: var(--color-text);
}

.contenedor__login-register form {
    transition: box-shadow 0.3s ease, border 0.3s ease;
}

.contenedor__login-register form:hover {
    box-shadow: 
        0 0 5px var(--color-text),
        0 0 10px var(--color-text),
        0 0 15px var(--color-text),
        0 0 20px var(--color-text);
    border: 1px solid var(--color-text);
}

.formulario__login:hover,
.formulario__register:hover {
    box-shadow: 
        0 0 10px var(--color-text),
        0 0 20px var(--color-text),
        0 0 30px var(--color-text),
        0 0 40px var(--color-text);
}



/* Responsive */
@media screen and (max-width: 850px) {

    .theme-toggle-button {
        top: 10px; right: 10px; width: 40px; height: 40px;
    }

    main { margin-top: 50px; }
    .caja__trasera { max-width: 350px; height: 300px; flex-direction: column; margin: auto; }
    .caja__trasera div { margin: 0px; position: absolute; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; }
    .contenedor__login-register { top: -10px; left: -5px; margin: auto; }
}
</style>
