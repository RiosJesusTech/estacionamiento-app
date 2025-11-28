<template>
  <div class="reservation-view">
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
          <router-link to="/reservations" @click="closeMenu"><i class="fas fa-calendar-check"></i> Reservaciones</router-link>

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
      <div class="contenedor-secciones">
        <section class="seccion-reservas">
          <div class="recuadro-reservas">
            <h2>Mis Reservaciones</h2>

            <div class="acciones-reservas">
              <button class="boton-nueva-reserva" @click="showNewReservationModal = true">
                <i class="fas fa-plus"></i> Nueva Reserva
              </button>
              <button class="boton-verificar-disponibilidad" @click="showAvailabilityModal = true">
                <i class="fas fa-search"></i> Verificar Disponibilidad
              </button>
            </div>

            <div class="lista-reservas">
              <div v-if="reservations.length === 0" class="sin-reservas">
                <p>No tienes reservaciones activas.</p>
              </div>
              <div v-else class="grid-reservas">
                <div v-for="reservation in reservations" :key="reservation.id" class="tarjeta-reserva">
                  <div class="encabezado-reserva">
                    <h3>{{ reservation.espacio.codigo }}</h3>
                    <span class="estado" :class="reservation.estado">{{ reservation.estado }}</span>
                  </div>
                  <div class="detalles-reserva">
                    <p><strong>Fecha y Hora:</strong> {{ formatDate(reservation.fecha_hora_reserva) }}</p>
                    <p><strong>Duración:</strong> {{ reservation.duracion }} minutos</p>
                  </div>
                  <div class="acciones-reserva">
                    <button v-if="reservation.estado === 'pendiente'" class="boton-cancelar" @click="cancelReservation(reservation.id)">
                      <i class="fas fa-times"></i> Cancelar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>

    <!-- Modal Nueva Reserva -->
    <div v-if="showNewReservationModal" class="modal-overlay" @click="showNewReservationModal = false">
      <div class="modal" @click.stop>
        <h3>Nueva Reserva</h3>
        <form @submit.prevent="createReservation">
          <div class="campo-formulario">
            <label for="espacio_id">Espacio:</label>
            <select id="espacio_id" v-model="newReservation.espacio_id" required>
              <option value="">Seleccionar espacio</option>
              <option v-for="espacio in availableSpaces" :key="espacio.id" :value="espacio.id">
                {{ espacio.codigo }}
              </option>
            </select>
          </div>
          <div class="campo-formulario">
            <label for="fecha_hora">Fecha y Hora:</label>
            <input type="datetime-local" id="fecha_hora" v-model="newReservation.fecha_hora_reserva" required>
          </div>
          <div class="campo-formulario">
            <label for="duracion">Duración (minutos):</label>
            <select id="duracion" v-model="newReservation.duracion" required>
              <option value="15">15 minutos</option>
              <option value="30">30 minutos</option>
              <option value="60">1 hora</option>
              <option value="120">2 horas</option>
              <option value="240">4 horas</option>
              <option value="480">8 horas</option>
            </select>
          </div>
          <div class="acciones-modal">
            <button type="button" class="boton-cancelar" @click="showNewReservationModal = false">Cancelar</button>
            <button type="submit" class="boton-confirmar">Reservar</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Verificar Disponibilidad -->
    <div v-if="showAvailabilityModal" class="modal-overlay" @click="showAvailabilityModal = false">
      <div class="modal" @click.stop>
        <h3>Verificar Disponibilidad</h3>
        <form @submit.prevent="checkAvailability">
          <div class="campo-formulario">
            <label for="check_fecha_hora">Fecha y Hora:</label>
            <input type="datetime-local" id="check_fecha_hora" v-model="availabilityCheck.fecha_hora" required>
          </div>
          <div class="campo-formulario">
            <label for="check_duracion">Duración (minutos):</label>
            <select id="check_duracion" v-model="availabilityCheck.duracion" required>
              <option value="15">15 minutos</option>
              <option value="30">30 minutos</option>
              <option value="60">1 hora</option>
              <option value="120">2 horas</option>
              <option value="240">4 horas</option>
              <option value="480">8 horas</option>
            </select>
          </div>
          <div class="acciones-modal">
            <button type="button" class="boton-cancelar" @click="showAvailabilityModal = false">Cancelar</button>
            <button type="submit" class="boton-confirmar">Verificar</button>
          </div>
        </form>
        <div v-if="availableSpacesForCheck.length > 0" class="resultados-disponibilidad">
          <h4>Espacios Disponibles:</h4>
          <ul>
            <li v-for="espacio in availableSpacesForCheck" :key="espacio.id">{{ espacio.codigo }}</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const router = useRouter();

