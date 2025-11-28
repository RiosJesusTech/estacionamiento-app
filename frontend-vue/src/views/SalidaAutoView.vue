i<template>
    <div class="salida-auto-view">
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
            <h1 class="titulo-principal">Salida de Vehículo</h1>
            
            <div class="grid-contenido dos-columnas">
                <div class="columna-principal">
                    <div class="tarjeta tabla-tarjeta">
                        <div class="encabezado-tarjeta">
                            <h2><i class="fas fa-car"></i> Vehículos en Estacionamiento</h2>
                        </div>
                        <div class="contenido-tarjeta">
                            <div class="table-responsive">
                                <table class="tabla-vehiculos">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Placas</th>
                                            <th>Marca/Modelo</th>
                                            <th>Sección</th>
                                            <th>Tiempo</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="vehiculo in vehiculos" :key="vehiculo.id" @click="selectVehicle(vehiculo)" :class="{ 'table-active': selectedVehicle && selectedVehicle.id === vehiculo.id }">
                                            <td><i :class="['fas', vehiculo.tipo === 'motocicleta' ? 'fa-motorcycle' : 'fa-car']"></i> {{ vehiculo.tipo }}</td>
                                            <td>{{ vehiculo.placas }}</td>
                                            <td>{{ vehiculo.marca }} {{ vehiculo.modelo }}</td>
                                            <td>{{ vehiculo.seccion }}</td>
                                            <td>{{ calcularTiempo(vehiculo.fecha_entrada) }}</td>
                                            <td>
                                                <button class="boton-seleccionar" :class="{'seleccionado': selectedVehicle && selectedVehicle.id === vehiculo.id}" @click.stop="selectVehicle(vehiculo)">
                                                    Seleccionar
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="vehiculos.length === 0">
                                            <td colspan="6" class="text-center">No hay vehículos estacionados.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="panel-pago" class="tarjeta mt-3" v-show="selectedVehicle">
                        <div class="encabezado-tarjeta">
                            <h2><i class="fas fa-receipt"></i> Información de Pago</h2>
                        </div>
                        <div class="contenido-tarjeta">
                            <div class="info-pago">
                                <p class="etiqueta">Vehículo:</p><p class="valor">{{ selectedVehicle?.marca }} {{ selectedVehicle?.modelo }} ({{ selectedVehicle?.placas }})</p>
                                <p class="etiqueta">Tipo:</p><p class="valor">{{ selectedVehicle?.tipo }}</p>
                                <p class="etiqueta">Sección:</p><p class="valor">{{ selectedVehicle?.seccion }}</p>
                                <p class="etiqueta">Teléfono:</p><p class="valor">{{ selectedVehicle?.telefono || 'No registrado' }}</p>
                                <p class="etiqueta">Hora Entrada:</p><p class="valor">{{ new Date(selectedVehicle?.fecha_entrada).toLocaleTimeString() }}</p>
                                <p class="etiqueta">Hora Salida:</p><p class="valor">{{ new Date().toLocaleTimeString() }}</p>
                                <p class="etiqueta">Tiempo total:</p><p class="valor">{{ timeElapsed }}</p>
                                <p class="etiqueta">Tarifa:</p><p class="valor">$20.00 / hora</p>
                                
                                <div v-if="selectedVehicle?.tipo === 'motocicleta'">
                                    <p class="etiqueta">Pertenencias:</p><p class="valor">{{ selectedVehicle?.pertenencias || 'No especificado' }}</p>
                                </div>
                                
                                <div class="total-pago mt-3">
                                    <h5>Total a pagar: <span class="fs-4">{{ formatCurrency(totalPagar) }}</span></h5>
                                </div>
                                
                                <button class="boton-imprimir mt-3" @click="procesarSalida">
                                    <i class="fas fa-print me-2"></i> Registrar Salida e Imprimir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <section class="seccion-camara-mapa">
                    
                    <div class="tarjeta mapa-tarjeta">
                        <div class="encabezado-tarjeta">
                            <h2><i class="fas fa-map-marked-alt"></i> Mapa del Estacionamiento</h2>
                        </div>
                        <div class="contenido-tarjeta mapa-contenido">
                            <div class="mapa-estacionamiento">
                                <div class="grid-espacios">
                                    <div 
                                        v-for="espacio in espacios" 
                                        :key="espacio.codigo"
                                        :class="['espacio-estacionamiento', getEstado(espacio), { 'seleccionado': selectedVehicle && selectedVehicle.seccion === espacio.codigo }]"
                                        @click="selectVehicle(vehiculos.find(v => v.seccion === espacio.codigo))"
                                    >
                                        <i :class="['fas', getIcono(espacio)]"></i>
                                        <span>{{ espacio.codigo }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="leyenda-mapa">
                                <h5>Estado de Espacios</h5>
                                <div class="leyenda-items">
                                    <div class="item-leyenda disponible">
                                        <span class="indicador-leyenda"></span>
                                        <span class="texto-leyenda">Disponible</span>
                                    </div>
                                    <div class="item-leyenda ocupado">
                                        <span class="indicador-leyenda"></span>
                                        <span class="texto-leyenda">Ocupado</span>
                                    </div>
                                    <div class="item-leyenda seleccionado">
                                        <span class="indicador-leyenda"></span>
                                        <span class="texto-leyenda">Seleccionado</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tarjeta camara-tarjeta">
                        <div class="encabezado-tarjeta">
                            <h2><i class="fas fa-video"></i> Cámara de Estacionamiento</h2>
                        </div>
                        <div class="contenido-tarjeta camara-contenido">
                            <div class="vista-camara">
                                <div>
                                    <h2>Cámara en Vivo</h2>
                                    <img :src="videoFeedUrl" class="video" alt="Video Feed">
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
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const router = useRouter();
const API_URL = 'http://127.0.0.1:8000/api';
const TARIFA_POR_HORA = 20.00;

// --- ESTADO REACTIVO ---
const userName = ref('Cargando...');
const isMenuOpen = ref(false);
const isDarkTheme = ref(false);
const vehiculos = ref([]);
const espacios = ref([]);
const selectedVehicle = ref(null);
const videoFeedUrl = ref('http://127.0.0.1:5000/video_feed');

// --- CÁLCULOS COMPUTADOS ---
const hoursElapsed = computed(() => {
    if (!selectedVehicle.value) return 0;
    const entrada = new Date(selectedVehicle.value.fecha_entrada);
    const ahora = new Date();
    const diffMs = ahora - entrada;
    return diffMs / (1000 * 60 * 60);
});

const totalPagar = computed(() => {
    return Math.ceil(hoursElapsed.value) * TARIFA_POR_HORA;
});

const timeElapsed = computed(() => {
    const totalSeconds = Math.floor(hoursElapsed.value * 60 * 60);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    return `${hours}h ${minutes}m`;
});

// --- FUNCIONES DE UI Y LÓGICA ---

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
        userName.value = response.data.user.name;
    } catch (error) {
        localStorage.removeItem('auth_token'); router.push({ name: 'auth' });
    }
};

