<template>
  <div class="registrar-auto-view">
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
        <div class="contenedor-registro">
            <h1 class="titulo-principal">Registrar Vehículo</h1>
            
            <div class="grid-registro">
                
                <section class="seccion-datos">
                    <div class="tarjeta">
                        <div class="encabezado-tarjeta">
                            <h2><i class="fas fa-car"></i> Datos del Vehículo</h2>
                        </div>
                        <div class="contenido-tarjeta">
                            <form class="formulario-datos" @submit.prevent="handleRegistroVehiculo">
                                
                                <div class="grupo-formulario">
                                    <label class="form-label">Tipo de Vehículo</label>
                                    <div class="grupo-radio">
                                        <label class="opcion-radio" v-for="type in ['automovil', 'motocicleta', 'otros']" :key="type">
                                            <input type="radio" name="tipo-vehiculo" :value="type" v-model="form.tipo" :disabled="isFormLocked">
                                            <span class="radio-custom"></span>
                                            {{ type.charAt(0).toUpperCase() + type.slice(1) }}
                                        </label>
                                    </div>
                                </div>

                                <div class="grupo-formulario">
                                    <label for="placas">Placas</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" id="placas" v-model="form.placas" placeholder="Ingrese las placas" required>
                                        <button class="btn btn-outline-secondary boton-buscar-placas" type="button" @click="buscarPlacas">
                                            Buscar
                                        </button>
                                    </div>
                                </div>

                                <div class="grupo-formulario">
                                    <label for="marca">Marca</label>
                                    <input type="text" id="marca" v-model="form.marca" placeholder="Ingrese la marca" required>
                                </div>
                                <div class="grupo-formulario">
                                    <label for="modelo">Modelo</label>
                                    <input type="text" id="modelo" v-model="form.modelo" placeholder="Ingrese el modelo" required>
                                </div>

                                <div class="grupo-formulario">
                                    <label for="color">Color</label>
                                    <div class="input-group">
                                        <input type="text" id="color" v-model="form.color" placeholder="Seleccionar color" readonly required>
                                        <button class="btn btn-outline-secondary" type="button" @click="toggleColorModal">
                                            <i class="fas fa-palette" :style="{ color: selectedColorCode || 'var(--color-primary)' }"></i>
                                            Seleccionar
                                        </button>
                                    </div>
                                </div>

                                <div class="grupo-formulario">
                                    <label for="telefono">Teléfono</label>
                                    <input type="tel" id="telefono" v-model="form.telefono" placeholder="Ingrese el teléfono">
                                </div>
                                
                                <div class="grupo-formulario">
                                    <label for="seccion">Sección</label>
                                    <input type="text" id="seccion" v-model="form.seccion" placeholder="Sección asignada" readonly required>
                                </div>
                                
                                <div class="campos-motocicleta" v-show="form.tipo === 'motocicleta'">
                                    <div class="grupo-formulario">
                                        <label for="nombre-cliente">Nombre del Cliente</label>
                                        <input type="text" id="nombre-cliente" v-model="form.nombre_cliente" placeholder="Ingrese el nombre del cliente">
                                    </div>
                                    <div class="grupo-formulario">
                                        <label for="pertenencias">Pertenencias</label>
                                        <textarea id="pertenencias" v-model="form.pertenencias" placeholder="Describa las pertenencias dejadas"></textarea>
                                    </div>
                                </div>
                                
                                <div class="campos-otros" v-show="form.tipo === 'otros'">
                                    <div class="grupo-formulario">
                                        <label for="tipo-especifico">Tipo específico</label>
                                        <input type="text" id="tipo-especifico" v-model="form.tipo_especifico" placeholder="Ej: Camión, Bicicleta, etc.">
                                    </div>
                                    <div class="grupo-formulario">
                                        <label for="observaciones">Observaciones</label>
                                        <textarea id="observaciones" v-model="form.observaciones" placeholder="Ingrese observaciones adicionales"></textarea>
                                    </div>
                                </div>
                                
                                <button type="submit" class="boton-registrar" :disabled="isRegistering || !form.seccion">
                                    <i v-if="isRegistering" class="fas fa-spinner fa-spin"></i>
                                    <i v-else class="fas fa-ticket-alt"></i> 
                                    {{ isRegistering ? 'Registrando...' : 'Registrar e Imprimir Ticket' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </section>
                
                <section class="seccion-camara-mapa">
                    <div class="tarjeta camara-tarjeta">
                        <div class="encabezado-tarjeta">
                            <h2><i class="fas fa-video"></i> Cámara de Estacionamiento</h2>
                        </div>
                        <div class="contenido-tarjeta camara-contenido">
                            <div class="vista-camara">
                                <div class="estado-camara">
                                    <i class="fas fa-circle"></i>
                                    <span>Cámara conectada</span>
                                </div>
                                <div class="feed-camara">
                                    <div>
                                        <h2>Cámara en Vivo</h2>
                                        <img :src="videoFeedUrl" class="video" alt="Video Feed">
                                    </div>
                                </div>
                                <div class="detectando-vehiculo" v-show="isDetecting">
                                    <i class="fas fa-car"></i>
                                    <p>Detectando vehículo...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tarjeta mapa-tarjeta">
                        <div class="encabezado-tarjeta">
                            <h2><i class="fas fa-map-marked-alt"></i> Mapa del Estacionamiento</h2>
                        </div>
                        <div class="contenido-tarjeta">
                            <div class="mapa-estacionamiento">
                                <div class="grid-espacios">
                                    <div 
                                        v-for="espacio in espacios" 
                                        :key="espacio.codigo"
                                        :class="['espacio-estacionamiento', espacio.estado, { 'seleccionado': espacio.codigo === form.seccion }]"
                                        @click="seleccionarEspacio(espacio)"
                                    >
                                        <i :class="['fas', espacio.estado === 'ocupado' ? 'fa-car' : 'fa-parking']"></i>
                                        <span>{{ espacio.codigo }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="leyenda-mapa">
                                <div class="leyenda-contenedor">
                                    <h3 class="titulo-leyenda">Estado de Espacios</h3>
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
                                        <div class="item-leyenda detectado">
                                            <span class="indicador-leyenda"></span>
                                            <span class="texto-leyenda">Detectado</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <div class="modal-overlay" v-show="isColorPickerOpen" @click.self="closeColorModal">
        <div class="modal-content-colores">
            <h3>Selecciona un Color</h3>
            <div class="modal-grid-colores">
                <div 
                    v-for="c in colorOptions" 
                    :key="c.name"
                    class="color-swatch"
                    :class="{ 'selected': form.color === c.name }"
                    :style="{ backgroundColor: c.code, border: c.name === 'Blanco' ? '1px solid #ddd' : '' }"
                    @click="selectColor(c.name, c.code)"
                >
                    <i v-if="form.color === c.name" class="fas fa-check"></i>
                    <span class="color-label">{{ c.name }}</span>
                </div>
            </div>
            <button class="modal-close-button" @click="closeColorModal">Cerrar</button>
        </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';

const isFormLocked = ref(true);
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

// --- CONFIGURACIÓN E INICIALIZACIÓN ---
const router = useRouter();
const API_URL = 'http://127.0.0.1:8000/api';

// --- ESTADO REACTIVO ---
const userName = ref('Cargando...');
const isMenuOpen = ref(false);
const isDarkTheme = ref(false);
const isDetecting = ref(false);
const isRegistering = ref(false);
const videoFeedUrl = ref('http://127.0.0.1:5000/video_feed');
const detectionUrl = 'http://127.0.0.1:5000/last_detection';
const espacios = ref([]);
const vehiculos = ref([]);
const isColorPickerOpen = ref(false);
const selectedColorCode = ref(null);
const pollingInterval = ref(null);
const detectedSpace = ref(null);

const form = reactive({
    tipo: '',
    marca: '',
    modelo: '',
    placas: '',
    color: '',
    seccion: '',
    nombre_cliente: '',
    pertenencias: '',
    tipo_especifico: '',
    observaciones: '',
    telefono: ''
});

const colorOptions = [
    { name: 'Rojo', code: '#ff0000' }, { name: 'Azul', code: '#0000ff' }, { name: 'Verde', code: '#008000' },
    { name: 'Amarillo', code: '#ffff00' }, { name: 'Naranja', code: '#ffa500' }, { name: 'Negro', code: '#000000' },
    { name: 'Blanco', code: '#ffffff' }, { name: 'Gris', code: '#808080' }, { name: 'Plateado', code: '#c0c0c0' },
    { name: 'Marrón', code: '#a52a2a' }, { name: 'Morado', code: '#800080' }, { name: 'Turquesa', code: '#40e0d0' },
    { name: 'Vino', code: '#722f37' }, { name: 'Rosa', code: '#ffc0cb' }, { name: 'Beige', code: '#f5f5dc' }
];

// --- LÓGICA DE UI Y AUTENTICACIÓN ---

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
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        router.push({ name: 'auth' });
    }
};
const checkAuthAndFetchUser = async () => {
    const token = localStorage.getItem('auth_token');
    if (!token) { router.push({ name: 'auth' }); return; }
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    axios.defaults.headers.common['Accept'] = 'application/json';
    try {
        const response = await axios.get(`${API_URL}/check-auth`);
        userName.value = response.data.user.name;
    } catch (error) {
        localStorage.removeItem('auth_token'); router.push({ name: 'auth' });
    }
};

// --- LÓGICA DE FORMULARIO Y MAPA ---

// Lógica de Modal de Color
const toggleColorModal = () => { isColorPickerOpen.value = !isColorPickerOpen.value; };
const closeColorModal = () => { isColorPickerOpen.value = false; };
const selectColor = (name, code) => {
    form.color = name;
    selectedColorCode.value = code;
    closeColorModal();
};

// Lógica de selección de espacio
const seleccionarEspacio = (espacio) => {
    if (espacio.estado === 'disponible') {
        form.seccion = espacio.codigo === form.seccion ? '' : espacio.codigo;
    }
};

// Cargar vehículos estacionados
const cargarVehiculos = async () => {
    try {
        const response = await axios.get(`${API_URL}/vehiculos/estacionados`);
        vehiculos.value = response.data;
        await generarEspaciosEstacionamiento();
    } catch (error) {
        console.error('Error al cargar vehículos:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los vehículos' });
    }
};

