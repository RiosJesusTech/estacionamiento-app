<template>
  <div class="admin-dashboard">
    <header class="encabezado">
      <div class="contenedor">
        <div class="logo">
          <router-link to="/admin"><i class="fas fa-chart-line"></i>Panel de Administración</router-link>
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
          <router-link to="/admin" @click="closeMenu"><i class="fas fa-tachometer-alt"></i> Dashboard</router-link>

          <div class="seccion-usuario">
            <span><i class="fas fa-user-shield"></i> <span class="user-name">{{ userName }}</span></span>

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
        <section class="seccion-dashboard">
          <div class="recuadro-dashboard">
            <h2>Dashboard de Administración</h2>

            <div class="controles-fecha">
              <div class="campo-fecha">
                <label for="report-date">Seleccionar Fecha:</label>
                <div class="input-with-icon">
                  <input type="date" id="report-date" v-model="selectedDate" @change="loadData" :min="currentYearStart" :max="currentYearEnd">
                  <i class="fas fa-calendar-alt" @click="focusDateInput"></i>
                </div>
              </div>
              <div class="botones-pdf">
                <button @click="downloadDailyReport" class="boton-pdf">Descargar PDF Diario</button>
                <button @click="downloadMonthlyReport" class="boton-pdf">Descargar PDF Mensual</button>
              </div>
            </div>

            <div class="grid-metricas">
              <div class="tarjeta-metrica">
                <h3>Ventas del Día</h3>
                <p class="valor">${{ dailySales.total_sales || 0 }}</p>
                <p class="sub-valor">{{ dailySales.total_transactions || 0 }} transacciones</p>
              </div>

              <div class="tarjeta-metrica">
                <h3>Horas Pico de Entradas</h3>
                <p class="valor">{{ dailyOccupancy.peak_entry_hours || 'N/A' }}</p>
                <p class="sub-valor">{{ dailyOccupancy.peak_entry_count || 0 }} entradas</p>
              </div>

              <div class="tarjeta-metrica">
                <h3>Reservaciones del Día</h3>
                <p class="valor">{{ dailyReservations || 0 }}</p>
                <p class="sub-valor">Activas</p>
              </div>

              <div class="tarjeta-metrica">
                <h3>Ventas del Mes</h3>
                <p class="valor">${{ monthlySales.total_sales || 0 }}</p>
                <p class="sub-valor">{{ monthlySales.total_transactions || 0 }} transacciones</p>
              </div>
            </div>

            <div class="seccion-graficos">
              <div class="grafico">
                <h3>Ocupación por Espacio (Día)</h3>
                <div class="placeholder-grafico">
                  <ul v-if="dailyOccupancy.occupancy_logs">
                    <li v-for="log in dailyOccupancy.occupancy_logs" :key="log.espacio_id">
                      Espacio {{ log.espacio_id }}: {{ log.estado }} ({{ log.count }} veces)
                    </li>
                  </ul>
                  <p v-else>No hay datos de ocupación para esta fecha.</p>
                </div>
              </div>

              <div class="grafico">
                <h3>Clientes Frecuentes (Mes)</h3>
                <div class="placeholder-grafico">
                  <ul v-if="monthlyCustomers.length > 0">
                    <li v-for="customer in monthlyCustomers" :key="customer.placas">
                      {{ customer.placas }} - {{ customer.nombre_cliente }}: {{ customer.visits }} visitas
                    </li>
                  </ul>
                  <p v-else>No hay datos de clientes frecuentes para este mes.</p>
                </div>
              </div>
            </div>

            <div class="seccion-reporte-mensual">
              <h3>Resumen Mensual</h3>
              <div class="grid-resumen">
                <div class="item-resumen">
                  <strong>Tasa de Ocupación:</strong> {{ monthlyReview.average_occupancy || 0 }}%
                </div>
                <div class="item-resumen">
                  <strong>Horas Pico:</strong> {{ monthlyReview.peak_hours || 'N/A' }}
                </div>
                <div class="item-resumen">
                  <strong>Reservaciones:</strong> {{ monthlyReview.reservation_count || 0 }}
                </div>
                <div class="item-resumen">
                  <strong>Cancelaciones:</strong> {{ monthlyReview.cancelled_reservations || 0 }}
                </div>
                <div class="item-resumen">
                  <strong>Tasa de Confirmación:</strong> {{ monthlyReview.reservation_rate || 0 }}%
                </div>
              </div>
            </div>

            <!-- Package Management Section -->
            <div class="seccion-paquetes">
              <h2>Gestión de Paquetes</h2>

              <div class="botones-paquetes">
                <button class="boton-nuevo-paquete" @click="showPackageModal = true">
                  <i class="fas fa-plus"></i> Nuevo Paquete
                </button>

                <button class="boton-enviar-mensaje" @click="sendPackageNotifications">
                  <i class="fas fa-envelope"></i> Enviar Mensajes a Clientes Frecuentes
                </button>
              </div>

              <div v-if="packages.length === 0" class="sin-paquetes">
                <p>No hay paquetes disponibles.</p>
              </div>

              <div v-else class="lista-paquetes">
                <div v-for="pkg in packages" :key="pkg.id" class="tarjeta-paquete">
                  <h3>{{ pkg.name }}</h3>
                  <p>{{ pkg.description }}</p>
                  <p><strong>Precio:</strong> ${{ pkg.price }}</p>
                  <p><strong>Duración:</strong> {{ pkg.duration_days }} días</p>
                  <p><strong>Reservas máximas por día:</strong> {{ pkg.max_reservations_per_day }}</p>
                  <p><strong>Espacio fijo:</strong> {{ pkg.fixed_spot ? 'Sí' : 'No' }}</p>
                  <div class="acciones-paquete">
                    <button @click="editPackage(pkg)">Editar</button>
                    <button @click="deletePackage(pkg.id)">Eliminar</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Package Modal -->
            <div v-if="showPackageModal" class="modal-overlay" @click="closePackageModal">
              <div class="modal" @click.stop>
                <h3>{{ editingPackage ? 'Editar Paquete' : 'Nuevo Paquete' }}</h3>
                <form @submit.prevent="savePackage">
                  <div class="campo-formulario">
                    <label for="package-name">Nombre:</label>
                    <input id="package-name" v-model="packageForm.name" required />
                  </div>
                  <div class="campo-formulario">
                    <label for="package-description">Descripción:</label>
                    <textarea id="package-description" v-model="packageForm.description" required></textarea>
                  </div>
                  <div class="campo-formulario">
                    <label for="package-price">Precio:</label>
                    <input id="package-price" type="number" v-model.number="packageForm.price" min="0" required />
                  </div>
                  <div class="campo-formulario">
                    <label for="package-duration">Duración (días):</label>
                    <input id="package-duration" type="number" v-model.number="packageForm.duration_days" min="1" required />
                  </div>
                  <div class="campo-formulario">
                    <label for="package-max-reservations">Reservas máximas por día:</label>
                    <input id="package-max-reservations" type="number" v-model.number="packageForm.max_reservations_per_day" min="1" required />
                  </div>
                  <div class="campo-formulario">
                    <label>
                      <input type="checkbox" v-model="packageForm.fixed_spot" />
                      Espacio fijo
                    </label>
                  </div>
                  <div class="acciones-modal">
                    <button type="button" @click="closePackageModal">Cancelar</button>
                    <button type="submit">{{ editingPackage ? 'Guardar' : 'Crear' }}</button>
                  </div>
                </form>
              </div>
            </div>



            <!-- Active User Packages Section -->
            <div class="seccion-paquetes-activos">
              <h2>Paquetes Activos de Usuarios</h2>

              <div v-if="filteredActiveUserPackages.length === 0" class="sin-paquetes-activos">
                <p>No hay paquetes activos de usuarios.</p>
              </div>
              <div v-else class="lista-usuarios-paquetes">
                <div v-for="user in filteredActiveUserPackages" :key="user.id" class="tarjeta-usuario-paquetes">
                  <h3>{{ user.name }} ({{ user.email }})</h3>
                  <div class="lista-paquetes-usuario">
                    <div v-for="pkg in user.packages" :key="pkg.id" class="paquete-item">
                      <p><strong>Paquete:</strong> {{ pkg.package_name }}</p>
                      <p><strong>Descripción:</strong> {{ pkg.description }}</p>
                      <p><strong>Precio:</strong> ${{ pkg.price }}</p>
                      <p><strong>Duración:</strong> {{ pkg.duration_days }} días</p>
                      <p><strong>Fecha de Compra:</strong> {{ new Date(pkg.start_date).toLocaleDateString() }}</p>
                      <p><strong>Fecha de Expiración:</strong> {{ new Date(pkg.end_date).toLocaleDateString() }}</p>
                      <p><strong>Espacio Fijo:</strong> {{ pkg.assigned_spot ? 'Sí' : 'No' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>



          </div>
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const router = useRouter();

// --- ESTADO REACTIVO ---
const userName = ref('Cargando...');
const isMenuOpen = ref(false);
const isDarkTheme = ref(false);
const API_URL = 'http://127.0.0.1:8000/api';

const selectedDate = ref(new Date().toISOString().split('T')[0]);
const dailySales = ref({});
const dailyOccupancy = ref({});
const dailyReservations = ref(0);
const monthlySales = ref({});
const monthlyCustomers = ref({});
const monthlyReview = ref({});

// Computed properties for date restrictions
const currentYearStart = computed(() => {
  const currentYear = new Date().getFullYear();
  return `${currentYear}-01-01`;
});

const currentYearEnd = computed(() => {
  const currentYear = new Date().getFullYear();
  return `${currentYear}-12-31`;
});

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
    // Check if user is admin or gestor
    if (!['admin', 'gestor'].includes(response.data.user.role)) {
      await Swal.fire({
        icon: 'error',
        title: 'Acceso denegado',
        text: 'No tienes permisos para acceder a esta sección'
      });
      router.push({ name: 'home' });
      return false;
    }
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

// --- LÓGICA DE DATOS ---
const loadData = async () => {
  try {
    const [salesRes, occupancyRes, reservationsRes] = await Promise.all([
      axios.get(`${API_URL}/admin/sales-report?date=${selectedDate.value}`),
      axios.get(`${API_URL}/admin/occupancy-stats?date=${selectedDate.value}`),
      axios.get(`${API_URL}/admin/daily-reservations?date=${selectedDate.value}`)
    ]);

    dailySales.value = salesRes.data;
    dailyOccupancy.value = occupancyRes.data;
    dailyReservations.value = reservationsRes.data;
    await loadMonthlyData();
  } catch (error) {
    console.error('Error loading daily data:', error);
  }
};

const loadMonthlyData = async () => {
  try {
    const date = new Date(selectedDate.value);
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const startDate = new Date(year, month - 1, 1).toISOString().split('T')[0];
    let endDate = new Date(year, month, 0).toISOString().split('T')[0]; // Last day of month

    // If it's the current month, limit to current day
    const now = new Date();
    if (year === now.getFullYear() && month === now.getMonth() + 1) {
      endDate = now.toISOString().split('T')[0];
    }

    const [salesRes, customersRes, reviewRes] = await Promise.all([
      axios.get(`${API_URL}/admin/sales-report?start=${startDate}&end=${endDate}`),
      axios.get(`${API_URL}/admin/customer-analytics?start=${startDate}&end=${endDate}`),
      axios.get(`${API_URL}/admin/monthly-review?month=${month}&year=${year}`)
    ]);

    monthlySales.value = salesRes.data;
    monthlyCustomers.value = customersRes.data;
    monthlyReview.value = reviewRes.data;
  } catch (error) {
    console.error('Error loading monthly data:', error);
    // Show user-friendly error messages
    if (error.response && error.response.status === 401) {
      Swal.fire('Error', 'No se pudieron cargar los datos del usuario. Verifique su sesión.', 'error');
    } else if (error.response && error.response.status === 403) {
      Swal.fire('Error', 'No se pudieron cargar los datos del gestor. Verifique sus permisos.', 'error');
    } else {
      Swal.fire('Error', 'Error al cargar datos mensuales. Intente nuevamente.', 'error');
    }
  }
};

// --- LÓGICA DE PAQUETES ---
const packages = ref([]);
const showPackageModal = ref(false);
const editingPackage = ref(false);
const packageForm = ref({
  id: null,
  name: '',
  description: '',
  price: 0,
  duration_days: 1,
  max_reservations_per_day: 1,
  fixed_spot: false,
});

// New reactive state for user packages
const activeUserPackages = ref([]);

// Computed for filtered active user packages (only those with packages)
const filteredActiveUserPackages = computed(() => {
  return activeUserPackages.value.filter(user => user.packages && user.packages.length > 0);
});

const fetchPackages = async () => {
  try {
    const response = await axios.get(`${API_URL}/packages`);
    packages.value = response.data;
  } catch (error) {
    console.error('Error fetching packages:', error);
  }
};

const savePackage = async () => {
  try {
    if (editingPackage.value) {
      await axios.put(`${API_URL}/packages/${packageForm.value.id}`, packageForm.value);
      Swal.fire('Éxito', 'Paquete actualizado correctamente', 'success');
    } else {
      await axios.post(`${API_URL}/packages`, packageForm.value);
      Swal.fire('Éxito', 'Paquete creado correctamente', 'success');
    }
    showPackageModal.value = false;
    await fetchPackages();
  } catch (error) {
    console.error('Error saving package:', error);
    Swal.fire('Error', 'No se pudo guardar el paquete', 'error');
  }
};

const editPackage = (pkg) => {
  editingPackage.value = true;
  packageForm.value = { ...pkg };
  showPackageModal.value = true;
};

const deletePackage = async (id) => {
  const { isConfirmed } = await Swal.fire({
    title: 'Eliminar paquete',
    text: '¿Está seguro que desea eliminar este paquete?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (isConfirmed) {
    try {
      await axios.delete(`${API_URL}/packages/${id}`);
      Swal.fire('Eliminado', 'El paquete ha sido eliminado', 'success');
      await fetchPackages();
    } catch (error) {
      console.error('Error deleting package:', error);
      Swal.fire('Error', 'No se pudo eliminar el paquete', 'error');
    }
  }
};

const closePackageModal = () => {
  showPackageModal.value = false;
  editingPackage.value = false;
  packageForm.value = {
    id: null,
    name: '',
    description: '',
    price: 0,
    duration_days: 1,
    max_reservations_per_day: 1,
    fixed_spot: false,
  };
};

const sendPackageNotifications = async () => {
  const { isConfirmed } = await Swal.fire({
    title: 'Enviar notificaciones de paquetes',
    text: '¿Está seguro que desea enviar notificaciones de paquetes a clientes frecuentes?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, enviar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (isConfirmed) {
    try {
      await axios.post(`${API_URL}/admin/send-package-notifications`);
      Swal.fire('Éxito', 'Notificaciones enviadas correctamente', 'success');
    } catch (error) {
      console.error('Error sending package notifications:', error);
      Swal.fire('Error', 'No se pudieron enviar las notificaciones', 'error');
    }
  }
};

// --- LÓGICA DE PAQUETES DE USUARIOS ---
const fetchActiveUserPackages = async () => {
  try {
    const response = await axios.get(`${API_URL}/admin/active-user-packages`);
    activeUserPackages.value = response.data;
  } catch (error) {
    console.error('Error fetching active user packages:', error);
    Swal.fire('Error', 'No se pudieron cargar los paquetes activos de usuarios', 'error');
  }
};

// --- LÓGICA DE DESCARGA DE REPORTES ---
const focusDateInput = () => {
  const dateInput = document.getElementById('report-date');
  if (dateInput) {
    dateInput.focus();
  }
};

const downloadDailyReport = async () => {
  try {
    const response = await axios.get(`${API_URL}/admin/daily-report?date=${selectedDate.value}`, {
      responseType: 'blob'
    });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `reporte_diario_${selectedDate.value}.pdf`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error('Error downloading daily report:', error);
    Swal.fire('Error', 'No se pudo descargar el reporte diario', 'error');
  }
};

const downloadMonthlyReport = async () => {
  try {
    const date = new Date(selectedDate.value);
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const response = await axios.get(`${API_URL}/admin/monthly-report?month=${month}&year=${year}`, {
      responseType: 'blob'
    });
    const monthStr = String(month).padStart(2, '0');
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `reporte_mensual_${year}_${monthStr}.pdf`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error('Error downloading monthly report:', error);
    Swal.fire('Error', 'No se pudo descargar el reporte mensual', 'error');
  }
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
    await loadData();
    await fetchPackages();
    await fetchActiveUserPackages();
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
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.recuadro-dashboard h2 {
  margin-bottom: 20px;
  color: var(--color-primary);
}

.controles-fecha {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.campo-fecha {
  display: flex;
  flex-direction: column;
}

.campo-fecha label {
  margin-bottom: 5px;
  font-weight: 500;
  color: var(--color-text);
}

.campo-fecha input {
  padding: 8px 12px;
  border: 1px solid var(--color-input-border);
  border-radius: 4px;
  background-color: var(--color-background);
  color: var(--color-text);
}

.input-with-icon {
  position: relative;
  display: inline-block;
  width: 100%;
}

.input-with-icon input {
  padding-right: 30px;
}

.input-with-icon i {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: var(--color-text);
}

.botones-pdf {
  display: flex;
  gap: 10px;
}

.boton-pdf {
  background: var(--color-primary);
  color: var(--color-button-text-hover);
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.boton-pdf:hover {
  background: var(--color-primary);
  opacity: 0.9;
}

.grid-metricas {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.tarjeta-metrica {
  background-color: var(--color-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.tarjeta-metrica h3 {
  margin-bottom: 10px;
  color: var(--color-primary);
  font-size: 1.1rem;
}

.valor {
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-primary);
  margin-bottom: 5px;
}

.sub-valor {
  color: var(--color-text);
  font-size: 0.9rem;
}

.seccion-graficos {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.grafico {
  background-color: var(--color-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.grafico h3 {
  margin-bottom: 15px;
  color: var(--color-primary);
}

.placeholder-grafico {
  min-height: 200px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  color: var(--color-text);
}

.placeholder-grafico ul {
  list-style: none;
  padding: 0;
}

.placeholder-grafico li {
  padding: 5px 0;
  border-bottom: 1px solid var(--color-input-border);
}

.seccion-reporte-mensual {
  background-color: var(--color-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.seccion-reporte-mensual h3 {
  margin-bottom: 15px;
  color: var(--color-primary);
}

.grid-resumen {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.item-resumen {
  padding: 10px;
  background-color: var(--color-form-background);
  border-radius: 4px;
  color: var(--color-text);
}

/* --- Estilos de Gestión de Paquetes --- */
.seccion-paquetes {
  background-color: var(--color-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  margin-top: 30px;
}

.seccion-paquetes h2 {
  margin-bottom: 20px;
  color: var(--color-primary);
}

.boton-nuevo-paquete {
  background: var(--color-primary);
  color: var(--color-button-text-hover);
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  font-weight: 500;
  transition: all 0.3s ease;
}

.boton-nuevo-paquete:hover {
  background: var(--color-primary);
  opacity: 0.9;
}

.boton-nuevo-paquete i {
  margin-right: 8px;
}

.botones-paquetes {
  display: flex;
  gap: 10px;
}

.boton-enviar-mensaje {
  background: var(--color-primary);
  color: var(--color-button-text-hover);
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  font-weight: 500;
  transition: all 0.3s ease;
}

.boton-enviar-mensaje:hover {
  background: var(--color-primary);
  opacity: 0.9;
}

.boton-enviar-mensaje i {
  margin-right: 8px;
}

.sin-paquetes {
  text-align: center;
  color: var(--color-text);
  font-style: italic;
  padding: 40px 20px;
}

.lista-paquetes {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.tarjeta-paquete {
  background-color: var(--color-form-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.tarjeta-paquete h3 {
  margin-bottom: 10px;
  color: var(--color-primary);
}

.tarjeta-paquete p {
  margin-bottom: 8px;
  color: var(--color-text);
}

.acciones-paquete {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.acciones-paquete button {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.acciones-paquete button:first-child {
  background: var(--color-primary);
  color: var(--color-button-text-hover);
}

.acciones-paquete button:first-child:hover {
  background: var(--color-primary);
  opacity: 0.9;
}

.acciones-paquete button:last-child {
  background: #dc3545;
  color: white;
}

.acciones-paquete button:last-child:hover {
  background: #c82333;
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
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.modal h3 {
  margin-bottom: 20px;
  color: var(--color-primary);
  text-align: center;
}

.campo-formulario {
  margin-bottom: 15px;
}

.campo-formulario label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: var(--color-text);
}

.campo-formulario input,
.campo-formulario textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid var(--color-input-border);
  border-radius: 4px;
  background-color: var(--color-background);
  color: var(--color-text);
  font-size: 1rem;
}

.campo-formulario textarea {
  resize: vertical;
  min-height: 80px;
}

.acciones-modal {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
}

.acciones-modal button {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.acciones-modal button:first-child {
  background: var(--color-input-border);
  color: var(--color-text);
}

.acciones-modal button:first-child:hover {
  background: #ccc;
}

.acciones-modal button:last-child {
  background: var(--color-primary);
  color: var(--color-button-text-hover);
}

.acciones-modal button:last-child:hover {
  background: var(--color-primary);
  opacity: 0.9;
}

/* --- Estilos de Reservaciones de Usuarios --- */
.seccion-reservaciones {
  background-color: var(--color-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  margin-top: 30px;
}

.seccion-reservaciones h2 {
  margin-bottom: 20px;
  color: var(--color-primary);
}

.estado-activa {
  color: #28a745;
  font-weight: bold;
}

.estado-cancelada {
  color: #dc3545;
  font-weight: bold;
}

.estado-pendiente {
  color: #ffc107;
  font-weight: bold;
}

.sin-reservaciones {
  text-align: center;
  color: var(--color-text);
  font-style: italic;
  padding: 40px 20px;
}

.lista-reservaciones {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.tarjeta-reservacion {
  background-color: var(--color-form-background);
  border: 1px solid var(--color-input-border);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.tarjeta-reservacion h3 {
  margin-bottom: 10px;
  color: var(--color-primary);
}

.tarjeta-reservacion p {
  margin-bottom: 8px;
  color: var(--color-text);
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

.seccion-paquetes-activos h2 {
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

.tarjeta-usuario-paquetes h3 {
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

  .controles-fecha {
    flex-direction: column;
  }

  .grid-metricas {
    grid-template-columns: 1fr;
  }

  .seccion-graficos {
    grid-template-columns: 1fr;
  }

  .grid-resumen {
    grid-template-columns: 1fr;
  }

  .lista-paquetes {
    grid-template-columns: 1fr;
  }

  .lista-reservaciones {
    grid-template-columns: 1fr;
  }

  .lista-paquetes-activos {
    grid-template-columns: 1fr;
  }

  .acciones-modal {
    flex-direction: column;
  }

  .acciones-modal button {
    width: 100%;
  }
}
</style>