const cargarVehiculos = async () => {
    try {
        const response = await axios.get(`${API_URL}/vehiculos/estacionados`);
        vehiculos.value = response.data;
        await generarEspaciosEstacionamiento();
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los vehículos.' });
    }
};

const generarEspaciosEstacionamiento = async () => {
    try {
        const response = await axios.get(`${API_URL}/espacios`);
        const espaciosMapeados = response.data.map(esp => {
            const ocupado = vehiculos.value.some(v => v.seccion === esp.codigo);
            return {
                codigo: esp.codigo,
                estado: ocupado ? 'ocupado' : 'disponible',
                tipo: esp.codigo.startsWith('M') ? 'motocicleta' : 'automovil'
            };
        });
        espacios.value = espaciosMapeados;
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los espacios del mapa.' });
    }
};

const selectVehicle = (vehiculo) => {
    selectedVehicle.value = vehiculo;
};

const formatCurrency = (value) => {
    return `$${value.toFixed(2)}`;
};

const getEstado = (espacio) => {
    if (selectedVehicle.value && selectedVehicle.value.seccion === espacio.codigo) {
        return 'seleccionado';
    }
    return espacio.estado;
};

const getIcono = (espacio) => {
    const vehiculo = vehiculos.value.find(v => v.seccion === espacio.codigo);
    if (espacio.estado === 'ocupado' || vehiculo) {
        return vehiculo?.tipo === 'motocicleta' ? 'fa-motorcycle' : 'fa-car';
    }
    return 'fa-parking';
};

