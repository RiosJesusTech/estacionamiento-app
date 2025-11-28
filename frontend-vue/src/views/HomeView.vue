<template>
  <div class="home-view">
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
      <div class="contenedor-secciones">

        <!-- Manager Dashboard (gestor) -->
        <section v-if="userRole === 'gestor'" class="seccion-dashboard">
          <div class="recuadro-dashboard">
            <h2>Panel de Gestión</h2>

            <div class="grid-tarjetas">
              <div class="tarjeta">
                <div class="encabezado-tarjeta">
                  <div class="titulo-tarjeta">
                    <i class="fas fa-car-alt"></i> Registrar Automóvil
                  </div>
                </div>
                <div class="contenido-tarjeta">
                  <p>Registre la entrada de vehículos al estacionamiento</p>
                </div>
                <div class="pie-tarjeta">
                  <router-link to="/RegistrarAutoView" class="boton-acceder">Acceder</router-link>
                </div>
              </div>

              <div class="tarjeta">
                <div class="encabezado-tarjeta">
                  <div class="titulo-tarjeta">
                    <i class="fas fa-sign-out-alt"></i> Salida de Automóvil
                  </div>
                </div>
                <div class="contenido-tarjeta">
                  <p>Gestione la salida de vehículos y genere tickets de cobro</p>
                </div>
                <div class="pie-tarjeta">
                  <router-link to="/SalidaAutoView" class="boton-acceder">Acceder</router-link>
                </div>
              </div>

              <div class="tarjeta">
                <div class="encabezado-tarjeta">
                  <div class="titulo-tarjeta">
                    <i class="fas fa-chart-line"></i> Cuentas del Día
                  </div>
                </div>
                <div class="contenido-tarjeta">
                  <p>Visualice los ingresos y estadísticas del día actual</p>
                </div>
                <div class="pie-tarjeta">
                  <router-link to="/cuentas" class="boton-acceder">Acceder</router-link>
                </div>
              </div>
            </div>

            <!-- Reservations Section -->
            <div class="seccion-reservaciones">
              <h3>Reservaciones de Usuarios</h3>
              <div v-if="reservations.length === 0" class="sin-datos">
                <p>No hay reservaciones activas.</p>
              </div>
              <div v-else class="lista-reservaciones">
                <div v-for="reservation in reservations" :key="reservation.id" class="tarjeta-reservacion">
                  <div class="info-reservacion">
                    <h4>{{ reservation.user_name }} - {{ reservation.placa }}</h4>
                    <p><strong>Fecha:</strong> {{ formatDate(reservation.fecha_reservacion) }}</p>
                    <p><strong>Hora:</strong> {{ reservation.hora_inicio }} - {{ reservation.hora_fin }}</p>
                    <p><strong>Espacio:</strong> {{ reservation.espacio_codigo }}</p>
                    <p><strong>Estado:</strong> <span :class="'estado-' + reservation.estado">{{ reservation.estado }}</span></p>
                  </div>
                  <div class="acciones-reservacion">
                    <button v-if="reservation.estado === 'pendiente'" @click="confirmReservation(reservation.id)" class="boton-confirmar">Confirmar</button>
                    <button v-if="reservation.estado === 'pendiente'" @click="cancelReservation(reservation.id)" class="boton-cancelar">Cancelar</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Active Packages Section -->
            <div class="seccion-paquetes-activos">
              <h3>Paquetes Activos</h3>
              <div v-if="activePackages.length === 0" class="sin-datos">
                <p>No hay paquetes activos.</p>
              </div>
              <div v-else class="lista-usuarios-paquetes">
                <div v-for="user in activePackages" :key="user.id" class="tarjeta-usuario-paquetes">
                  <h4>{{ user.name }} ({{ user.email }})</h4>
                  <div class="lista-paquetes-usuario">
                    <div v-for="pkg in user.packages" :key="pkg.id" class="paquete-item">
                      <p><strong>Paquete:</strong> {{ pkg.package_name }}</p>
                      <p><strong>Descripción:</strong> {{ pkg.description }}</p>
                      <p><strong>Precio:</strong> ${{ pkg.price }}</p>
                      <p><strong>Duración:</strong> {{ pkg.duration_days }} días</p>
                      <p><strong>Fecha de Compra:</strong> {{ formatDate(pkg.start_date) }}</p>
                      <p><strong>Fecha de Expiración:</strong> {{ formatDate(pkg.end_date) }}</p>
                      <p><strong>Espacio Fijo:</strong> {{ pkg.assigned_spot ? 'Sí' : 'No' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- User Dashboard (user) -->
        <section v-if="userRole === 'user'" class="seccion-dashboard">
          <div class="recuadro-dashboard">
            <h2>Mi Panel</h2>

            <div class="grid-tarjetas">
              <div class="tarjeta">
                <div class="encabezado-tarjeta">
                  <div class="titulo-tarjeta">
                    <i class="fas fa-history"></i> Historial ({{ userTransactionsCount || 0 }} transacciones)
                  </div>
                </div>
                <div class="contenido-tarjeta">
                  <p>Visualice su historial de estacionamientos y pagos</p>
                </div>
                <div class="pie-tarjeta">
                  <button class="boton-acceder" @click="showHistoryModal = true">Ver Historial</button>
                </div>
              </div>

              <div class="tarjeta">
                <div class="encabezado-tarjeta">
                  <div class="titulo-tarjeta">
                    <i class="fas fa-user"></i> Datos de Cuenta
                  </div>
                </div>
                <div class="contenido-tarjeta">
                  <p>Gestione su información personal y preferencias</p>
                </div>
                <div class="pie-tarjeta">
                  <button class="boton-acceder" @click="showAccountModal = true">Ver Datos</button>
                </div>
              </div>

              <div class="tarjeta">
                <div class="encabezado-tarjeta">
                  <div class="titulo-tarjeta">
                    <i class="fas fa-car"></i> Información de Vehículos
                  </div>
                </div>
                <div class="contenido-tarjeta">
                  <p>Administre sus vehículos registrados</p>
                </div>
                <div class="pie-tarjeta">
                  <button class="boton-acceder" @click="showVehiclesModal = true">Ver Vehículos</button>
                </div>
              </div>

              <div class="tarjeta">
                <div class="encabezado-tarjeta">
                  <div class="titulo-tarjeta">
                    <i class="fas fa-box"></i> Paquetes Activos
                  </div>
                </div>
                <div class="contenido-tarjeta">
                  <p>Visualice sus paquetes de estacionamiento activos</p>
                </div>
                <div class="pie-tarjeta">
                  <button class="boton-acceder" @click="showPackagesModal = true">Ver Paquetes</button>
                </div>
              </div>

              <div class="tarjeta">
                <div class="encabezado-tarjeta">
                  <div class="titulo-tarjeta">
                    <i class="fas fa-calendar-check"></i> Reservaciones
                  </div>
                </div>
                <div class="contenido-tarjeta">
                  <p>Reserve espacios de estacionamiento y gestione sus reservas</p>
                </div>
                <div class="pie-tarjeta">
                  <router-link to="/reservations" class="boton-acceder">Reservar</router-link>
                </div>
              </div>

              <div class="tarjeta">
                <div class="encabezado-tarjeta">
                  <div class="titulo-tarjeta">
                    <i class="fas fa-robot"></i> Chatbot
                  </div>
                </div>
                <div class="contenido-tarjeta">
                  <p>Consulta disponibilidad de espacios, costos y paquetes</p>
                </div>
                <div class="pie-tarjeta">
                  <router-link to="/chatbot" class="boton-acceder">Chatear</router-link>
                </div>
              </div>

              <div class="tarjeta">
                <div class="encabezado-tarjeta">
                  <div class="titulo-tarjeta">
                    <i class="fas fa-map-marked-alt"></i> Minimap
                  </div>
                </div>
                <div class="contenido-tarjeta">
                  <p>Visualiza la ubicación del estacionamiento en el mapa</p>
                </div>
                <div class="pie-tarjeta">
                  <router-link to="/minimap" class="boton-acceder">Ver Mapa</router-link>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Camera Section (for both roles) -->
        <section v-if="false" class="seccion-camara">
          <div class="recuadro-camara">
            <div class="placeholder-camara">
              <div>
                <h2>Cámara en vivo</h2>
                <img :src="videoFeedUrl" alt="Cámara de Video" class="video">
              </div>
            </div>
            <div class="controles-camara">
              <button class="boton-camara">
                <i class="fas fa-camera"></i> Capturar
              </button>
              <button class="boton-camara">
                <i class="fas fa-cog"></i> Ajustes
              </button>
            </div>
          </div>
        </section>
      </div>

      <!-- Account Modal -->
      <div v-if="showAccountModal" class="modal-overlay" @click="showAccountModal = false">
        <div class="modal" @click.stop>
          <h3>Datos de Cuenta</h3>
          <div class="info-cuenta">
            <p><strong>Nombre:</strong> {{ userName }}</p>
            <p><strong>Email:</strong> {{ userEmail }}</p>
            <p><strong>Rol:</strong> {{ userRole }}</p>
          </div>
          <div class="acciones-modal">
            <button @click="showAccountModal = false">Cerrar</button>
          </div>
        </div>
      </div>

      <!-- Vehicles Modal -->
      <div v-if="showVehiclesModal" class="modal-overlay" @click="showVehiclesModal = false">
        <div class="modal" @click.stop>
          <h3>Mis Vehículos</h3>
          <div v-if="userVehicles.length === 0" class="sin-datos">
            <p>No tiene vehículos registrados.</p>
          </div>
          <div v-else class="lista-vehiculos">
            <div v-for="vehicle in userVehicles" :key="vehicle.id" class="tarjeta-vehiculo">
              <h4>{{ vehicle.placas }}</h4>
              <p><strong>Modelo:</strong> {{ vehicle.modelo }}</p>
              <p><strong>Color:</strong> {{ vehicle.color }}</p>
            </div>
          </div>
          <div class="acciones-modal">
            <button @click="showVehiclesModal = false">Cerrar</button>
          </div>
        </div>
      </div>

      <!-- Packages Modal -->
      <div v-if="showPackagesModal" class="modal-overlay" @click="showPackagesModal = false">
        <div class="modal" @click.stop>
          <h3>Mis Paquetes</h3>
          <div v-if="userPackages.length === 0" class="sin-datos">
            <p>No tiene paquetes activos.</p>
          </div>
          <div v-else class="lista-paquetes">
            <div v-for="pkg in userPackages" :key="pkg.id" class="tarjeta-paquete">
              <h4>{{ pkg.package.name }}</h4>
              <p><strong>Fecha de compra:</strong> {{ formatDate(pkg.purchase_date) }}</p>
              <p><strong>Expira:</strong> {{ formatDate(pkg.expiry_date) }}</p>
              <p><strong>Reservas restantes:</strong> {{ pkg.remaining_reservations }}</p>
            </div>
          </div>
          <div class="acciones-modal">
            <button @click="showPackagesModal = false">Cerrar</button>
          </div>
        </div>
      </div>

      <!-- History Modal -->
      <div v-if="showHistoryModal" class="modal-overlay" @click="showHistoryModal = false">
        <div class="modal" @click.stop>
          <h3>Historial de Transacciones</h3>
          <div v-if="userTransactions.length === 0" class="sin-datos">
            <p>No tiene transacciones registradas.</p>
          </div>
          <div v-else class="lista-transacciones">
            <div v-for="tx in userTransactions" :key="tx.id" class="tarjeta-transaccion">
              <h4>{{ tx.placas }} - {{ formatDate(tx.fecha_entrada) }}</h4>
              <p><strong>Hora Entrada:</strong> {{ tx.hora_entrada }}</p>
              <p><strong>Hora Salida:</strong> {{ tx.hora_salida || 'Pendiente' }}</p>
              <p><strong>Monto:</strong> {{ formatCurrency(tx.monto) }}</p>
            </div>
          </div>
          <div class="acciones-modal">
            <button @click="showHistoryModal = false">Cerrar</button>
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

// --- ESTADO REACTIVO ---
const userName = ref('Cargando...');
const userEmail = ref('');
const userRole = ref('');
const isMenuOpen = ref(false);
const isDarkTheme = ref(false); // Estado para el tema
const API_URL = 'http://127.0.0.1:8000/api';
const videoFeedUrl = ref('http://127.0.0.1:5000/video_feed');

// Manager data
const reservations = ref([]);
const activePackages = ref([]);

// User data
const userVehicles = ref([]);
const userPackages = ref([]);
const userTransactionsCount = ref(null);
const userTotalSpent = ref(0);
const userTransactions = ref([]);

// Modals
const showAccountModal = ref(false);
const showVehiclesModal = ref(false);
const showPackagesModal = ref(false);
const showHistoryModal = ref(false);

// --- LÓGICA DE UI (MENÚ MÓVIL) ---
const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
  if (isMenuOpen.value) {
    isMenuOpen.value = false;
  }
};

// --- LÓGICA DE TEMA (MODO CLARO/OSCURO) ---

/** Verifica y aplica el tema guardado en localStorage al body. */
const checkTheme = () => {
    // Obtener el tema, por defecto 'light'
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Actualizar el estado reactivo
    isDarkTheme.value = currentTheme === 'dark';
    
    // Aplicar la clase al body (donde deberían estar definidas las variables CSS)
    document.body.className = `theme-${currentTheme}`;
};

/** Alterna el tema y lo guarda. */
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

  // Configurar Axios headers
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  axios.defaults.headers.common['Accept'] = 'application/json';

  if (userData) {
    userName.value = userData.name;
  }

  try {
    const response = await axios.get(`${API_URL}/check-auth`);
    userName.value = response.data.user.name;
    userEmail.value = response.data.user.email;
    userRole.value = response.data.user.role;

    // Load role-specific data
    if (userRole.value === 'gestor') {
      await loadManagerData();
    } else if (userRole.value === 'user') {
      await loadUserData();
    }

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
      // Se limpia el local storage de todas formas
    } finally {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        
        await Swal.fire({
          title: 'Sesión cerrada',
          text: 'Ha cerrado sesión correctamente',
          icon: 'success',
          confirmButtonText: 'Aceptar'
        });
        
        router.push({ name: 'auth' }); // Redirigir al login
    }
  }
};

