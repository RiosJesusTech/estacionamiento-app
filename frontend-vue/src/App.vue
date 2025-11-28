<script setup>
import { onMounted } from 'vue';
import { RouterView } from 'vue-router'


const loadTheme = () => {
  const savedTheme = localStorage.getItem('theme');
  
  const themeToApply = savedTheme || 'light';
  
 
  document.body.className = `theme-${themeToApply}`;
};

onMounted(() => {
  loadTheme();
});


</script>

<template>
  <RouterView /> 
</template>

<style scoped>

</style>