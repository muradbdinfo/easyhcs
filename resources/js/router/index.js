import { createRouter, createWebHistory } from 'vue-router'

// ── Layouts ──────────────────────────────────────────────────────────────────
import AuthLayout   from '@/layouts/AuthLayout.vue'
import AdminLayout  from '@/layouts/AdminLayout.vue'
import TenantLayout from '@/layouts/TenantLayout.vue'

// ── Auth pages ────────────────────────────────────────────────────────────────
const Login              = () => import('@/Pages/auth/Login.vue')
const Register           = () => import('@/Pages/auth/Register.vue')
const ForgotPassword     = () => import('@/Pages/auth/ForgotPassword.vue')
const ResetPassword      = () => import('@/Pages/auth/ResetPassword.vue')
const VerifyEmail        = () => import('@/Pages/auth/VerifyEmail.vue')
const TwoFactorChallenge = () => import('@/Pages/auth/TwoFactorChallenge.vue')

// ── Install ───────────────────────────────────────────────────────────────────
const InstallWizard = () => import('@/Pages/install/InstallWizard.vue')

// ── Admin pages ───────────────────────────────────────────────────────────────
const AdminDashboard      = () => import('@/Pages/admin/Dashboard.vue')
const AdminTenants        = () => import('@/Pages/admin/Tenants.vue')
const AdminCreateTenant   = () => import('@/Pages/admin/CreateTenant.vue')
const AdminPlans          = () => import('@/Pages/admin/Plans.vue')
const AdminSubscriptions  = () => import('@/Pages/admin/Subscriptions.vue')
const AdminTransactions   = () => import('@/Pages/admin/Transactions.vue')
const AdminRefunds        = () => import('@/Pages/admin/Refunds.vue')
const AdminGateways       = () => import('@/Pages/admin/Gateways.vue')
const AdminLicenses       = () => import('@/Pages/admin/Licenses.vue')
const AdminSettings       = () => import('@/Pages/admin/Settings.vue')
const AdminUsers          = () => import('@/Pages/admin/AdminUsers.vue')
const AdminAuditLog       = () => import('@/Pages/admin/AuditLog.vue')
const AdminHealth         = () => import('@/Pages/admin/Health.vue')

// ── Tenant — Core ─────────────────────────────────────────────────────────────
const TenantDashboard     = () => import('@/Pages/tenant/Dashboard.vue')
const TenantPatients      = () => import('@/Pages/tenant/core/Patients.vue')
const TenantUsers         = () => import('@/Pages/tenant/core/Users.vue')
const TenantSettings      = () => import('@/Pages/tenant/core/Settings.vue')
const TenantAuditLog      = () => import('@/Pages/tenant/core/AuditLog.vue')
const TenantBilling       = () => import('@/Pages/tenant/core/Billing.vue')
const TenantNotifications = () => import('@/Pages/tenant/core/Notifications.vue')

// ── Tenant — Pharmacy ─────────────────────────────────────────────────────────
const PharmacyPOS            = () => import('@/Pages/tenant/pharmacy/POS.vue')
const PharmacyMedicines      = () => import('@/Pages/tenant/pharmacy/Medicines.vue')
const PharmacyPurchaseReqs   = () => import('@/Pages/tenant/pharmacy/PurchaseRequests.vue')
const PharmacyPurchaseOrders = () => import('@/Pages/tenant/pharmacy/PurchaseOrders.vue')
const PharmacyStockAlerts    = () => import('@/Pages/tenant/pharmacy/StockAlerts.vue')
const PharmacySuppliers      = () => import('@/Pages/tenant/pharmacy/Suppliers.vue')

// ── Tenant — Diagnostic ───────────────────────────────────────────────────────
const DiagTestOrders  = () => import('@/Pages/tenant/diagnostic/TestOrders.vue')
const DiagTestCatalog = () => import('@/Pages/tenant/diagnostic/TestCatalog.vue')
const DiagSamples     = () => import('@/Pages/tenant/diagnostic/SampleCollection.vue')
const DiagResults     = () => import('@/Pages/tenant/diagnostic/Results.vue')

// ── Tenant — Hospital ─────────────────────────────────────────────────────────
const HospAppointments  = () => import('@/Pages/tenant/hospital/Appointments.vue')
const HospQueue         = () => import('@/Pages/tenant/hospital/Queue.vue')
const HospPatients      = () => import('@/Pages/tenant/hospital/Patients.vue')
const HospAdmissions    = () => import('@/Pages/tenant/hospital/Admissions.vue')
const HospWards         = () => import('@/Pages/tenant/hospital/Wards.vue')
const HospOT            = () => import('@/Pages/tenant/hospital/OT.vue')
const HospDoctors       = () => import('@/Pages/tenant/hospital/Doctors.vue')
const HospEMR           = () => import('@/Pages/tenant/hospital/EMR.vue')
const HospPrescriptions = () => import('@/Pages/tenant/hospital/Prescriptions.vue')