// --- LÓGICA DE DATOS POR ROL ---

const loadManagerData = async () => {
  try {
    // Load reservations
    const reservationsResponse = await axios.get(`${API_URL}/reservations`);
    reservations.value = reservationsResponse.data;

    // Load active user packages
    const packagesResponse = await axios.get(`${API_URL}/admin/active-user-packages`);
    activePackages.value = packagesResponse.data;
  } catch (error) {
    console.error('Error loading manager data:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudieron cargar los datos del gestor'
    });
  }
};

const loadUserData = async () => {
  try {
    // Load user vehicles
    const vehiclesResponse = await axios.get(`${API_URL}/vehicles`);
    userVehicles.value = vehiclesResponse.data;

    // Load user packages
    const packagesResponse = await axios.get(`${API_URL}/packages/my`);
    userPackages.value = packagesResponse.data;

    // Load user history summary
    await loadUserHistorySummary();
  } catch (error) {
    console.error('Error loading user data:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudieron cargar los datos del usuario'
    });
  }
};

const loadUserHistorySummary = async () => {
  try {
    const response = await axios.get(`${API_URL}/transacciones/my`);
    const userTxs = response.data;
    userTransactions.value = userTxs;
    userTransactionsCount.value = userTxs.length;
    userTotalSpent.value = userTxs.reduce((sum, t) => sum + parseFloat(t.monto), 0);
  } catch (error) {
    console.error('Error loading user history summary:', error);
  }
};

