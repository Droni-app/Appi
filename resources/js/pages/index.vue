<template>
  <div>
    <h1>Home Page</h1>
    <p>{{ user() }}</p>
  </div>
</template>
<script setup lang="ts">
import { user } from '../utils/auth'
</script>