// Generar y actualizar el mapa de espacios
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
        console.error('Error al cargar espacios:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los espacios' });
    }
};

// Búsqueda por placas
const buscarPlacas = async () => {
    const placas = form.placas.trim();
    if (!placas) {
        Swal.fire({ icon: 'warning', title: 'Placas vacías', text: 'Por favor ingrese placas para buscar' });
        return;
    }

    try {
        const response = await axios.get(`${API_URL}/vehiculos/buscar?placas=${encodeURIComponent(placas)}`);
        const vehiculos = Array.isArray(response.data) ? response.data : [response.data];
        if (vehiculos.length === 0) {
            Swal.fire({ icon: 'info', title: 'No encontrado', text: 'No se encontró un vehículo con esas placas' });
        } else if (vehiculos.length === 1) {
            const v = vehiculos[0];
            form.marca = v.marca;
            form.modelo = v.modelo;
            form.color = v.color;
            form.nombre_cliente = v.nombre_cliente;
            form.pertenencias = v.pertenencias;
            form.telefono = v.telefono || '';
            Swal.fire({ icon: 'success', title: 'Vehículo encontrado', text: 'Los datos han sido rellenados' });
        } else {
            // show list
            const options = vehiculos.map(v => `${v.marca} ${v.modelo} (${v.placas})`).join('\n');
            const { value: selected } = await Swal.fire({
                title: 'Múltiples vehículos encontrados',
                input: 'select',
                inputOptions: vehiculos.reduce((acc, v, i) => { acc[i] = `${v.marca} ${v.modelo} (${v.placas})`; return acc; }, {}),
                inputPlaceholder: 'Selecciona un vehículo',
                showCancelButton: true,
            });
            if (selected !== undefined) {
                const v = vehiculos[selected];
                form.marca = v.marca;
                form.modelo = v.modelo;
                form.color = v.color;
                form.nombre_cliente = v.nombre_cliente;
                form.pertenencias = v.pertenencias;
                form.telefono = v.telefono || '';
            }
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: error.response?.data?.message || 'Error al buscar vehículo' });
    }
};