const confirmReservation = async (reservationId) => {
  try {
    await axios.post(`${API_URL}/reservations/${reservationId}/confirm`);
    await loadManagerData(); // Reload data
    Swal.fire({
      icon: 'success',
      title: 'Confirmado',
      text: 'La reservación ha sido confirmada'
    });
  } catch (error) {
    console.error('Error confirming reservation:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo confirmar la reservación'
    });
  }
};

const cancelReservation = async (reservationId) => {
  try {
    await axios.post(`${API_URL}/reservations/${reservationId}/cancel`);
    await loadManagerData(); // Reload data
    Swal.fire({
      icon: 'success',
      title: 'Cancelado',
      text: 'La reservación ha sido cancelada'
    });
  } catch (error) {
    console.error('Error canceling reservation:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo cancelar la reservación'
    });
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'MXN'
  }).format(amount);
};

// --- CICLO DE VIDA ---

const handleGlobalClick = (event) => {
    // Si el clic no es dentro del menú o el botón, cierra el menú
    const isMenu = event.target.closest('.menu');
    const isButton = event.target.closest('.boton-menu');
    
    if (isMenuOpen.value && !isMenu && !isButton) {
        closeMenu();
    }
};

onMounted(() => {
  checkAuthAndFetchUser();
  checkTheme(); // <--- Cargar el tema al montar el componente
  document.addEventListener('click', handleGlobalClick);
});

