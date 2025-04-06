<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import LoginLayout from '@/Layouts/LoginLayout.vue'

defineOptions({
  layout: LoginLayout
})

const form = useForm({
    name: null,
    username: null,
    password: null,
    password_confirmation: null,
    role: null,
    avatar: null,
    preview: null,
});
const change = (e) => {
   form.avatar = e.target.files[0];
    form.preview = URL.createObjectURL(e.target.files[0]);
};

const capitalizeFirstLetterOfEachWord = (string) => {
    return string.replace(/\b\w/g, char => char.toUpperCase());
};

const submit = () => {
    form.name = capitalizeFirstLetterOfEachWord(form.name);
    form.post(route('register'),
        {
            onError: () => {
                form.reset("password", "password_confirmation");
            }
        });
};
</script>

<template>
    <Head title="| Register" />
    
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="container max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6 text-center">Register</h1>
            <form @submit.prevent="submit">
                <!-- Upload Avatar -->
                <!-- <div class="grid place-items-center" >
                    <div
                        class="relative w-24 h-24 rounded-full overflow-hidden border border-slate-300"
                        >
                        <label for="avatar" class="absolute inset-0 grid content-end cursor-pointer avatar">
                            <span class="bg-white/70 pb-2 text-center">Avatar</span>
                        </label>
                        <input type="file" @change="change" id="avatar" hidden />

                        <img
                            class="object-cover w-24 h-24"
                            :src="form.preview ?? 'storage/avatars/default.jpg'"
                        />
                    </div>

                    <p class="text-xs text-red-500 mb-2">{{ form.errors.avatar }}</p>
                </div> -->
                <!-- End Upload Avatar -->
                 
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        @input="form.name = capitalizeFirstLetterOfEachWord(form.name)"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>
                <div class="mt-4">
                    <InputLabel for="username" value="Username" />
                    <TextInput
                        id="username"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.username"
                        required
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.username" />
                </div>
                <div class="mt-4">
                    <InputLabel for="role" value="Role" />
                    <select
                        id="role"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        v-model="form.role"
                        required
                    >
                        <option value="" disabled>Select a role</option>
                        <option value="admin">Admin</option>
                        <option value="adviser">Adviser</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>
                <div class="mt-4">
                    <InputLabel for="password" value="Password" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>
                <div class="mt-4">
                    <InputLabel
                        for="password_confirmation"
                        value="Confirm Password"
                    />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.password_confirmation"
                    />
                </div>
                <div class="mt-6 flex items-center justify-between">
                    <Link
                        :href="route('login')"
                        class="text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Already registered?
                    </Link>
                    <PrimaryButton
                        class="ml-4"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Register
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.container{
    width: 100%;
    max-width: 400px;
}
</style>