// Registro de vehículo
const handleRegistroVehiculo = async () => {
    const { tipo, marca, modelo, placas, color, seccion } = form;
    
    if (!marca || !modelo || !placas || !color || !seccion) {
        Swal.fire({ icon: 'error', title: 'Campos incompletos', text: 'Complete los datos del vehículo y seleccione un espacio.' });
        return;
    }
    
    const payload = { tipo, marca, modelo, placas, color, seccion, telefono: form.telefono };
    // Agregar campos condicionales si es necesario...

    const { isConfirmed } = await Swal.fire({
        icon: 'question', title: 'Confirmar registro', html: `Registrar ${placas} en ${seccion}?`, showCancelButton: true, confirmButtonText: 'Registrar', cancelButtonText: 'Cancelar'
    });
    
    if (!isConfirmed) return;
    
    isRegistering.value = true;
    try {
        const response = await axios.post(`${API_URL}/vehiculos`, payload);
        const ticketId = response.data.ticket_id;
        
        Swal.fire({ icon: 'success', title: 'Registro exitoso', html: `Ticket #${ticketId}`, confirmButtonText: 'Aceptar' });

        // Agregar el vehículo registrado a la lista local para actualización inmediata
        vehiculos.value.push({
            id: response.data.id, // Asumiendo que la respuesta incluye el ID
            tipo: form.tipo,
            marca: form.marca,
            modelo: form.modelo,
            placas: form.placas,
            color: form.color,
            seccion: form.seccion,
            telefono: form.telefono,
            // Otros campos si es necesario
        });
        await generarEspaciosEstacionamiento();

        // Limpiar formulario
        Object.keys(form).forEach(key => form[key] = '');
        selectedColorCode.value = null;
        form.seccion = ''; // Reset seccion
        isFormLocked.value = true; // Lock the form again for next detection
        // Wait a bit before restarting polling to avoid rapid detections
        setTimeout(() => {
            startPolling(); // Restart polling for next vehicle
        }, 5000); // 5 seconds delay

    } catch (error) {
        let text = 'Error al registrar el vehículo';
        if (error.response?.status === 409) text = 'El espacio seleccionado ya fue ocupado.';
        else if (error.response?.data?.message) text = error.response.data.message;

        Swal.fire({ icon: 'error', title: 'Error', text: text });
    } finally {
        isRegistering.value = false;
        // Actualizar la lista de vehículos y el mapa
        await cargarVehiculos();
    }
};