const procesarSalida = async () => {
    const vehiculo = selectedVehicle.value;
    if (!vehiculo) return;

    // Modal de selección de método de pago
    const { value: metodoPago } = await Swal.fire({
        title: 'Método de Pago',
        html: `
            <div style="display: flex; flex-direction: column; gap: 20px; align-items: center; margin-top: 15px; padding: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">
                <div style="font-size: 22px; font-weight: bold; color: #333; text-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 10px;">Total: ${formatCurrency(totalPagar.value)}</div>
                <button id="efectivo" class="payment-btn" style="background: #ffffff; color: #333; border: 2px solid #dee2e6; padding: 18px 35px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; font-size: 18px; font-weight: 600; transition: all 0.3s ease; width: 100%; max-width: 280px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <i class="fas fa-money-bill-wave fa-2x"></i> Efectivo
                </button>
                <button id="tarjeta" class="payment-btn" style="background: #6c757d; color: white; border: none; padding: 18px 35px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; font-size: 18px; font-weight: 600; transition: all 0.3s ease; width: 100%; max-width: 280px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                    <i class="fas fa-credit-card fa-2x"></i> Tarjeta
                </button>
                <button id="spei" class="payment-btn" style="background: #343a40; color: white; border: none; padding: 18px 35px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; font-size: 18px; font-weight: 600; transition: all 0.3s ease; width: 100%; max-width: 280px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
                    <i class="fas fa-university fa-2x"></i> SPEI
                </button>
            </div>
        `,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'payment-modal'
        },
        didOpen: () => {
            const buttons = document.querySelectorAll('.payment-btn');
            buttons.forEach(btn => {
                btn.addEventListener('mouseenter', () => {
                    btn.style.transform = 'scale(1.05)';
                    btn.style.boxShadow = '0 6px 12px rgba(0,0,0,0.2)';
                });
                btn.addEventListener('mouseleave', () => {
                    btn.style.transform = 'scale(1)';
                    btn.style.boxShadow = btn.id === 'efectivo' ? '0 4px 8px rgba(0,0,0,0.1)' : btn.id === 'tarjeta' ? '0 4px 8px rgba(0,0,0,0.2)' : '0 4px 8px rgba(0,0,0,0.3)';
                });
            });
            document.getElementById('efectivo').addEventListener('click', () => Swal.close({ value: 'efectivo' }));
            document.getElementById('tarjeta').addEventListener('click', () => Swal.close({ value: 'tarjeta' }));
            document.getElementById('spei').addEventListener('click', () => Swal.close({ value: 'spei' }));
        }
    });

    if (!metodoPago) return; // Cancelado

    // Manejar según método de pago
    if (metodoPago === 'tarjeta') {
        // Simular procesamiento con Stripe
        Swal.fire({
            title: 'Procesando Pago',
            text: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        // Simular delay de procesamiento
        await new Promise(resolve => setTimeout(resolve, 3000));
        Swal.close();
    } else if (metodoPago === 'spei') {
        // Mostrar número de cuenta SPEI
        const { isConfirmed } = await Swal.fire({
            title: 'Pago por SPEI',
            html: `
                <p>Realice la transferencia al siguiente número de cuenta:</p>
                <strong style="font-size: 18px; color: #007bff;">123456789012345678</strong>
                <p style="margin-top: 10px; font-size: 14px; color: #666;">Una vez realizado el pago, confirme para registrar la salida.</p>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar'
        });
        if (!isConfirmed) return;
    }
    // Para efectivo, proceder directamente

    // Confirmar salida
    const { isConfirmed } = await Swal.fire({
        title: 'Confirmar Salida',
        html: `Total cobrado: <strong>${formatCurrency(totalPagar.value)}</strong><br>¿Desea registrar la salida del vehículo ${vehiculo.placas}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar salida',
        cancelButtonText: 'Cancelar'
    });

    if (isConfirmed) {
        try {
            const response = await axios.post(`${API_URL}/vehiculos/${vehiculo.id}/salida`, {
                monto: totalPagar.value,
            });

            await Swal.fire({
                title: '¡Salida Exitosa!',
                text: `Vehículo ${vehiculo.placas} retirado. Total cobrado: ${formatCurrency(totalPagar.value)}.`,
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });

            // Enviar ticket por WhatsApp si hay número de teléfono
            if (vehiculo.telefono) {
                const ticketId = response.data.ticket_id || 'N/A';
                const mensaje = `Ticket de Salida\n\nVehículo: ${vehiculo.marca} ${vehiculo.modelo}\nPlacas: ${vehiculo.placas}\nSección: ${vehiculo.seccion}\nTiempo: ${timeElapsed.value}\nTotal: ${formatCurrency(totalPagar.value)}\nTicket ID: ${ticketId}\n\nGracias por su visita.`;
                const urlWhatsApp = `https://wa.me/${vehiculo.telefono}?text=${encodeURIComponent(mensaje)}`;
                window.open(urlWhatsApp, '_blank');
            } else {
                await Swal.fire({
                    title: 'Ticket Generado',
                    html: `<p>Ticket ID: <strong>${response.data.ticket_id || 'N/A'}</strong></p><p>No se pudo enviar por WhatsApp porque no hay número de teléfono registrado.</p>`,
                    icon: 'warning',
                    confirmButtonText: 'Aceptar'
                });
            }

            selectedVehicle.value = null;
            await cargarVehiculos();

        } catch (error) {
            Swal.fire({
                title: 'Error de Salida',
                text: error.response?.data?.message || 'Ocurrió un error al registrar la salida.',
                icon: 'error'
            });
        }
    }
};

const calcularTiempo = (fechaEntrada) => {
    const entrada = new Date(fechaEntrada);
    const ahora = new Date();
    const diff = Math.abs(ahora - entrada) / 1000;
    const hours = Math.floor(diff / 3600);
    const minutes = Math.floor((diff % 3600) / 60);
    return `${hours}h ${minutes}m`;
};

// --- CICLO DE VIDA ---
onMounted(() => {
    checkAuthAndFetchUser();
    checkTheme();
    cargarVehiculos();
});
</script>

<style scoped>
/* --- VARIABLES BASE (Definidas en main.css) --- */

/* --- ESTILOS REUTILIZADOS Y CONTENEDOR PRINCIPAL --- */
.encabezado { 
    position: fixed; top: 0; left: 0; width: 100%; height: var(--altura-encabezado); 
    background-color: var(--color-background); border-bottom: 1px solid var(--color-input-border); 
    box-shadow: 0 2px 5px rgba(0,0,0,0.1); z-index: 1000; 
}
.contenedor { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 10px; height: 100%; display: flex; justify-content: space-between; align-items: center; }
.logo a { color: var(--color-primary); text-decoration: none; display: flex; align-items: center; font-size: 1.5rem; font-weight: 700; }
/* Menú Hamburguesa y su ícono de cierre */
.boton-menu { background: transparent; border: none; cursor: pointer; display: none; padding: 5px; outline: none; }
.boton-menu svg { fill: var(--color-primary); width: 24px; height: 24px; }
/* Estilos del menú en ESCRITORIO */
.menu { display: flex; align-items: center; transition: all 0.3s ease; }
.menu a { 
    color: var(--color-text); text-decoration: none; padding: 0 15px; height: var(--altura-encabezado); 
    display: flex; align-items: center; position: relative; font-weight: 500; transition: all 0.3s ease; 
    font-size: 1rem; 
}
/* Línea activa y hover */
.menu a.router-link-exact-active::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background-color: var(--color-primary); transform: scaleX(1); transition: transform 0.3s ease; }
.menu a:not(.router-link-exact-active):hover::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background-color: var(--color-primary); transform: scaleX(0.5); transition: transform 0.3s ease; }
.seccion-usuario { display: flex; align-items: center; margin-left: 20px; border-left: 1px solid var(--color-input-border); padding-left: 20px; }
.seccion-usuario span { margin-right: 10px; font-size: 0.9rem; color: var(--color-text); }
.boton-salir { background: transparent; color: var(--color-primary); border: 1px solid var(--color-primary); padding: 5px 12px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; font-size: 0.9rem; }
.theme-toggle-button { background: transparent; color: var(--color-primary); border: 1px solid var(--color-primary); padding: 5px 8px; border-radius: 50%; cursor: pointer; transition: all 0.3s ease; margin-right: 10px; }

