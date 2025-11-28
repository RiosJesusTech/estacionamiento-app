<template>
    <div class="cuentas-view">
        <header class="encabezado">
            <div class="contenedor">
                <div class="logo">
                    <router-link to="/inicio"><i class="fas fa-parking"></i>Estacionamiento</router-link>
                </div>

                <button class="boton-menu" @click="toggleMenu">
                    <svg v-if="!isMenuOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M4 6H20V8H4zM4 11H20V13H4zM4 16H20V18H4z"/>
                    </svg>
                    <svg v-else class="close-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M16.192 6.344L11.949 10.586 7.707 6.344 6.293 7.758 10.535 12 6.293 16.242 7.707 17.656 11.949 13.414 16.192 17.656 17.606 16.242 13.364 12 17.606 7.758z"/>
                    </svg>
                </button>

                <nav class="menu" :class="{ 'activo': isMenuOpen }">
                    <router-link to="/inicio" @click="closeMenu"><i class="fas fa-home"></i> Inicio</router-link>
                    <router-link to="/RegistrarAutoView" @click="closeMenu"><i class="fas fa-car-alt"></i> Registrar Auto</router-link>
                    <router-link to="/SalidaAutoView" @click="closeMenu"><i class="fas fa-sign-out-alt"></i> Salida Auto</router-link>
                    <router-link to="/cuentas" @click="closeMenu"><i class="fas fa-chart-line"></i> Cuentas</router-link>

                    <div class="seccion-usuario">
                        <span><i class="fas fa-user-circle"></i> <span class="user-name">{{ userName }}</span></span>

                        <button class="theme-toggle-button" @click="toggleTheme" title="Cambiar Tema">
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
            <h1 class="titulo-principal">{{ userRole === 'user' ? 'Mi Panel' : 'Cuentas del Día' }}</h1>

            <div v-if="userRole === 'user'" class="row row-cols-1 row-cols-md-2 g-4 mb-4">
                <div class="col">
                    <div class="card h-100 tarjeta">
                        <div class="card-header encabezado-tarjeta">
                            <h2 class="h5 mb-0"><i class="fas fa-user me-2"></i>Información de la Cuenta</h2>
                        </div>
                        <div class="card-body contenido-tarjeta">
                            <p><strong>Nombre:</strong> {{ userData.name }}</p>
                            <p><strong>Email:</strong> {{ userData.email }}</p>
                            <p><strong>Rol:</strong> {{ userData.role }}</p>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 tarjeta">
                        <div class="card-header encabezado-tarjeta">
                            <h2 class="h5 mb-0"><i class="fas fa-car me-2"></i>Información de Vehículos</h2>
                        </div>
                        <div class="card-body contenido-tarjeta">
                            <div class="table-responsive">
                                <table class="tabla-transacciones table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Placas</th>
                                            <th>Modelo</th>
                                            <th>Color</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="v in userVehicles" :key="v.id">
                                            <td>{{ v.placas }}</td>
                                            <td>{{ v.modelo }}</td>
                                            <td>{{ v.color }}</td>
                                        </tr>
                                        <tr v-if="userVehicles.length === 0">
                                            <td colspan="3" class="text-center">No hay vehículos registrados.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4 mb-4 grid-metricas" v-if="userRole !== 'user'">
                <div class="col">
                    <div class="card h-100 tarjeta-metrica">
                        <div class="card-header encabezado-metrica">
                            <span>Total Ingresos</span>
                        </div>
                        <div class="card-body contenido-metrica text-center">
                            <i class="fas fa-credit-card fa-3x mb-3 text-secondary"></i>
                            <h2 id="total-ingresos">{{ formatCurrency(metrics.totalIngresos) }}</h2>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 tarjeta-metrica">
                        <div class="card-header encabezado-metrica">
                            <span>Vehículos Atendidos</span>
                        </div>
                        <div class="card-body contenido-metrica text-center">
                            <i class="fas fa-car fa-3x mb-3 text-secondary"></i>
                            <h2 id="total-vehiculos">{{ metrics.totalVehiculos }}</h2>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 tarjeta-metrica">
                        <div class="card-header encabezado-metrica">
                            <span>Tiempo Promedio</span>
                        </div>
                        <div class="card-body contenido-metrica text-center">
                            <i class="fas fa-chart-bar fa-3x mb-3 text-secondary"></i>
                            <h2 id="tiempo-promedio">{{ metrics.tiempoPromedio }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="userRole === 'user'" class="row row-cols-1 row-cols-md-2 g-4 mb-4">
                <div class="col">
                    <div class="card h-100 tarjeta">
                        <div class="card-header encabezado-tarjeta">
                            <h2 class="h5 mb-0"><i class="fas fa-box me-2"></i>Paquetes Activos</h2>
                        </div>
                        <div class="card-body contenido-tarjeta">
                            <div class="table-responsive">
                                <table class="tabla-transacciones table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre del Paquete</th>
                                            <th>Estado</th>
                                            <th>Fecha de Expiración</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="p in packages" :key="p.id">
                                            <td>{{ p.nombre }}</td>
                                            <td>{{ p.estado }}</td>
                                            <td>{{ new Date(p.fecha_expiracion).toLocaleDateString() }}</td>
                                        </tr>
                                        <tr v-if="packages.length === 0">
                                            <td colspan="3" class="text-center">No hay paquetes activos.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 tarjeta">
                        <div class="card-header encabezado-tarjeta">
                            <h2 class="h5 mb-0"><i class="fas fa-calendar-check me-2"></i>Reservaciones</h2>
                        </div>
                        <div class="card-body contenido-tarjeta">
                            <div class="table-responsive">
                                <table class="tabla-transacciones table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Vehículo</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="r in reservations" :key="r.id">
                                            <td>{{ new Date(r.fecha).toLocaleDateString() }}</td>
                                            <td>{{ r.vehiculo_placas }}</td>
                                            <td>{{ r.estado }}</td>
                                        </tr>
                                        <tr v-if="reservations.length === 0">
                                            <td colspan="3" class="text-center">No hay reservaciones.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="userRole !== 'user'" class="card mb-4 tarjeta">
                    <div class="card-header encabezado-tarjeta">
                        <h2 class="h5 mb-0"><i class="fas fa-car me-2"></i>Total Acumulado por Vehículo</h2>
                    </div>
                    <div class="card-body contenido-tarjeta">
                        <div class="table-responsive">
                            <table class="tabla-transacciones table table-hover">
                                <thead>
                                    <tr>
                                        <th>Placas</th>
                                        <th>Cliente</th>
                                        <th>Número de Visitas</th>
                                        <th class="text-end">Total Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="v in vehiculosAgrupados" :key="v.placas">
                                        <td>{{ v.placas }}</td>
                                        <td>{{ v.nombre_cliente }}</td>
                                        <td>{{ v.visitas }}</td>
                                        <td class="text-end">{{ formatCurrency(v.totalMonto) }}</td>
                                    </tr>
                                    <tr v-if="vehiculosAgrupados.length === 0">
                                        <td colspan="4" class="text-center">No hay datos de vehículos.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>

            <div class="card mb-4 tarjeta">
                <div class="card-header encabezado-tarjeta">
                    <h2 class="h5 mb-0"><i class="fas fa-list me-2"></i>{{ userRole === 'user' ? 'Historial de Transacciones' : 'Transacciones del Día' }}</h2>
                    <div class="acciones-tarjeta">
                        <button class="btn btn-light btn-sm boton-accion" @click="handleReporte('imprimir')">
                            <i class="fas fa-print me-1"></i> Imprimir
                        </button>
                        <button class="btn btn-light btn-sm boton-accion" @click="handleReporte('exportar')">
                            <i class="fas fa-download me-1"></i> Exportar
                        </button>
                    </div>
                </div>
                <div class="card-body contenido-tarjeta">
                    <div class="table-responsive">
                        <table class="tabla-transacciones table table-hover">
                            <thead>
                                <tr v-if="userRole === 'user'">
                                    <th>Placas</th>
                                    <th>Modelo</th>
                                    <th>Color</th>
                                    <th>Número de Visitas</th>
                                    <th class="text-end">Total Pagado</th>
                                </tr>
                                <tr v-else>
                                    <th>Placas</th>
                                    <th>Cliente</th>
                                    <th>Hora Entrada</th>
                                    <th>Hora Salida</th>
                                    <th>Tiempo</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="vs in userVehiclesWithStats" :key="'user-' + vs.placas" v-if="userRole === 'user'">
                                    <td>{{ vs.placas }}</td>
                                    <td>{{ vs.modelo }}</td>
                                    <td>{{ vs.color }}</td>
                                    <td>{{ vs.visitas }}</td>
                                    <td class="text-end">{{ formatCurrency(vs.totalMonto) }}</td>
                                </tr>
                                <tr v-for="t in transacciones" :key="'gestor-' + t.id" v-else>
                                    <td>{{ t.placas }}</td>
                                    <td>{{ t.vehiculo_perfil ? t.vehiculo_perfil.nombre_cliente : 'N/A' }}</td>
                                    <td>{{ new Date(t.fecha_entrada).toLocaleTimeString() }}</td>
                                    <td>{{ new Date(t.fecha_salida).toLocaleTimeString() }}</td>
                                    <td>{{ calcularTiempoEstancia(t.fecha_entrada, t.fecha_salida) }}</td>
                                    <td class="text-end">{{ formatCurrency(t.monto) }}</td>
                                </tr>
                                <tr v-if="(userRole === 'user' ? userVehiclesWithStats.length : transacciones.length) === 0">
                                    <td :colspan="userRole === 'user' ? 5 : 6" class="text-center">No hay transacciones registradas hoy.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="userRole !== 'user'" class="card tarjeta">
                <div class="card-header encabezado-tarjeta">
                    <h2 class="h5 mb-0"><i class="fas fa-chart-line me-2"></i>Resumen del Día</h2>
                </div>
                <div class="card-body contenido-tarjeta">
                    <Bar :data="chartData" :options="chartOptions" />
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import jsPDF from 'jspdf';
import { autoTable } from 'jspdf-autotable';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const router = useRouter();
const API_URL = 'http://127.0.0.1:8000/api';

// --- ESTADO REACTIVO ---
const userName = ref('Cargando...');
const userRole = ref('');
const userId = ref(null);
const isMenuOpen = ref(false);
const isDarkTheme = ref(false);
const transacciones = ref([]);
const vehiculosAgrupados = ref([]);
const userVehicles = ref([]);
const userVehiclesWithStats = ref([]);
const userData = ref({});
const packages = ref([]);
const reservations = ref([]);

const metrics = reactive({
    totalIngresos: 0,
    totalVehiculos: 0,
    tiempoPromedio: '0h 00m'
});

const chartData = computed(() => {
    const ingresosPorHora = {};
    transacciones.value.forEach(t => {
        const hora = new Date(t.fecha_salida).getHours();
        ingresosPorHora[hora] = (ingresosPorHora[hora] || 0) + parseFloat(t.monto);
    });
    const labels = Object.keys(ingresosPorHora).sort((a, b) => a - b);
    const data = labels.map(h => ingresosPorHora[h]);
    return {
        labels,
        datasets: [{
            label: 'Ingresos por Hora',
            data,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    };
});

const chartOptions = {
    responsive: true,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Ingresos por Hora del Día'
        }
    }
};

// --- FUNCIONES DE UI Y TEMA ---

const toggleMenu = () => { isMenuOpen.value = !isMenuOpen.value; };
const closeMenu = () => { isMenuOpen.value = false; };
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
const handleLogout = async () => {
    const { isConfirmed } = await Swal.fire({ title: 'Cerrar sesión', text: '¿Está seguro que desea cerrar la sesión?', icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, cerrar' });
    if (isConfirmed) {
        try { await axios.post(`${API_URL}/logout`); } catch (error) { console.error('Logout error:', error); }
        localStorage.removeItem('auth_token'); localStorage.removeItem('user'); router.push({ name: 'auth' });
    }
};
const checkAuthAndFetchUser = async () => {
    const token = localStorage.getItem('auth_token');
    if (!token) { router.push({ name: 'auth' }); return; }
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`; axios.defaults.headers.common['Accept'] = 'application/json';
    try {
        const response = await axios.get(`${API_URL}/check-auth`);
        userData.value = response.data.user;
        userName.value = response.data.user.name;
        userRole.value = response.data.user.role;
        userId.value = response.data.user.id;

        if (userRole.value === 'user') {
            await loadUserVehicles();
        }
    } catch (error) {
        localStorage.removeItem('auth_token'); router.push({ name: 'auth' });
    }
};

const loadUserVehicles = async () => {
    try {
        const response = await axios.get(`${API_URL}/vehicles`);
        userVehicles.value = response.data;
    } catch (error) {
        console.error('Error loading user vehicles:', error);
    }
};

const loadPackages = async () => {
    try {
        const response = await axios.get(`${API_URL}/packages`);
        packages.value = response.data;
    } catch (error) {
        console.error('Error loading packages:', error);
    }
};

const loadReservations = async () => {
    try {
        const response = await axios.get(`${API_URL}/reservations`);
        reservations.value = response.data;
    } catch (error) {
        console.error('Error loading reservations:', error);
    }
};

// --- LÓGICA DE DATOS Y REPORTE ---

const formatCurrency = (value) => {
    return `$${parseFloat(value).toFixed(2)}`;
};

const calcularTiempoEstancia = (entrada, salida) => {
    const start = new Date(entrada);
    const end = new Date(salida);
    let diff = Math.abs(end - start) / 1000; // Diferencia en segundos

    // If difference is less than 60 seconds, consider it as 0 minutes (start at 0)
    if (diff < 60) {
        return '0h 00m';
    }

    const horas = Math.floor(diff / 3600);
    const minutos = Math.floor((diff % 3600) / 60);

    return `${horas}h ${minutos.toString().padStart(2, '0')}m`;
};

const calcularTiempoPromedio = (txs) => {
    if (txs.length === 0) return '0h 00m';

    // Filter out transactions where salida is before entrada or invalid
    const validTxs = txs.filter(t => {
        const entrada = new Date(t.fecha_entrada);
        const salida = new Date(t.fecha_salida);
        return salida > entrada;
    });

    if (validTxs.length === 0) return '0h 00m';

    const totalMinutos = validTxs.reduce((sum, t) => {
        const entrada = new Date(t.fecha_entrada);
        const salida = new Date(t.fecha_salida);
        const diff = (salida - entrada) / 1000 / 60;
        return sum + diff;
    }, 0);

    const promedioMinutos = totalMinutos / validTxs.length;
    const horas = Math.floor(promedioMinutos / 60);
    const minutos = Math.floor(promedioMinutos % 60);

    return `${horas}h ${minutos.toString().padStart(2, '0')}m`;
};

const cargarDatosDelDia = async () => {
    try {
        const response = await axios.get(`${API_URL}/transacciones`);
        const allTxs = response.data;
        let txs;

        if (userRole.value === 'user') {
            const userPlacas = userVehicles.value.map(v => v.placas);
            txs = allTxs.filter(t => userPlacas.includes(t.placas));
            await loadPackages();
            await loadReservations();

            // Compute stats for user's vehicles
            const stats = {};
            userVehicles.value.forEach(v => {
                stats[v.placas] = {
                    ...v,
                    visitas: 0,
                    totalMonto: 0
                };
            });
            txs.forEach(t => {
                if (stats[t.placas]) {
                    stats[t.placas].visitas += 1;
                    stats[t.placas].totalMonto += parseFloat(t.monto);
                }
            });
            userVehiclesWithStats.value = Object.values(stats);
        } else {
            // Filter transactions for today for gestor
            const today = new Date().toISOString().split('T')[0];
            txs = allTxs.filter(t => new Date(t.fecha_salida).toISOString().split('T')[0] === today);
        }

        transacciones.value = txs;

        if (userRole.value !== 'user') {
            // Agrupar por placas
            const agrupados = {};
            txs.forEach(t => {
                if (!agrupados[t.placas]) {
                    agrupados[t.placas] = {
                        placas: t.placas,
                        nombre_cliente: t.vehiculo_perfil ? t.vehiculo_perfil.nombre_cliente : 'N/A',
                        visitas: 0,
                        totalMonto: 0
                    };
                }
                agrupados[t.placas].visitas += 1;
                agrupados[t.placas].totalMonto += parseFloat(t.monto);
            });
            vehiculosAgrupados.value = Object.values(agrupados);
        }

        // Actualizar métricas
        const totalIngresos = txs.reduce((sum, t) => sum + parseFloat(t.monto), 0);

        metrics.totalIngresos = totalIngresos;
        metrics.totalVehiculos = userRole.value === 'user' ? userVehiclesWithStats.value.length : vehiculosAgrupados.value.length; // Unique vehicles
        metrics.tiempoPromedio = calcularTiempoPromedio(txs);

    } catch (error) {
        console.error('Error loading transactions:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las transacciones del día' });
    }
};

import * as XLSX from 'xlsx';

const handleReporte = async (accion) => {
    if (accion === 'imprimir') {
        try {
            // Generate PDF using jsPDF and autotable
            const doc = new jsPDF();

            // Title
            doc.setFontSize(18);
            doc.text('Reporte de Transacciones del Día', 14, 22);

            // Prepare table head and body
            const head = [['Placas', 'Hora Entrada', 'Hora Salida', 'Tiempo', 'Monto']];

            const body = transacciones.value.map(t => [
                t.placas,
                new Date(t.fecha_entrada).toLocaleTimeString(),
                new Date(t.fecha_salida).toLocaleTimeString(),
                calcularTiempoEstancia(t.fecha_entrada, t.fecha_salida),
                formatCurrency(t.monto)
            ]);

            // Add autotable
            autoTable(doc, {
                startY: 30,
                head,
                body,
                styles: { fontSize: 10 },
                headStyles: { fillColor: [54, 162, 235] }
            });

            // Open PDF in new window/tab
            window.open(doc.output('bloburl'), '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Reporte generado',
                text: 'El reporte se ha generado para impresión',
                confirmButtonText: 'Aceptar'
            });
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo generar el reporte para impresión',
                confirmButtonText: 'Aceptar'
            });
        }
    } else if (accion === 'exportar') {
        try {
            // Prepare data for Excel
            const data = transacciones.value.map(t => ({
                Placas: t.placas,
                'Hora Entrada': new Date(t.fecha_entrada).toLocaleTimeString(),
                'Hora Salida': new Date(t.fecha_salida).toLocaleTimeString(),
                Tiempo: calcularTiempoEstancia(t.fecha_entrada, t.fecha_salida),
                Monto: parseFloat(t.monto)
            }));

            // Create worksheet and workbook
            const worksheet = XLSX.utils.json_to_sheet(data);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Transacciones');

            // Generate Excel file and trigger download
            XLSX.writeFile(workbook, `Transacciones_${new Date().toISOString().split('T')[0]}.xlsx`);

            Swal.fire({
                icon: 'success',
                title: 'Reporte exportado',
                text: 'El reporte se ha exportado correctamente',
                confirmButtonText: 'Aceptar'
            });
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo exportar el reporte',
                confirmButtonText: 'Aceptar'
            });
        }
    }
};

// --- CICLO DE VIDA ---
onMounted(() => {
    checkAuthAndFetchUser();
    checkTheme();
    cargarDatosDelDia();
});
</script>

<style scoped>
/* --- ESTILOS COMPACTOS DE NAVEGACIÓN Y CUERPO --- */

/* --- ENCABEZADO Y NAVEGACIÓN (CORRECCIÓN FINAL) --- */
.encabezado {
    position: fixed; top: 0; left: 0; width: 100%; height: var(--altura-encabezado);
    background-color: var(--color-background); border-bottom: 1px solid var(--color-input-border);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1); z-index: 1000;
}
.contenedor { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 10px; height: 100%; display: flex; justify-content: space-between; align-items: center; }
.logo a { color: var(--color-primary); text-decoration: none; display: flex; align-items: center; font-size: 1.5rem; font-weight: 700; }
.boton-menu { background: transparent; border: none; cursor: pointer; display: none; padding: 5px; outline: none; }
.boton-menu svg { fill: var(--color-primary); width: 24px; height: 24px; }

/* Estilos del menú en ESCRITORIO */
.menu { display: flex; align-items: center; transition: all 0.3s ease; }
.menu a {
    color: var(--color-text); text-decoration: none; padding: 0 15px; height: var(--altura-encabezado);
    display: flex; align-items: center; position: relative; font-weight: 500; transition: all 0.3s ease;
    font-size: 1rem;
}
/* Línea activa Fija */
.menu a.router-link-exact-active::after {
    content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 3px;
    background-color: var(--color-primary); transform: scaleX(1); transition: transform 0.3s ease;
}
/* Línea Hover Sutil */
.menu a:not(.router-link-exact-active):hover::after {
    content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 3px;
    background-color: var(--color-primary); transform: scaleX(0.5); transition: transform 0.3s ease;
}

.seccion-usuario { display: flex; align-items: center; margin-left: 20px; border-left: 1px solid var(--color-input-border); padding-left: 20px; }
.seccion-usuario span { margin-right: 10px; font-size: 0.9rem; color: var(--color-text); }
.boton-salir { background: transparent; color: var(--color-primary); border: 1px solid var(--color-primary); padding: 5px 12px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; font-size: 0.9rem; }
.theme-toggle-button { background: transparent; color: var(--color-primary); border: 1px solid var(--color-primary); padding: 5px 8px; border-radius: 50%; cursor: pointer; transition: all 0.3s ease; margin-right: 10px; }
.boton-menu { display: none; }


/* --- CONTENIDO PRINCIPAL Y CUENTAS --- */
.contenido-principal {
    margin-top: var(--altura-encabezado); padding: 20px; min-height: calc(100vh - var(--altura-encabezado));
    background-color: var(--color-background);
}
.titulo-principal { text-align: center; margin-bottom: 25px; color: var(--color-primary); font-size: 1.8rem; font-weight: 600; }

/* MÉTRICAS GRID */
.grid-metricas {
    --bs-gutter-x: 1.5rem;
    --bs-gutter-y: 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.tarjeta-metrica {
    background-color: var(--color-form-background); border-radius: 8px; border: 1px solid var(--color-input-border);
    transition: box-shadow 0.3s ease;
}

/* Encabezados de Tarjetas (Métricos y de Lista) */
.card-header.encabezado-metrica {
    /* Usamos el color principal para el fondo del encabezado */
    background-color: var(--color-input-background) !important; /* Fondo claro para métricas */
    color: var(--color-text);
    padding: 12px 15px;
    font-size: 1rem;
    font-weight: 500;
    border-bottom: 1px solid var(--color-input-border);
}

.contenido-metrica {
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}
.contenido-metrica i {
    font-size: 2.5rem;
    margin-bottom: 10px;
    color: var(--color-secondary); /* Ícono en color secundario/gris */
}
.contenido-metrica h2 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-primary); /* Valor principal */
}

/* TABLAS Y ACCIONES */
.tarjeta {
    background-color: var(--color-form-background); border-radius: 8px; border: 1px solid var(--color-input-border);
    margin-bottom: 30px;
}
.encabezado-tarjeta {
    /* Encabezado principal de las tablas (Lista, Resumen) */
    background-color: var(--color-primary); /* Fondo Negro */
    color: var(--color-button-text-hover); /* Texto Blanco */
    padding: 15px 20px;
    border-radius: 8px 8px 0 0;
    display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;
}
.encabezado-tarjeta h2 { font-size: 1.2rem; font-weight: 500; display: flex; align-items: center; gap: 10px; }
.acciones-tarjeta { display: flex; gap: 10px; }
.boton-accion {
    background-color: var(--color-input-background);
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
    border-radius: 4px; padding: 6px 12px; font-size: 0.9rem; cursor: pointer;
    transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;
}
.boton-accion:hover { background-color: var(--color-primary); color: var(--color-button-text-hover); }

/* Tabla de Transacciones */
.tabla-transacciones { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.tabla-transacciones th, .tabla-transacciones td { padding: 12px 15px; border-bottom: 1px solid var(--color-input-border); text-align: left; color: var(--color-text); }
.tabla-transacciones th { background-color: var(--color-input-background); color: var(--color-text); font-weight: 600; }
.tabla-transacciones tr:hover { background-color: var(--color-input-border); }
.text-end { text-align: right; }


</style>
