<script setup>
import FileIcon from "./FileIcon.vue";
import UploadItem from "./UploadItem.vue";
import { ref, onMounted } from "vue";
import axios from "axios";
import { createUpload } from '@mux/upchunk';
import { router, usePage } from '@inertiajs/vue3';

const uploads = ref([]);
const dropping = ref(false);

onMounted(() => {
    Echo.private(`users.${usePage().props.auth.user.id}`)
        .listen("VideoEncodingProgress", (e) => {
            const upload = getUploadById(e.video_id);

            if (!upload) {
                return;
            }
            upload.encoding = true;
            upload.encodingProgress = e.percentage;
            console.log("Updated upload:", upload);
        });
});

const handleUploadedFiles = (files) => {
    Array.from(files).forEach((file) => {
        axios.post(route('videos.store'), {
            title: file.name
        }).then((response) => {
            uploads.value.unshift({
                id: response.data.id,
                title: file.name,
                file: startChunkedUpload(file, response.data.id),
                uploading: true,
                progress: 0,
                encoding: false,
                encodingProgress: 0
            })
        });
    });
}

const getUploadById = (id) => {
    return uploads.value.find((upload) => upload.id === id);
}

const cancelUpload = (id) => {
    getUploadById(id).file.abort();
    router.delete(route('videos.destroy', id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            uploads.value = uploads.value.filter((upload) => upload.id !== id);
        }
    });
}

const startChunkedUpload = (file, id) => {
    const upload = createUpload({
        endpoint: route("videos.upload", id),
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        method: 'POST',
        file: file,
        chunkSize: 256 * 1024, // 256 KB (262144 bytes)
    });

    upload.on('error', (error) => {
        console.error('Error:', error);
    });
    upload.on('progress', (progress) => {
        getUploadById(id).progress = progress.detail;
    });
    upload.on('success', (success) => {
        getUploadById(id).uploading = false;
    });

    return upload;
};
</script>

<template>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div v-on:dragover.prevent="dropping = true" v-on:dragleave.prevent="dropping = false"
                        v-on:drop.prevent="
                            handleUploadedFiles($event.dataTransfer.files);
                            dropping = false;
                        "
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md border-gray-300"
                        :class="{ 'border-indigo-600': dropping }">
                        <div class="space-y-1 text-center">
                            <FileIcon />
                            <div class="flex text-sm text-gray-600">
                                <label for="file-upload"
                                    class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload a file</span>
                                    <input
                                        @change="handleUploadedFiles($event.target.files)"
                                        id="file-upload" name="file-upload" type="file" class="sr-only" multiple />
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                MP4, AVI up to 100MB
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-3" v-for="upload in uploads" :key="upload.id">
                <UploadItem 
                    v-on:cancel="cancelUpload" 
                    v-on:resume="resumeUpload" 
                    v-on:pause="pauseUpload"
                    :upload="upload" 
                />
            </div>
        </div>
    </div>
</template>