.contenido-principal { margin-top: var(--altura-encabezado); padding: 20px; min-height: calc(100vh - var(--altura-encabezado)); background-color: var(--color-background); }
.titulo-principal { text-align: center; margin-bottom: 20px; color: var(--color-primary); font-size: 1.5rem; font-weight: 600; }

/* --- LAYOUT DE DOS COLUMNAS (TABLA/PAGO | MAPA/CÁMARA) --- */
.grid-contenido.dos-columnas { 
    display: grid; 
    grid-template-columns: 1.7fr 1fr; /* 1.7 veces más ancho para la tabla */
    gap: 20px;
}
.columna-principal { display: flex; flex-direction: column; }
.seccion-camara-mapa { 
    display: grid; 
    grid-template-rows: 1fr 2fr; /* Mapa (1/3) | Cámara (2/3) */
    gap: 20px; 
    height: calc(100vh - var(--altura-encabezado) - 40px); /* Ajuste de altura total */
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .grid-contenido.dos-columnas { grid-template-columns: 1fr; gap: 15px; } 
    .seccion-camara-mapa { height: auto; grid-template-rows: auto auto; gap: 15px; order: 2; }
    .columna-principal { order: 1; }
    .boton-menu { display: block; }
    .menu { display: none; }
    .menu.activo { display: flex; flex-direction: column; position: fixed; width: 100%; background: var(--color-background); top: var(--altura-encabezado); left: 0; height: auto; box-shadow: 0 5px 10px rgba(0,0,0,0.2); }
    .menu.activo a { padding: 10px 20px; height: auto; width: 100%; }
    .menu.activo .seccion-usuario { padding: 10px 20px; border-left: none; border-top: 1px solid var(--color-input-border); margin-left: 0; width: 100%; }
}

