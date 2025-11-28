import { createRouter, createWebHistory } from 'vue-router'
import AuthView from '../views/AuthView.vue'
import HomeView from '../views/HomeView.vue'
import RegistrarAutoView from '@/views/RegistrarAutoView.vue';
import SalidaAutoView from '../views/SalidaAutoView.vue'
import CuentasView from '../views/CuentasView.vue'
import AdminDashboard from '../views/AdminDashboard.vue'
import ReservationView from '../views/ReservationView.vue'
import ChatbotView from '../views/ChatbotView.vue'
import MinimapView from '../views/MinimapView.vue'

/**
 * Función que verifica si el usuario tiene un token de autenticación.
 * @returns {boolean} True si hay token, False si no.
 */
const isAuthenticated = () => {
  return !!localStorage.getItem('auth_token');
}

/**
 * Función que obtiene el rol del usuario desde localStorage.
 * @returns {string|null} El rol del usuario o null si no está disponible.
 */
const getUserRole = () => {
  const userData = localStorage.getItem('user');
  if (userData) {
    try {
      const user = JSON.parse(userData);
      return user.role;
    } catch (e) {
      return null;
    }
  }
  return null;
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // 1. RUTA DE AUTENTICACIÓN (Login y Registro)
    {
      path: '/',
      name: 'auth',
      component: AuthView,
      beforeEnter: (to, from, next) => {
        if (isAuthenticated()) {
          const role = getUserRole();
          if (role === 'admin') {
            next('/admin');
          } else {
            next({ name: 'home' });
          }
        } else {
          next();
        }
      }
    },

    // 2. RUTA DE INICIO (Página Principal/Dashboard para trabajadores y usuarios)
    {
      path: '/inicio',
      name: 'home',
      component: HomeView,
      meta: { requiresAuth: true, allowedRoles: ['gestor', 'user'] }
    },

    // 3. RUTA DE ADMIN DASHBOARD
    {
      path: '/admin',
      name: 'admin',
      component: AdminDashboard,
      meta: { requiresAuth: true, allowedRoles: ['admin'] }
    },

    // 4. RUTAS DE NAVEGACIÓN SECUNDARIAS (Protegidas por rol)

    {
      path: '/RegistrarAutoView',
      name: 'RegistrarAutoView',
      component: RegistrarAutoView,
      meta: { requiresAuth: true, allowedRoles: ['gestor'] }
    },
    {
      path: '/SalidaAutoView',
      name: 'SalidaAutoView',
      component: SalidaAutoView,
      meta: { requiresAuth: true, allowedRoles: ['gestor'] }
    },
    {
      path: '/cuentas',
      name: 'cuentas',
      component: CuentasView,
      meta: { requiresAuth: true, allowedRoles: ['gestor'] }
    },
    {
      path: '/historial',
      name: 'historial',
      component: CuentasView,
      meta: { requiresAuth: true, allowedRoles: ['user'] }
    },
    {
      path: '/reservations',
      name: 'reservations',
      component: ReservationView,
      meta: { requiresAuth: true, allowedRoles: ['user'] }
    },
    {
      path: '/chatbot',
      name: 'chatbot',
      component: ChatbotView,
      meta: { requiresAuth: true, allowedRoles: ['user'] }
    },
    {
      path: '/minimap',
      name: 'minimap',
      component: MinimapView,
      meta: { requiresAuth: true, allowedRoles: ['user'] }
    }
  ]
})

// --- GUARDIÁN DE NAVEGACIÓN GLOBAL: Protección de Rutas ---

router.beforeEach((to, from, next) => {
  // Si la ruta requiere autenticación y el usuario NO tiene token
  if (to.meta.requiresAuth && !isAuthenticated()) {
    next({ name: 'auth' });
    return;
  }

  // Si la ruta requiere roles específicos
  if (to.meta.allowedRoles) {
    const userRole = getUserRole();
    if (!userRole || !to.meta.allowedRoles.includes(userRole)) {
      // Redirigir al dashboard apropiado según el rol
      if (userRole === 'admin') {
        next('/admin');
      } else {
        next({ name: 'home' });
      }
      return;
    }
  }

  // Permitir continuar la navegación
  next();
});

export default router
