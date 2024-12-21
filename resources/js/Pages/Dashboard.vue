<script setup>
import Uploader from '@/Components/Uploader.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

// onMounted(() => {
//     Echo.channel('videos').listen("Test", (e) =>{
//         console.log(e);
//     })
// });
</script>

<template>
    <Head title="Dashboard" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>
        <Uploader />
    </AuthenticatedLayout>
</template>
