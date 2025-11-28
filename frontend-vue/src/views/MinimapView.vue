<template>
  <div class="minimap-view">
    <header class="encabezado">
      <div class="contenedor">
        <div class="logo">
          <router-link to="/inicio"><i class="fas fa-parking"></i>Estacionamiento</router-link>
        </div>

        <button class="boton-menu" @click="toggleMenu">
          <svg v-if="!isMenuOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path d="M4 6H20V8H4zM4 11H20V13H4zM4 16H20V18H4z"/>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path d="M16.192 6.344L11.949 10.586 7.707 6.344 6.293 7.758 10.535 12 6.293 16.242 7.707 17.656 11.949 13.414 16.192 17.656 17.606 16.242 13.364 12 17.606 7.758z"/>
          </svg>
        </button>

        <nav class="menu" :class="{ 'activo': isMenuOpen }">
          <router-link to="/inicio" @click="closeMenu"><i class="fas fa-home"></i> Inicio</router-link>
          <router-link v-if="userRole === 'admin' || userRole === 'gestor'" to="/RegistrarAutoView" @click="closeMenu"><i class="fas fa-car-alt"></i> Registrar Auto</router-link>
          <router-link v-if="userRole === 'admin' || userRole === 'gestor'" to="/SalidaAutoView" @click="closeMenu"><i class="fas fa-sign-out-alt"></i> Salida Auto</router-link>
          <router-link v-if="userRole === 'admin' || userRole === 'gestor'" to="/cuentas" @click="closeMenu"><i class="fas fa-chart-line"></i> Cuentas</router-link>
          <router-link v-if="userRole === 'admin'" to="/admin" @click="closeMenu"><i class="fas fa-cogs"></i> Admin</router-link>

          <div class="seccion-usuario">
            <span><i class="fas fa-user-circle"></i> <span class="user-name">{{ userName }}</span></span>

            <button class="boton-modo" @click="toggleTheme" title="Cambiar Tema">
                <i :class="isDarkTheme ? 'fas fa-sun' : 'fas fa-moon'"></i>
            </button>

            <button class="boton-salir" @click="handleLogout">
              <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </button>
          </div>
        </nav>
      </div>
    </header>

    <main class="contenido-principal">
      <div class="contenedor-mapa">
        <div class="recuadro-mapa">
          <h2><i class="fas fa-map-marked-alt"></i> Ubicación del Estacionamiento</h2>
          <div class="mapa-container">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d3589.7349919966728!2d-96.8543889!3d19.9283333!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zMTnCsDU1JzQyLjAiTiA5NsKwNTEnMTUuOCJX!5e1!3m2!1ses-419!2smx!4v1763759603989!5m2!1ses-419!2smx"
              width="100%"
              height="400"
              style="border:0;"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>
          <div class="info-mapa">
            <div class="info-item">
              <i class="fas fa-map-marker-alt"></i>
              <div>
                <h4>Dirección</h4>
                <p>Díaz Mirón, Centro, 93820 Misantla, Ver.</p>
              </div>
            </div>
            <div class="info-item">
              <i class="fas fa-clock"></i>
              <div>
                <h4>Horario</h4>
                <p>Abierto 24 horas</p>
              </div>
            </div>
            <div class="info-item">
              <i class="fas fa-phone"></i>
              <div>
                <h4>Contacto</h4>
                <p>+52 232 103 7983</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const router = useRouter();

// Estado reactivo
const userName = ref('Cargando...');
const userRole = ref('');
const isMenuOpen = ref(false);
const isDarkTheme = ref(false);
const API_URL = 'http://127.0.0.1:8000/api';

// Lógica de menú móvil
const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
  if (isMenuOpen.value) {
    isMenuOpen.value = false;
  }
};

// Lógica de tema
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

// Lógica de autenticación
const checkAuthAndFetchUser = async () => {
  const token = localStorage.getItem('auth_token');
  const userData = JSON.parse(localStorage.getItem('user'));

  if (!token) {
    router.push({ name: 'auth' });
    return false;
  }

  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  axios.defaults.headers.common['Accept'] = 'application/json';

  if (userData) {
    userName.value = userData.name;
  }

  try {
    const response = await axios.get(`${API_URL}/check-auth`);
    userName.value = response.data.user.name;
    userRole.value = response.data.user.role;
    return true;
  } catch (error) {
    console.error("Error de autenticación:", error);
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');

    await Swal.fire({
      icon: 'error',
      title: 'Sesión expirada',
      text: 'Su sesión ha expirado, por favor ingrese nuevamente',
      confirmButtonText: 'Aceptar'
    });

    router.push({ name: 'auth' });
    return false;
  }
};

