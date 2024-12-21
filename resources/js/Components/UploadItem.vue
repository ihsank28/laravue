<script setup>
    import InputError from "./InputError.vue";
    import InputLabel from "./InputLabel.vue";
    import PrimaryButton from "./PrimaryButton.vue";
    import TextArea from "./TextArea.vue";
    import TextInput from "./TextInput.vue";
    import {
        useForm
    } from "@inertiajs/vue3";

    const emit = defineEmits();

    const props = defineProps({
        upload: Object,
    });

    const form = useForm({
        title: props.upload.title,
        description: "",
    });

    const update = () => {
        form.put(route("videos.update", props.upload.id), {
            preserveScroll: true,
            preserveState: true,
        });
    };
</script>

<template>
    <div v-if="upload.encoding" class="mt-2">
        Encoding Progress: {{ upload . encodingProgress }}%
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 border-gray-900">
            <div class="flex space-x-6">
                <div class="max-w-[240px] w-full space-y-3">
                    <div class="space-y-1" v-if="upload.uploading">
                        <div class="bg-gray-100 shadow-inner h-3 rounded overflow-hidden">
                            <div class="h-full bg-indigo-600 rounded-full"
                                v-bind:style="{ width: `${upload.progress}%` }"></div>
                        </div>
                        <div class="text-sm">Uploading</div>
                    </div>
                    <div class="space-y-1" v-if="upload.encoding">
                        <div class="bg-gray-100 shadow-inner h-3 rounded overflow-hidden">
                            <div class="bg-green-500 h-full rounded-full"
                                v-bind:style="{
                                    width: `${upload.encodingProgress}%`,
                                }">
                            </div>
                        </div>
                        <div class="text-sm">Encoding</div>
                    </div>

                    <div class="flex items-center space-x-3" v-if="upload.uploading">
                        <button v-if="!upload.file.paused" @click="emit('pause', upload.id)"
                            class="text-blue-500 text-sm font-medium">
                            Pause
                        </button>
                        <button v-if="upload.file.paused" @click="emit('resume', upload.id)"
                            class="text-blue-500 text-sm font-medium">
                            Resume
                        </button>
                        <button @click="emit('cancel', upload.id)" v-if="upload.uploading"
                            class="text-blue-500 text-sm font-medium">
                            Cancel upload
                        </button>
                    </div>
                </div>

                <form @submit.prevent="update" class="flex-grow space-y-6">
                    <div>
                        <InputLabel for="title" value="Title" />
                        <TextInput v-model="form.title" id="title" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div>
                        <InputLabel for="description" value="Description" />
                        <TextArea v-model="form.description" id="description" class="mt-1 block w-full" />
                        <InputError :message="form.errors.description" />
                    </div>

                    <!-- TextArea -->

                    <div class="flex items-center space-x-3">
                        <PrimaryButton> Save </PrimaryButton>
                        <div v-if="form.recentlySuccessful" class="text-sm">
                            Video updated
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
