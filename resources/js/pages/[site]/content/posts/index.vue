<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center mt-8 mb-6">
        <h1 class="text-2xl font-bold">Posts</h1>
        <RouterLink :to="`/app/${siteId}/content/posts/create`">
          <DuiAction color="primary">
            <i class="mdi mdi-plus"></i>
            Crear nuevo post
          </DuiAction>
        </RouterLink>
      </div>
      <DuiTable
        :rows="posts.data"
        :columns="[
          { name: 'id', label: '#' },
          { name: 'name', label: 'Name' },
          { name: 'category', label: 'Category' },
          { name: 'created_at', label: 'Created At' },
          { name: 'updated_at', label: 'Updated At' },
        ]">
        <template #name="{ slug, name, description }">
          <RouterLink :to="`/app/${siteId}/content/posts/${slug}`" class="text-blue-600 hover:text-blue-800">
            {{ name }}
          </RouterLink>
          <p class="text-gray-500 text-sm">
            {{ description }}
          </p>
        </template>
        <template #category="{ category }">
          <p class="text-gray-500 text-sm">
            {{ category.name }}
          </p>
        </template>
        <template #created_at="{ created_at }">
          <p class="text-gray-500 text-sm">
            {{ new Date(created_at).toLocaleString() }}
          </p>
        </template>
        <template #updated_at="{ updated_at }">
          <p class="text-gray-500 text-sm">
            {{ new Date(updated_at).toLocaleString() }}
          </p>
        </template>
      </DuiTable>
    </div>
  </div>
</template>

<script setup lang="ts">
import AdminMenu from '@/components/AdminMenu.vue';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import appi from '@/utils/appi';
import { DuiTable, DuiAction } from '@dronico/droni-kit';

const route = useRoute();
const siteId = route.params['site'] as string;

const posts = ref<Pagination<Post[]>>({
  data: [],
});
const getPosts = async () => {
  const response = await appi.get<Pagination<Post[]>>(`/content/posts`, {
    headers: {
      site: siteId,
    },
  });
  posts.value = response.data;
};

onMounted(() => {
  getPosts();
});

</script>
