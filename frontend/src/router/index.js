// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/Login.vue'),
    meta: { requiresAuth: false, guestOnly: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/auth/Register.vue'),
    meta: { requiresAuth: false, guestOnly: true }
  },
  {
    path: '/',
    name: 'Dashboard',
    component: () => import('@/views/dashboard/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/students',
    name: 'StudentList',
    component: () => import('@/views/students/StudentList.vue'),
    meta: { requiresAuth: true, requiresTeacher: true }
  },
  {
    path: '/students/:id',
    name: 'StudentDetail',
    component: () => import('@/views/students/StudentDetail.vue'),
    meta: { requiresAuth: true, requiresTeacher: true }
  },
  {
    path: '/attendance',
    name: 'Attendance',
    component: () => import('@/views/attendance/AttendanceRecording.vue'),
    meta: { requiresAuth: true, requiresTeacher: true }
  },
  {
    path: '/reports',
    name: 'Reports',
    component: () => import('@/views/attendance/AttendanceReports.vue'),
    meta: { requiresAuth: true, requiresTeacher: true }
  },
  {
    path: '/admin/users',
    name: 'AdminUsers',
    component: () => import('@/views/admin/UserManagement.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/unauthorized',
    name: 'Unauthorized',
    component: () => import('@/views/errors/Unauthorized.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/errors/NotFound.vue'),
    meta: { requiresAuth: false }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Check authentication if we have a token but not authenticated
  if (!authStore.isAuthenticated && authStore.token) {
    const isAuthenticated = await authStore.checkAuth()
    if (!isAuthenticated) {
      next('/login')
      return
    }
  }

  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
    return
  }

  // Check if route is for guests only
  if (to.meta.guestOnly && authStore.isAuthenticated) {
    next('/')
    return
  }

  // Check admin routes
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next('/unauthorized')
    return
  }

  // Check teacher routes
  if (to.meta.requiresTeacher && !authStore.isTeacher && !authStore.isAdmin) {
    next('/unauthorized')
    return
  }

  next()
})

export default router
