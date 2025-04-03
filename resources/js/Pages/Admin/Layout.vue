<template>
    <div>
      <AdminNavbar />
      <slot />
    </div>
  </template>
  
  <script setup>
  import AdminNavbar from '@/Components/Admin/Navbar.vue'
  </script>