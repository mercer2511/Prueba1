<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { badgeVariants } from './index';
import type { BadgeVariants } from './index';

interface Props {
  variant?: BadgeVariants['variant'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  class: '',
});
</script>

<template>
  <div
    data-slot="badge"
    :class="cn(badgeVariants({ variant: props.variant }), props.class)"
  >
    <slot />
  </div>
</template>