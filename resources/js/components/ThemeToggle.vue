<script setup lang="ts">
import { Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';
import { computed } from 'vue';

const { appearance, updateAppearance } = useAppearance();

const isDarkMode = computed(() => {
  if (appearance.value === 'system') {
    // Check system preference when set to "system"
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
  }
  return appearance.value === 'dark';
});

function toggleTheme() {
  // If we're in system mode or light mode, switch to dark mode
  // If we're in dark mode, switch to light mode
  if (isDarkMode.value) {
    updateAppearance('light');
  } else {
    updateAppearance('dark');
  }
}
</script>

<template>
  <button
    @click="toggleTheme"
    class="flex h-9 w-9 items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground"
    aria-label="Toggle theme"
    title="Toggle between light and dark mode"
  >
    <Sun v-if="!isDarkMode" class="h-4 w-4 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
    <Moon v-if="isDarkMode" class="h-4 w-4 transition-all" />
    <span class="sr-only">Toggle theme</span>
  </button>
</template>