/* --- ESTILOS DE TABLA Y PAGO --- */
.tarjeta { background-color: var(--color-form-background); border-radius: 8px; border: 1px solid var(--color-input-border); overflow: hidden; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05); }
.encabezado-tarjeta { background-color: var(--color-primary); color: var(--color-button-text-hover); padding: 12px 15px; }
.encabezado-tarjeta h2 { font-size: 1.1rem; font-weight: 500; display: flex; align-items: center; gap: 8px; }
.contenido-tarjeta { padding: 15px; }

/* Tabla */
.tabla-vehiculos { width: 100%; border-collapse: collapse; font-size: 0.9rem; table-layout: fixed; }
.tabla-vehiculos th, .tabla-vehiculos td { padding: 8px 12px; border-bottom: 1px solid var(--color-input-border); text-align: left; color: var(--color-text); }
.tabla-vehiculos th { background-color: var(--color-input-background); color: var(--color-text); font-weight: 600; }
.tabla-vehiculos tr:hover { background-color: var(--color-input-border); cursor: pointer; }

/* Botones y Pago */
.boton-seleccionar { background: var(--color-primary); color: var(--color-button-text-hover); border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.85rem; }
.boton-seleccionar.seleccionado { background: var(--color-seleccionado); color: var(--color-primary); font-weight: 600; }
.info-pago { display: grid; grid-template-columns: auto 1fr; gap: 5px 15px; align-items: center; }
.info-pago .etiqueta { color: var(--color-text); font-weight: 500; font-size: 0.9rem; margin: 0; }
.total-pago { border-top: 1px solid var(--color-input-border); padding-top: 10px; margin-top: 15px; }
.total-pago span { color: var(--color-ocupado); font-weight: 700; }
.boton-imprimir { background: var(--color-ocupado); color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; font-size: 1rem; width: 100%; }