// --- ESTADO REACTIVO ---
const userName = ref('Cargando...');
const isMenuOpen = ref(false);
const isDarkTheme = ref(false);
const API_URL = 'http://127.0.0.1:8000/api';

const reservations = ref([]);
const availableSpaces = ref([]);
const showNewReservationModal = ref(false);
const showAvailabilityModal = ref(false);
const newReservation = ref({
  espacio_id: '',
  fecha_hora_reserva: '',
  duracion: ''
});
const availabilityCheck = ref({
  fecha_hora: '',
  duracion: ''
});
const availableSpacesForCheck = ref([]);

// --- LÓGICA DE UI (MENÚ MÓVIL) ---
const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
  if (isMenuOpen.value) {
    isMenuOpen.value = false;
  }
};

// --- LÓGICA DE TEMA ---
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

// --- LÓGICA DE AUTENTICACIÓN ---
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
    return true;
  } catch (error) {
    console.error("Error de autenticación:", error);
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');

    await Swal.fire({
      icon: 'error',
      title: 'Sesión expirada',
      text: 'Su sesión ha expirado, por favor ingrese nuevamente'
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

// --- LÓGICA DE RESERVAS ---
const fetchReservations = async () => {
  try {
    const response = await axios.get(`${API_URL}/reservations`);
    reservations.value = response.data;
  } catch (error) {
    console.error('Error fetching reservations:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudieron cargar las reservaciones'
    });
  }
};

const fetchAvailableSpaces = async () => {
  try {
    const response = await axios.get(`${API_URL}/espacios`);
    availableSpaces.value = response.data.filter(espacio => espacio.estado === 'disponible');
  } catch (error) {
    console.error('Error fetching spaces:', error);
  }
};

const createReservation = async () => {
  try {
    const response = await axios.post(`${API_URL}/reservations`, newReservation.value);
    reservations.value.push(response.data);
    showNewReservationModal.value = false;
    newReservation.value = { espacio_id: '', fecha_hora_reserva: '', duracion: '' };

    await Swal.fire({
      icon: 'success',
      title: 'Reserva creada',
      text: 'Su reserva ha sido creada exitosamente. Recibirá una confirmación por email.'
    });
  } catch (error) {
    console.error('Error creating reservation:', error);
    const message = error.response?.data?.message || 'Error al crear la reserva';
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: message
    });
  }
};

const cancelReservation = async (id) => {
  const { isConfirmed } = await Swal.fire({
    title: 'Cancelar reserva',
    text: '¿Está seguro que desea cancelar esta reserva?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, cancelar',
    cancelButtonText: 'No'
  });

  if (isConfirmed) {
    try {
      await axios.post(`${API_URL}/reservations/${id}/cancel`);
      await fetchReservations();

      Swal.fire({
        icon: 'success',
        title: 'Reserva cancelada',
        text: 'La reserva ha sido cancelada exitosamente.'
      });
    } catch (error) {
      console.error('Error canceling reservation:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo cancelar la reserva'
      });
    }
  }
};

