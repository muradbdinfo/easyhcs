<script setup>
// AuthLayout: used by /login, /register, /install — no nav, no auth required
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex flex-col">

    <!-- Top bar (logo only) -->
    <header class="flex items-center justify-center py-8">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
          <span class="text-white font-black text-lg">H</span>
        </div>
        <span class="text-2xl font-bold text-gray-800">EasyHCS</span>
      </div>
    </header>

    <!-- Page content (login card, install wizard, etc.) -->
    <main class="flex-1 flex items-start justify-center px-4 pb-12">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="text-center text-xs text-gray-400 py-4">
      © {{ new Date().getFullYear() }} Easy Healthcare Solutions. All rights reserved.
    </footer>
  </div>
</template>