/* --- MAPA Y CÁMARA (Visibilidad de Colores Corregida) --- */
.mapa-tarjeta { order: 1; } 
.camara-tarjeta { order: 2; }
.mapa-estacionamiento { background-color: var(--color-input-background); padding: 10px; min-height: 150px; }
.grid-espacios { display: grid; grid-template-columns: repeat(auto-fill, minmax(40px, 1fr)); gap: 4px; padding: 5px; }

/* Estilos de los elementos individuales del mapa */
.espacio-estacionamiento { 
    aspect-ratio: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; 
    border-radius: 4px; cursor: pointer; padding: 3px; font-size: 0.7rem; 
    background-color: var(--color-form-background); border: 1px solid var(--color-input-border);
    color: var(--color-text);
}
.espacio-estacionamiento i, .espacio-estacionamiento span { font-size: 0.7rem; transition: color 0.2s ease; }

/* VISIBILIDAD DE ESTADOS (ALTA ESPECIFICIDAD) */
/* 1. DISPONIBLE (VERDE) */
.espacio-estacionamiento.disponible { border: 2px solid var(--color-disponible); background-color: var(--color-input-background); }
.espacio-estacionamiento.disponible i, .espacio-estacionamiento.disponible span { color: var(--color-disponible) !important; }

/* 2. OCUPADO (ROJO) */
.espacio-estacionamiento.ocupado { border: 2px solid var(--color-ocupado); opacity: 0.8; }
.espacio-estacionamiento.ocupado i, .espacio-estacionamiento.ocupado span { color: var(--color-ocupado) !important; }

/* 3. SELECCIONADO (AMARILLO/DORADO) */
.espacio-estacionamiento.seleccionado { border: 3px solid var(--color-seleccionado); background-color: var(--color-seleccionado); }
.espacio-estacionamiento.seleccionado i, .espacio-estacionamiento.seleccionado span { color: var(--color-primary) !important; }

/* LEYENDA */
.leyenda-mapa { margin-top: 15px; padding: 10px; background-color: var(--color-input-background); border: 1px solid var(--color-input-border); border-radius: 8px; }
.leyenda-mapa h5 { font-size: 0.95rem; color: var(--color-text); margin-bottom: 10px; }
.leyenda-items { display: flex; flex-wrap: wrap; justify-content: space-around; gap: 10px; }
.item-leyenda { font-size: 0.85rem; color: var(--color-text); }
.indicador-leyenda { width: 14px; height: 14px; border-radius: 50%; display: inline-block; margin-right: 5px; border: 1px solid var(--color-input-border); }

.item-leyenda.disponible .indicador-leyenda { background-color: var(--color-disponible); }
.item-leyenda.ocupado .indicador-leyenda { background-color: var(--color-ocupado); }
.item-leyenda.seleccionado .indicador-leyenda { background-color: var(--color-seleccionado); }

/* CÁMARA */
.vista-camara { 
    background-color: var(--color-primary); 
    border-radius: 4px; 
    aspect-ratio: 16 / 9; 
    max-height: 360px; 
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
}
.feed-camara h2 { font-size: 0.9rem; }
.camara-tarjeta { flex-grow: 1; }
.video { 
    width: auto; 
    height: 100%; 
    max-width: 100%; 
    object-fit: contain; 
    border-radius: 4px; 
    display: block;
}
</style>