onUnmounted(() => {
  document.removeEventListener('click', handleGlobalClick);
});
</script>

<style scoped>
/* Variables usadas (deben estar definidas globalmente en .theme-light/.theme-dark) */
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

/* Efecto de subrayado minimalista */
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
  white-space: nowrap; /* Evitar que el nombre se corte */
}

/* Botón de Cambio de Tema (NUEVO) */
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
  /* Asegura que ocupe el espacio restante y se vea el fondo global */
  min-height: calc(100vh - var(--altura-encabezado)); 
  background-color: var(--color-background);
}

.contenedor-secciones {
  display: flex;
  gap: 20px;
  /* Altura mínima para que las secciones se extiendan, 
     ajustada para el padding de 20px en .contenido-principal */
  min-height: calc(100vh - var(--altura-encabezado) - 40px);
}

/* Sección de la cámara */
.seccion-camara {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.recuadro-camara {
  background-color: var(--color-form-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.placeholder-camara {
  background-color: var(--color-background);
  border: 2px dashed var(--color-input-border);
  border-radius: 4px;
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--color-text);
  font-size: 1.2rem;
}

.placeholder-camara .video {
    max-width: 100%;
    height: auto;
    display: block;
    border-radius: 4px;
}

.controles-camara {
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal {
  background-color: var(--color-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.modal h3 {
  color: var(--color-primary);
  margin-bottom: 15px;
  font-size: 1.4rem;
}

.info-cuenta {
  margin-bottom: 20px;
}

.info-cuenta p {
  margin: 10px 0;
  color: var(--color-text);
}

.acciones-modal {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.acciones-modal button {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.acciones-modal button:hover {
  background-color: var(--color-primary);
  color: var(--color-button-text-hover);
}

.tarjeta-transaccion {
  background-color: var(--color-background);
  border: 1px solid var(--color-input-border);
  border-radius: 6px;
  padding: 15px;
  margin-bottom: 10px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.tarjeta-transaccion h4 {
  color: var(--color-primary);
  margin-bottom: 10px;
  font-size: 1.1rem;
}

.tarjeta-transaccion p {
  margin: 5px 0;
  color: var(--color-text);
  font-size: 0.9rem;
}

/* --- Sección de tarjetas --- */
.seccion-tarjetas {
  width: 400px; /* Ancho fijo para las tarjetas en escritorio */
  display: flex;
  flex-direction: column;
}

.grid-tarjetas {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.enlace-tarjeta {
  text-decoration: none;
}

.tarjeta {
  background-color: var(--color-form-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  transition: all 0.3s ease;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.tarjeta:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  border-color: var(--color-primary);
}

.encabezado-tarjeta {
  margin-bottom: 15px;
}

.titulo-tarjeta {
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--color-primary);
  display: flex;
  align-items: center;
  gap: 10px;
}

.contenido-tarjeta p {
  color: var(--color-text);
  margin-bottom: 20px;
}

.pie-tarjeta {
  margin-top: auto;
  display: flex;
  justify-content: center;
}

.boton-acceder {
  background-color: var(--color-primary);
  color: var(--color-button-text-hover);
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
  text-decoration: none;
  display: inline-block;
  text-align: center;
}

.boton-acceder:hover {
  background-color: #333;
}

/* --- Estilos para Dashboard de Gestor --- */
.seccion-dashboard {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.recuadro-dashboard {
  background-color: var(--color-form-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.seccion-reservaciones,
.seccion-paquetes-activos {
  margin-top: 30px;
}

.seccion-reservaciones h3,
.seccion-paquetes-activos h3 {
  color: var(--color-primary);
  margin-bottom: 15px;
  font-size: 1.3rem;
}

.sin-datos {
  text-align: center;
  color: var(--color-text);
  font-style: italic;
  padding: 20px;
}

.lista-reservaciones,
.lista-paquetes,
.lista-vehiculos {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.tarjeta-reservacion,
.tarjeta-paquete,
.tarjeta-vehiculo {
  background-color: var(--color-background);
  border: 1px solid var(--color-input-border);
  border-radius: 6px;
  padding: 15px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.tarjeta-reservacion h4,
.tarjeta-paquete h4,
.tarjeta-vehiculo h4 {
  color: var(--color-primary);
  margin-bottom: 10px;
  font-size: 1.1rem;
}

.tarjeta-reservacion p,
.tarjeta-paquete p,
.tarjeta-vehiculo p {
  margin: 5px 0;
  color: var(--color-text);
  font-size: 0.9rem;
}

.info-reservacion {
  margin-bottom: 15px;
}

.acciones-reservacion {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.boton-confirmar,
.boton-cancelar {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.boton-confirmar {
  background-color: #28a745;
  color: white;
}

.boton-confirmar:hover {
  background-color: #218838;
}

.boton-cancelar {
  background-color: #dc3545;
  color: white;
}

.boton-cancelar:hover {
  background-color: #c82333;
}

.estado-pendiente {
  color: #ffc107;
  font-weight: bold;
}

.estado-confirmada {
  color: #28a745;
  font-weight: bold;
}

.estado-cancelada {
  color: #dc3545;
  font-weight: bold;
}

/* --- Estilos de Paquetes Activos de Usuarios --- */
.seccion-paquetes-activos {
  background-color: var(--color-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  margin-top: 30px;
}

.seccion-paquetes-activos h3 {
  margin-bottom: 20px;
  color: var(--color-primary);
}

.sin-paquetes-activos {
  text-align: center;
  color: var(--color-text);
  font-style: italic;
  padding: 40px 20px;
}

.lista-usuarios-paquetes {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.tarjeta-usuario-paquetes {
  background-color: var(--color-form-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.tarjeta-usuario-paquetes h4 {
  margin-bottom: 15px;
  color: var(--color-primary);
  border-bottom: 1px solid var(--color-input-border);
  padding-bottom: 10px;
}

.sin-paquetes-usuario {
  text-align: center;
  color: var(--color-text);
  font-style: italic;
  padding: 20px;
}

.lista-paquetes-usuario {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.paquete-item {
  background-color: var(--color-background);
  border: 1px solid var(--color-input-border);
  border-radius: 6px;
  padding: 15px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.paquete-item p {
  margin-bottom: 6px;
  color: var(--color-text);
  font-size: 0.9rem;
}

.paquete-item p:last-child {
  margin-bottom: 0;
}

/* --- Estilos para Modales --- */
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
  z-index: 1001;
}

.lista-transacciones {
  display: flex;
  flex-direction: column;
  gap: 15px;
  max-height: 400px;
  overflow-y: auto;
}

/* --- RESPONSIVE DESIGN --- */

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

  .contenedor-secciones {
    flex-direction: column;
  }

  .seccion-tarjetas {
    width: 100%;
  }

  .grid-tarjetas {
    grid-template-columns: 1fr;
  }

  .modal {
    width: 95%;
    padding: 15px;
  }
}
</style>
