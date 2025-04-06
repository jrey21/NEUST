<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    backgroundClass: {
        type: String,
        default: 'bg-default',
    },
});

function capitalizeFirstLetter(word) {
    if (!word) return '';
    return word.charAt(0).toUpperCase() + word.slice(1);
}
</script>

<template>
    <header class="header">
        <nav class="flex justify-between">
            <div class=" flex justify-between ml-4">
                <div>
                    <img src="../Images/neust-logo.png" alt="NEUST Logo" class="w-10 h-10" />
                </div>
                <div class="flex items-center space-x-2 text-large">
                    <Link :href="route('dashboard')" class="dropdown-item">Dashboard</Link>
                </div>
            </div>
            <div class="flex items-center space-x-2 mr-4">
                {{ capitalizeFirstLetter($page.props.auth.user.role) }}
            </div>
        </nav>
    </header>    
</template>

<style scoped>
.header {
    background-color: #004aad;
    color: white;
    text-align: right;
    padding: 10px 2% 10px 0;  
}

.header a {
    color: white;
    text-decoration: none;
}

.header a:hover {
    /* text-decoration: underline; */
    color: #ffcc00;
}
</style>
