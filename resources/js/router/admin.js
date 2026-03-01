// resources/js/router/admin.js  (import into main router)
export const adminRoutes = [
  {
    path: '/admin',
    component: () => import('@/Layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresSuperAdmin: true },
    children: [
      { path: '',             name: 'admin.dashboard',         component: () => import('@/Pages/Admin/Dashboard.vue') },
      { path: 'tenants',     name: 'admin.tenants.index',     component: () => import('@/Pages/Admin/Tenants/Index.vue') },
      { path: 'tenants/create', name: 'admin.tenants.create', component: () => import('@/Pages/Admin/Tenants/Create.vue') },
      { path: 'tenants/:id', name: 'admin.tenants.show',      component: () => import('@/Pages/Admin/Tenants/Show.vue') },
      { path: 'plans',       name: 'admin.plans.index',       component: () => import('@/Pages/Admin/Plans/Index.vue') },
      { path: 'subscriptions', name: 'admin.subscriptions.index', component: () => import('@/Pages/Admin/Subscriptions/Index.vue') },
      { path: 'subscriptions/:id', name: 'admin.subscriptions.show', component: () => import('@/Pages/Admin/Subscriptions/Show.vue') },
      { path: 'transactions', name: 'admin.transactions.index', component: () => import('@/Pages/Admin/Transactions/Index.vue') },
      { path: 'gateways',    name: 'admin.gateways.index',    component: () => import('@/Pages/Admin/Gateways/Index.vue') },
      { path: 'licenses',    name: 'admin.licenses.index',    component: () => import('@/Pages/Admin/Licenses/Index.vue') },
      { path: 'settings',    name: 'admin.settings.index',    component: () => import('@/Pages/Admin/Settings/Index.vue') },
      { path: 'admin-users', name: 'admin.admin-users.index', component: () => import('@/Pages/Admin/AdminUsers/Index.vue') },
      { path: 'audit-log',   name: 'admin.audit-log.index',   component: () => import('@/Pages/Admin/AuditLog/Index.vue') },
      { path: 'health',      name: 'admin.health.index',      component: () => import('@/Pages/Admin/Health/Index.vue') },
    ]
  }
]