// Polling para detección de vehículo
const startPolling = () => {
    if (pollingInterval.value) return; // Ya está corriendo
    // Find an available space and set it to 'detectado'
    const availableSpace = espacios.value.find(esp => esp.estado === 'disponible');
    if (availableSpace) {
        availableSpace.estado = 'detectado';
        detectedSpace.value = availableSpace.codigo;
        isDetecting.value = true;
    }
    pollingInterval.value = setInterval(async () => {
        try {
            const response = await axios.get(detectionUrl);
            if (response.data && response.data.tipo) {
                if (isFormLocked.value) {
                    // First detection: unlock the form
                    form.tipo = response.data.tipo;
                    isFormLocked.value = false; // Unlock the form after detection
                    // Set the space to 'seleccionado'
                    const space = espacios.value.find(esp => esp.codigo === detectedSpace.value);
                    if (space) {
                        space.estado = 'seleccionado';
                        form.seccion = detectedSpace.value;
                    }
                    isDetecting.value = false;
                    Swal.fire({ icon: 'success', title: 'Vehículo detectado', text: `Tipo: ${response.data.tipo}. El formulario ha sido desbloqueado.` });
                } else {
                    // Form already unlocked: update tipo if different
                    if (form.tipo !== response.data.tipo) {
                        form.tipo = response.data.tipo;
                        Swal.fire({ icon: 'info', title: 'Tipo de vehículo actualizado', text: `Nuevo tipo detectado: ${response.data.tipo}.` });
                    }
                }
            }
        } catch (error) {
            console.error('Error en polling de detección:', error);
        }
    }, 2000); // Cada 2 segundos
};