const handleLogout = async () => {
  const { isConfirmed } = await Swal.fire({
    title: 'Cerrar sesión',
    text: '¿Está seguro que desea cerrar sesión?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, cerrar sesión',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (isConfirmed) {
    try {
      await axios.post(`${API_URL}/logout`);
    } catch (error) {
      console.error('Error al cerrar sesión en API:', error);
    } finally {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');

      await Swal.fire({
        title: 'Sesión cerrada',
        text: 'Ha cerrado sesión correctamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
      });

      router.push({ name: 'auth' });
    }
  }
};

const handleGlobalClick = (event) => {
  const isMenu = event.target.closest('.menu');
  const isButton = event.target.closest('.boton-menu');

  if (isMenuOpen.value && !isMenu && !isButton) {
    closeMenu();
  }
};

onMounted(() => {
  checkAuthAndFetchUser();
  checkTheme();
  document.addEventListener('click', handleGlobalClick);
});

onUnmounted(() => {
  document.removeEventListener('click', handleGlobalClick);
});
</script>

<style scoped>
/* Variables CSS (deben estar definidas globalmente) */

.encabezado {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: var(--altura-encabezado);
  background-color: var(--color-background);
  border-bottom: 1px solid var(--color-input-border);
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  z-index: 1000;
}

.contenedor {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  height: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  font-size: 1.5rem;
  font-weight: 700;
}

.logo a {
  color: var(--color-primary);
  text-decoration: none;
  display: flex;
  align-items: center;
}

.logo i {
  margin-right: 10px;
  color: var(--color-primary);
}

.boton-menu {
  background: transparent;
  border: none;
  cursor: pointer;
  display: flex;
  padding: 5px;
  outline: none;
  display: none;
}

.boton-menu svg {
  fill: var(--color-primary);
  width: 24px;
  height: 24px;
}

.menu {
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
}

.menu a {
  color: var(--color-text);
  text-decoration: none;
  padding: 0 15px;
  height: var(--altura-encabezado);
  display: flex;
  align-items: center;
  position: relative;
  font-weight: 500;
  transition: all 0.3s ease;
}

.menu a:hover {
  color: var(--color-primary);
}

.menu a::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 15px;
  width: calc(100% - 30px);
  height: 2px;
  background-color: var(--color-primary);
  transform: scaleX(0);
  transform-origin: right;
  transition: transform 0.3s ease;
}

.menu a:hover::after,
.menu a.router-link-exact-active::after {
  transform: scaleX(1);
  transform-origin: left;
}

.seccion-usuario {
  display: flex;
  align-items: center;
  margin-left: 20px;
  border-left: 1px solid var(--color-input-border);
  padding-left: 20px;
}

.seccion-usuario span {
  margin-right: 15px;
  font-size: 0.9rem;
  color: var(--color-text);
  white-space: nowrap;
}

.boton-modo {
  background: transparent;
  border: 1px solid var(--color-primary);
  color: var(--color-primary);
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  margin-right: 10px;
}

.boton-modo:hover {
  background: var(--color-primary);
  color: var(--color-button-text-hover);
}

.boton-modo i {
  font-size: 1.1rem;
}

.boton-salir {
  background: transparent;
  color: var(--color-primary);
  border: 1px solid var(--color-primary);
  padding: 5px 15px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  white-space: nowrap;
}

.boton-salir:hover {
  background: var(--color-primary);
  color: var(--color-button-text-hover);
}

.boton-salir i {
  margin-right: 5px;
}

.contenido-principal {
  margin-top: var(--altura-encabezado);
  padding: 20px;
  min-height: calc(100vh - var(--altura-encabezado));
  background-color: var(--color-background);
}

.contenedor-mapa {
  max-width: 1000px;
  margin: 0 auto;
}

.recuadro-mapa {
  background-color: var(--color-form-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.recuadro-mapa h2 {
  color: var(--color-primary);
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.mapa-container {
  margin-bottom: 20px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid var(--color-input-border);
}

.mapa-container iframe {
  width: 100%;
  height: 400px;
  border: none;
}

.info-mapa {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.info-item {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  padding: 15px;
  background-color: var(--color-background);
  border: 1px solid var(--color-input-border);
  border-radius: 6px;
}

.info-item i {
  color: var(--color-primary);
  font-size: 1.2rem;
  margin-top: 2px;
}

.info-item h4 {
  color: var(--color-primary);
  margin: 0 0 5px 0;
  font-size: 1rem;
}

.info-item p {
  color: var(--color-text);
  margin: 0;
  font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
  .contenedor {
    padding: 0 10px;
  }

  .menu {
    position: fixed;
    top: var(--altura-encabezado);
    left: 0;
    width: 100%;
    height: calc(100vh - var(--altura-encabezado));
    background-color: var(--color-background);
    flex-direction: column;
    align-items: flex-start;
    padding: 20px;
    transform: translateX(-100%);
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
  }

  .menu.activo {
    transform: translateX(0);
  }

  .menu a {
    width: 100%;
    padding: 15px 0;
    border-bottom: 1px solid var(--color-input-border);
  }

  .menu a::after {
    display: none;
  }

  .seccion-usuario {
    flex-direction: column;
    align-items: flex-start;
    margin-left: 0;
    border-left: none;
    padding-left: 0;
    margin-top: 20px;
  }

  .seccion-usuario span {
    margin-right: 0;
    margin-bottom: 10px;
  }

  .boton-menu {
    display: flex;
  }

  .info-mapa {
    grid-template-columns: 1fr;
  }

  .mapa-container iframe {
    height: 300px;
  }
}
</style>