const checkAvailability = async () => {
  try {
    const response = await axios.post(`${API_URL}/reservations/check-availability`, availabilityCheck.value);
    availableSpacesForCheck.value = response.data;
  } catch (error) {
    console.error('Error checking availability:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo verificar la disponibilidad'
    });
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// --- CICLO DE VIDA ---
const handleGlobalClick = (event) => {
  const isMenu = event.target.closest('.menu');
  const isButton = event.target.closest('.boton-menu');

  if (isMenuOpen.value && !isMenu && !isButton) {
    closeMenu();
  }
};

onMounted(async () => {
  if (await checkAuthAndFetchUser()) {
    await fetchReservations();
    await fetchAvailableSpaces();
  }
  checkTheme();
  document.addEventListener('click', handleGlobalClick);
});

onUnmounted(() => {
  document.removeEventListener('click', handleGlobalClick);
});
</script>

<style scoped>
/* Variables CSS (deben estar definidas globalmente) */
/*
:root {
  --color-primary: #000000;
  --color-background: #FFFFFF;
  --color-text: #333333;
  --color-button-text-hover: #FFFFFF;
  --color-form-background: #f4f4f4;
  --color-input-border: #ddd;
  --altura-encabezado: 60px;
}
*/

/* --- Estilos del Encabezado --- */
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

/* --- CONTENIDO PRINCIPAL --- */
.contenido-principal {
  margin-top: var(--altura-encabezado);
  padding: 20px;
  min-height: calc(100vh - var(--altura-encabezado));
  background-color: var(--color-background);
}

.contenedor-secciones {
  display: flex;
  gap: 20px;
  min-height: calc(100vh - var(--altura-encabezado) - 40px);
}

.seccion-reservas {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.recuadro-reservas {
  background-color: var(--color-form-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  flex: 1;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.recuadro-reservas h2 {
  margin-bottom: 20px;
  color: var(--color-primary);
}

.acciones-reservas {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.boton-nueva-reserva,
.boton-verificar-disponibilidad {
  background-color: var(--color-primary);
  color: var(--color-button-text-hover);
  border: none;
  padding: 10px 15px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.boton-nueva-reserva:hover,
.boton-verificar-disponibilidad:hover {
  background-color: var(--color-secondary);
}

.sin-reservas {
  text-align: center;
  color: var(--color-text);
  font-style: italic;
}

.grid-reservas {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.tarjeta-reserva {
  background-color: var(--color-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.encabezado-reserva {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.encabezado-reserva h3 {
  margin: 0;
  color: var(--color-primary);
}

.estado {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.estado.pendiente {
  background-color: #fff3cd;
  color: #856404;
}

.estado.confirmada {
  background-color: #d1ecf1;
  color: #0c5460;
}

.estado.cancelada {
  background-color: #f8d7da;
  color: #721c24;
}

.detalles-reserva p {
  margin: 5px 0;
  color: var(--color-text);
}

.acciones-reserva {
  margin-top: 15px;
  display: flex;
  justify-content: flex-end;
}

.boton-cancelar {
  background-color: #dc3545;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: all 0.3s ease;
}

.boton-cancelar:hover {
  background-color: #c82333;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.modal {
  background-color: var(--color-background);
  border-radius: 8px;
  padding: 20px;
  max-width: 500px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
}

.modal h3 {
  margin-bottom: 20px;
  color: var(--color-primary);
}

.campo-formulario {
  margin-bottom: 15px;
}

.campo-formulario label {
  display: block;
  margin-bottom: 5px;
  color: var(--color-text);
  font-weight: 500;
}

.campo-formulario input,
.campo-formulario select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid var(--color-input-border);
  border-radius: 4px;
  background-color: var(--color-form-background);
  color: var(--color-text);
}

.acciones-modal {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.boton-confirmar {
  background-color: var(--color-primary);
  color: var(--color-button-text-hover);
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.boton-confirmar:hover {
  background-color: var(--color-secondary);
}

.resultados-disponibilidad {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid var(--color-input-border);
}

.resultados-disponibilidad h4 {
  margin-bottom: 10px;
  color: var(--color-primary);
}

.resultados-disponibilidad ul {
  list-style: none;
  padding: 0;
}

.resultados-disponibilidad li {
  padding: 5px 0;
  color: var(--color-text);
}

/* Responsive */
@media (max-width: 1024px) {
  .boton-menu {
    display: flex;
  }

  .menu {
    position: fixed;
    top: var(--altura-encabezado);
    left: 0;
    width: 100%;
    background-color: var(--color-background);
    flex-direction: column;
    align-items: flex-start;
    padding: 20px 0;
    clip-path: circle(0% at 100% 0);
    transition: clip-path 0.5s ease;
    box-shadow: 0 5px 10px rgba(0,0,0,0.1);
  }

  .menu.activo {
    clip-path: circle(150% at 100% 0);
  }

  .menu a {
    width: 100%;
    padding: 15px 25px;
    height: auto;
  }

  .menu a::after {
    display: none;
  }

  .menu a:hover {
    background-color: var(--color-form-background);
  }

  .seccion-usuario {
    border-left: none;
    padding-left: 0;
    margin-left: 0;
    margin-top: 15px;
    width: 100%;
    padding: 0 25px;
    flex-direction: column;
    align-items: flex-start;
  }

  .seccion-usuario span {
    margin-bottom: 10px;
    margin-right: 0;
  }

  .boton-modo,
  .boton-salir {
    width: 100%;
    justify-content: center;
    padding: 10px;
    margin-right: 0;
    margin-bottom: 10px;
  }

  .contenedor-secciones {
    flex-direction: column;
    min-height: auto;
  }

  .acciones-reservas {
    flex-direction: column;
  }

  .grid-reservas {
    grid-template-columns: 1fr;
  }

  .acciones-modal {
    flex-direction: column;
  }

  .boton-cancelar {
    order: 2;
  }

  .boton-confirmar {
    order: 1;
    margin-bottom: 10px;
  }
}
</style>
