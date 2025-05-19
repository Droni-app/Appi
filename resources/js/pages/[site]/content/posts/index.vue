<template>
  <div>
    <AdminMenu :siteId="siteId" />
    <div class="container mx-auto px-4">
      <h1>Post</h1>
      <DuiTable
        :rows="posts.data"
        :columns="[
          { name: 'id', label: '#' },
          { name: 'name', label: 'Name' },
          { name: 'Created At', label: 'created_at' },
          { name: 'Updated At', label: 'updated_at' },
        ]">
        <template #name="{ slug, name, description }">
          <RouterLink :to="`/app/${siteId}/content/posts/${slug}`" class="text-blue-600 hover:text-blue-800">
            {{ name }}
          </RouterLink>
          <p class="text-gray-500 text-sm">
            {{ description }}
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
import { DuiTable } from '@dronico/droni-kit';

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