const stopPolling = () => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
};

// --- CICLO DE VIDA ---
onMounted(async () => {
    checkAuthAndFetchUser();
    checkTheme();
    await cargarVehiculos();
    startPolling(); // Iniciar polling al montar
});

onUnmounted(() => {
    stopPolling(); // Limpiar al desmontar
});
</script>

<style scoped>
/* Disable form inputs when locked */
input:disabled,
textarea:disabled,
select:disabled,
button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
</style>

<style scoped>
/* --- VARIABLES BASE (Definidas en main.css) --- */

/* --- ESTILOS COMPACTOS Y REUTILIZADOS --- */
.encabezado { position: fixed; top: 0; left: 0; width: 100%; height: var(--altura-encabezado); background-color: var(--color-background); border-bottom: 1px solid var(--color-input-border); box-shadow: 0 2px 5px rgba(0,0,0,0.1); z-index: 1000; }
.contenedor { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 10px; height: 100%; display: flex; justify-content: space-between; align-items: center; }
.logo a { color: var(--color-primary); text-decoration: none; display: flex; align-items: center; font-size: 1.5rem; }
/* Menú Hamburguesa y su ícono de cierre */
.boton-menu { background: transparent; border: none; cursor: pointer; display: flex; padding: 5px; outline: none; }
.boton-menu svg { fill: var(--color-primary); width: 24px; height: 24px; }
.menu { display: flex; align-items: center; transition: all 0.3s ease; }
.menu a { color: var(--color-text); text-decoration: none; padding: 0 15px; height: var(--altura-encabezado); display: flex; align-items: center; position: relative; font-weight: 500; transition: all 0.3s ease; }
.menu a.router-link-exact-active::after { content: ''; position: absolute; bottom: 0; left: 15px; width: calc(100% - 30px); height: 2px; background-color: var(--color-primary); transform: scaleX(1); transform-origin: left; }
.seccion-usuario { display: flex; align-items: center; margin-left: 10px; border-left: 1px solid var(--color-input-border); padding-left: 10px; }
.seccion-usuario span { margin-right: 10px; font-size: 0.9rem; color: var(--color-text); }
.boton-salir { background: transparent; color: var(--color-primary); border: 1px solid var(--color-primary); padding: 3px 10px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; }
.boton-salir:hover { background: var(--color-primary); color: var(--color-button-text-hover); }
.theme-toggle-button { background: transparent; color: var(--color-primary); border: 1px solid var(--color-primary); padding: 5px 8px; border-radius: 50%; cursor: pointer; transition: all 0.3s ease; margin-right: 10px; }
.theme-toggle-button:hover { background: var(--color-primary); color: var(--color-button-text-hover); }

/* CONTENIDO PRINCIPAL COMPACTO */
.contenido-principal {
    margin-top: var(--altura-encabezado); padding: 10px; min-height: calc(100vh - var(--altura-encabezado));
    background-color: var(--color-background);
}
.contenedor-registro { max-width: 1400px; margin: 0 auto; padding: 10px; }
.titulo-principal { text-align: center; margin-bottom: 15px; color: var(--color-primary); font-size: 1.5rem; font-weight: 600; }

/* GRID DE REGISTRO */
.grid-registro { display: grid; grid-template-columns: 1fr; gap: 10px; }
.tarjeta { background-color: var(--color-form-background); border-radius: 8px; border: 1px solid var(--color-input-border); overflow: hidden; margin-bottom: 10px; transition: transform 0.3s ease, box-shadow 0.3s ease; }
.tarjeta:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
.encabezado-tarjeta { background-color: var(--color-primary); color: var(--color-button-text-hover); padding: 8px 12px; }
.encabezado-tarjeta h2 { font-size: 1rem; font-weight: 500; display: flex; align-items: center; gap: 6px; }
.contenido-tarjeta { padding: 12px; }
.formulario-datos { display: grid; gap: 10px; }
.grupo-formulario { display: flex; flex-direction: column; gap: 4px; }
.grupo-formulario label { font-weight: 500; color: var(--color-text); font-size: 0.9rem; }
.grupo-formulario input, .grupo-formulario textarea { 
    background-color: var(--color-input-background); border: 1px solid var(--color-input-border);
    border-radius: 4px; padding: 6px 10px; color: var(--color-text); font-size: 0.9rem; 
}
.grupo-formulario input[readonly] { background-color: var(--color-form-background); cursor: not-allowed; }

