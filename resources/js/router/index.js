import AuthLayout    from '@/layouts/AuthLayout.vue'
import InstallWizard from '@/pages/install/InstallWizard.vue'

// Install group — no auth required
{
  path: '/install',
  component: AuthLayout,
  children: [
    {
      path: '',
      name: 'install',
      component: InstallWizard,
    },
  ],
},