// ── Tenant — Accounts ─────────────────────────────────────────────────────────
const AccDashboard = () => import('@/Pages/tenant/accounts/Accounts.vue')
const AccInvoices  = () => import('@/Pages/tenant/accounts/Invoices.vue')
const AccInsurance = () => import('@/Pages/tenant/accounts/InsuranceClaims.vue')
const AccReports   = () => import('@/Pages/tenant/accounts/Reports.vue')

// ── Error pages ───────────────────────────────────────────────────────────────
const Unauthorized = () => import('@/Pages/errors/Unauthorized.vue')
const ModuleLocked = () => import('@/Pages/errors/ModuleLocked.vue')
const NotFound     = () => import('@/Pages/errors/NotFound.vue')

// ─────────────────────────────────────────────────────────────────────────────
// ROUTES
// ─────────────────────────────────────────────────────────────────────────────
const routes = [

  // ── GROUP 1: Auth — wrapped in AuthLayout ─────────────────────────────────
  {
    path      : '/',
    component : AuthLayout,
    children  : [
      { path: 'login',               name: 'login',           component: Login,              meta: { guest: true } },
      { path: 'register',            name: 'register',        component: Register,           meta: { guest: true } },
      { path: 'forgot-password',     name: 'forgot-password', component: ForgotPassword,     meta: { guest: true } },
      { path: 'reset-password/:token', name: 'reset-password', component: ResetPassword,     meta: { guest: true } },
      { path: 'verify-email',        name: 'verify-email',    component: VerifyEmail,        meta: { requiresAuth: true } },
      { path: 'two-factor-challenge',name: 'two-factor-challenge', component: TwoFactorChallenge, meta: { requiresAuth: true } },
    ],
  },

  // ── GROUP 2: Install — wrapped in AuthLayout ──────────────────────────────
  {
    path      : '/',
    component : AuthLayout,
    children  : [
      { path: 'install', name: 'install', component: InstallWizard },
    ],
  },

  // ── GROUP 3: SuperAdmin — AdminLayout ─────────────────────────────────────
  {
    path      : '/admin',
    component : AdminLayout,
    meta      : { requiresAuth: true, requiresSuperAdmin: true },
    children  : [
      { path: '',           name: 'admin.dashboard',      component: AdminDashboard },
      { path: 'tenants',    name: 'admin.tenants',        component: AdminTenants },
      { path: 'tenants/create', name: 'admin.tenants.create', component: AdminCreateTenant },
      { path: 'plans',      name: 'admin.plans',          component: AdminPlans },
      { path: 'subscriptions', name: 'admin.subscriptions', component: AdminSubscriptions },
      { path: 'transactions',  name: 'admin.transactions',  component: AdminTransactions },
      { path: 'refunds',    name: 'admin.refunds',        component: AdminRefunds },
      { path: 'gateways',   name: 'admin.gateways',       component: AdminGateways },
      { path: 'licenses',   name: 'admin.licenses',       component: AdminLicenses },
      { path: 'settings',   name: 'admin.settings',       component: AdminSettings },
      { path: 'users',      name: 'admin.users',          component: AdminUsers },
      { path: 'audit-log',  name: 'admin.audit-log',      component: AdminAuditLog },
      { path: 'health',     name: 'admin.health',         component: AdminHealth },
    ],
  },

  // ── GROUP 4: Tenant — TenantLayout ────────────────────────────────────────
  {
    path      : '/',
    component : TenantLayout,
    meta      : { requiresAuth: true, requiresTenant: true },
    children  : [

      // Core — always active
      { path: 'dashboard',     name: 'dashboard',     component: TenantDashboard },
      { path: 'patients',      name: 'patients',      component: TenantPatients,      meta: { permission: 'manage-patients' } },
      { path: 'users',         name: 'users',         component: TenantUsers,         meta: { permission: 'manage-users' } },
      { path: 'settings',      name: 'settings',      component: TenantSettings,      meta: { permission: 'manage-settings' } },
      { path: 'audit-log',     name: 'audit-log',     component: TenantAuditLog,      meta: { permission: 'view-audit-log' } },
      { path: 'billing',       name: 'billing',       component: TenantBilling },
      { path: 'notifications', name: 'notifications', component: TenantNotifications },

      // Pharmacy
      { path: 'pharmacy/pos',              name: 'pharmacy.pos',              component: PharmacyPOS,            meta: { module: 'pharmacy', permission: 'pharmacy-sell' } },
      { path: 'pharmacy/medicines',        name: 'pharmacy.medicines',        component: PharmacyMedicines,      meta: { module: 'pharmacy', permission: 'pharmacy-manage-medicines' } },
      { path: 'pharmacy/purchase-requests',name: 'pharmacy.purchase-requests',component: PharmacyPurchaseReqs,   meta: { module: 'pharmacy', permission: 'pr-view-all' } },
      { path: 'pharmacy/purchase-orders',  name: 'pharmacy.purchase-orders',  component: PharmacyPurchaseOrders, meta: { module: 'pharmacy', permission: 'po-view' } },
      { path: 'pharmacy/stock-alerts',     name: 'pharmacy.stock-alerts',     component: PharmacyStockAlerts,    meta: { module: 'pharmacy' } },
      { path: 'pharmacy/suppliers',        name: 'pharmacy.suppliers',        component: PharmacySuppliers,      meta: { module: 'pharmacy', permission: 'pharmacy-manage-suppliers' } },

      // Diagnostic
      { path: 'diagnostic/test-orders',  name: 'diagnostic.test-orders',  component: DiagTestOrders,  meta: { module: 'diagnostic', permission: 'diagnostic-view-orders' } },
      { path: 'diagnostic/test-catalog', name: 'diagnostic.test-catalog', component: DiagTestCatalog, meta: { module: 'diagnostic', permission: 'diagnostic-manage-tests' } },
      { path: 'diagnostic/samples',      name: 'diagnostic.samples',      component: DiagSamples,     meta: { module: 'diagnostic', permission: 'diagnostic-collect-sample' } },
      { path: 'diagnostic/results',      name: 'diagnostic.results',      component: DiagResults,     meta: { module: 'diagnostic', permission: 'diagnostic-enter-result' } },

      // Hospital
      { path: 'hospital/appointments',  name: 'hospital.appointments',  component: HospAppointments,  meta: { module: 'hospital', permission: 'hospital-appointments' } },
      { path: 'hospital/queue',         name: 'hospital.queue',         component: HospQueue,         meta: { module: 'hospital', permission: 'hospital-appointments' } },
      { path: 'hospital/patients',      name: 'hospital.patients',      component: HospPatients,      meta: { module: 'hospital', permission: 'manage-patients' } },
      { path: 'hospital/admissions',    name: 'hospital.admissions',    component: HospAdmissions,    meta: { module: 'hospital', permission: 'hospital-admissions' } },
      { path: 'hospital/wards',         name: 'hospital.wards',         component: HospWards,         meta: { module: 'hospital', permission: 'hospital-ward-management' } },
      { path: 'hospital/ot',            name: 'hospital.ot',            component: HospOT,            meta: { module: 'hospital', permission: 'hospital-ot' } },
      { path: 'hospital/doctors',       name: 'hospital.doctors',       component: HospDoctors,       meta: { module: 'hospital', permission: 'hospital-manage-doctors' } },
      { path: 'hospital/emr',           name: 'hospital.emr',           component: HospEMR,           meta: { module: 'hospital', permission: 'hospital-emr' } },
      { path: 'hospital/prescriptions', name: 'hospital.prescriptions', component: HospPrescriptions, meta: { module: 'hospital', permission: 'hospital-prescribe' } },

      // Accounts
      { path: 'accounts',          name: 'accounts',          component: AccDashboard, meta: { module: 'accounts', permission: 'accounts-manage' } },
      { path: 'accounts/invoices', name: 'accounts.invoices', component: AccInvoices,  meta: { module: 'accounts', permission: 'accounts-manage' } },
      { path: 'accounts/insurance',name: 'accounts.insurance',component: AccInsurance, meta: { module: 'accounts', permission: 'hospital-insurance' } },
      { path: 'accounts/reports',  name: 'accounts.reports',  component: AccReports,   meta: { module: 'accounts', permission: 'accounts-view-reports' } },
    ],
  },

  // ── Utility / error pages ─────────────────────────────────────────────────
  { path: '/unauthorized',           name: 'unauthorized',  component: Unauthorized },
  { path: '/module-locked/:module',  name: 'module.locked', component: ModuleLocked },
  { path: '/:pathMatch(.*)*',        name: 'not-found',     component: NotFound },
]

// ─────────────────────────────────────────────────────────────────────────────
// ROUTER INSTANCE
// Guards are attached in app.js via setupGuards(router) — NOT here.
// Keeping guards in one place (guards.js) prevents double-execution.
// ─────────────────────────────────────────────────────────────────────────────
const router = createRouter({
  history      : createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

export default router