/* Input Group y Botones de búsqueda */
.input-group { display: flex; width: 100%; align-items: stretch; }
.input-group input { flex-grow: 1; border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0; }
.input-group-text { 
    display: flex; 
    align-items: center; 
    padding: 6px 10px; 
    background-color: var(--color-input-background); 
    border: 1px solid var(--color-input-border); 
    border-right: none; 
    border-top-right-radius: 0; 
    border-bottom-right-radius: 0; 
    border-top-left-radius: 4px; 
    border-bottom-left-radius: 4px; 
    color: var(--color-primary); 
}
.input-group button { 
    margin-left: 5px; 
    background: var(--color-input-background); 
    color: var(--color-primary); 
    border: 1px solid var(--color-input-border); 
    padding: 0 10px; /* Compactar el padding del botón */
    border-radius: 4px;
}
.input-group button:hover { background: var(--color-primary); color: var(--color-button-text-hover); border-color: var(--color-primary); }

.boton-registrar { 
    margin-top: 5px; padding: 8px 15px; font-size: 0.9rem; gap: 6px; 
    background-color: var(--color-primary); color: var(--color-button-text-hover); border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center;
}
.boton-registrar:hover { background-color: var(--color-secondary); }

/* Cámara y Detección */
.vista-camara { background-color: var(--color-primary); border-radius: 4px; aspect-ratio: 16/9; position: relative; overflow: hidden; }
.feed-camara { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--color-button-text-hover); }
.vista-camara img { width: 100%; height: 100%; object-fit: cover; }
.estado-camara { position: absolute; top: 8px; right: 8px; background-color: rgba(0,0,0,0.7); padding: 5px 8px; border-radius: 20px; color: white; display: flex; align-items: center; gap: 5px; font-size: 0.8rem; z-index: 10; }
.estado-camara i { color: var(--color-disponible); }

/* --- MAPA DE ESPACIOS Y VISIBILIDAD DE ESTADOS (Corregido y Optimizado) --- */
.mapa-estacionamiento { 
    background-color: var(--color-input-background); 
    border-radius: 4px; 
    padding: 10px; 
    margin-bottom: 10px; 
    min-height: 200px; /* Asegura un tamaño mínimo */
}

/* Ajuste de cuadrícula más apretado */
.grid-espacios { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(45px, 1fr)); /* Más compacto */
    gap: 4px; /* Menos espacio entre celdas */
    padding: 5px; 
}

.espacio-estacionamiento { 
    aspect-ratio: 1; 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    justify-content: center; 
    border-radius: 4px; 
    cursor: pointer; 
    transition: all 0.3s ease; 
    background-color: var(--color-form-background);
    border: 1px solid var(--color-input-border); /* Borde por defecto para visibilidad */
    color: var(--color-text); /* Color de texto base */
    padding: 3px; 
    font-size: 0.75rem; /* Fuente pequeña para caber */
}

/* VISIBILIDAD DE ESTADOS (CORRECCIÓN DE COLORES) */
/* 1. DISPONIBLE (VERDE) */
.espacio-estacionamiento.disponible { 
    border: 2px solid var(--color-disponible); 
    background-color: var(--color-input-background); 
}
.espacio-estacionamiento.disponible i,
.espacio-estacionamiento.disponible span {
    color: var(--color-disponible); /* Fuerza color verde */
}

/* 2. OCUPADO (ROJO) */
.espacio-estacionamiento.ocupado { 
    border: 2px solid var(--color-ocupado); 
    background-color: var(--color-form-background);
    opacity: 0.8; 
    cursor: not-allowed;
}
.espacio-estacionamiento.ocupado i,
.espacio-estacionamiento.ocupado span {
    color: var(--color-ocupado); /* Fuerza color rojo */
}

