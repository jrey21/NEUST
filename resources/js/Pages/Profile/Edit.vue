<script setup>
import UpdateLayout from '../../Layouts/UpdateLayout.vue';
import UpdateInfo from './Sections/UpdateInfo.vue';
import UpdatePassword from './Sections/UpdatePassword.vue';


defineOptions({ layout: UpdateLayout});

defineProps({
    user: Object,
});

</script>

<template>
    <Head title=" | Profile"></Head>
    <div class="bg-gray-100 mt-0 p-0">
        <UpdateInfo :user="user"/>
        <UpdatePassword :user="user"/>
    </div>
</template>