/* 3. SELECCIONADO (AMARILLO/DORADO) */
.espacio-estacionamiento.seleccionado {
    border: 3px solid var(--color-seleccionado);
    background-color: var(--color-seleccionado);
    /* El color de texto debe ser OSCURO para contraste con el amarillo brillante */
    color: #000000;
    box-shadow: 0 0 5px var(--color-seleccionado);
}
.espacio-estacionamiento.seleccionado i,
.espacio-estacionamiento.seleccionado span {
    color: #000000; /* Fuerza color negro para contraste */
}

/* 4. DETECTADO (AZUL) */
.espacio-estacionamiento.detectado {
    border: 2px solid var(--color-detectado);
    background-color: var(--color-detectado);
    color: #000000;
}
.espacio-estacionamiento.detectado i,
.espacio-estacionamiento.detectado span {
    color: #000000;
}


/* --- LEYENDA (CORRECCIÓN DE COLORES) --- */
.leyenda-mapa { 
    margin-top: 10px; 
    padding: 10px; 
    background-color: var(--color-form-background); 
    border-radius: 8px; 
    border: 1px solid var(--color-input-border); 
}
.leyenda-mapa h3 { 
    font-size: 0.9rem; 
    margin-bottom: 8px; 
    color: var(--color-text); 
}

.leyenda-items { 
    display: flex; 
    justify-content: space-around; 
    flex-wrap: wrap; 
    gap: 10px; /* Reducimos el espacio */
}
.item-leyenda { 
    display: flex; 
    align-items: center; 
    gap: 5px; 
    padding: 5px 8px; 
    border-radius: 4px; 
    background-color: var(--color-input-background); 
}
.indicador-leyenda { 
    width: 12px; 
    height: 12px; 
    border-radius: 50%; 
    display: inline-block; 
    border: 1px solid var(--color-input-border);
}
.texto-leyenda { 
    color: var(--color-text); 
    font-size: 0.8rem;
}

/* Asegurar colores para la leyenda */
.disponible .indicador-leyenda { background-color: var(--color-disponible); }
.ocupado .indicador-leyenda { background-color: var(--color-ocupado); }
.seleccionado .indicador-leyenda { background-color: var(--color-seleccionado); }
.detectado .indicador-leyenda { background-color: var(--color-detectado); }

/* MODAL DE SELECCIÓN DE COLORES (Minimalista) */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.7); z-index: 9999; display: flex; justify-content: center; align-items: center; }
.modal-content-colores { background-color: var(--color-background); border-radius: 10px; padding: 20px; width: 90%; max-width: 450px; box-shadow: 0 5px 25px rgba(0, 0, 0, 0.4); }
.modal-content-colores h3 { color: var(--color-primary); font-size: 1.2rem; margin-bottom: 15px; text-align: center; }
.modal-grid-colores { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; }
.color-swatch { aspect-ratio: 1; border-radius: 8px; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; border: 1px solid var(--color-input-border); position: relative; padding-top: 10px; }
.color-swatch:hover { transform: scale(1.05); box-shadow: 0 0 8px rgba(0, 0, 0, 0.2); }
.color-swatch i { color: white; font-size: 1rem; opacity: 0; transition: opacity 0.2s; position: absolute; top: 35%; }
.color-swatch.selected i { opacity: 1; }
.color-label { font-size: 0.7rem; color: var(--color-text); margin-top: 5px; text-align: center; padding-top: 5px; }
.modal-close-button { background: var(--color-primary); color: var(--color-button-text-hover); border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; margin-top: 15px; width: 100%; }


/* RESPONSIVE */
@media (min-width: 1024px) {
    .grid-registro { grid-template-columns: 1.5fr 1fr; } 
    .seccion-camara-mapa { display: grid; grid-template-rows: 1fr 1fr; gap: 10px; }
    .boton-menu { display: none !important; }
}
@media (max-width: 1024px) {
    .boton-menu { display: block; }
    .grid-registro { grid-template-columns: 1fr; }
    .grid-espacios { grid-template-columns: repeat(4, 1fr); }
    .modal-grid-colores { grid-template-columns: repeat(4, 1fr); }
    .menu { display: none; }
    .menu.activo { display: flex; flex-direction: column; position: fixed; width: 100%; background: var(--color-background); top: var(--altura-encabezado); left: 0; height: auto; }
}
</style>