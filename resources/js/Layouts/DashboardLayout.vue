<script setup>
import { ref, onMounted, onBeforeUnmount} from 'vue';
import Sidebar from '@/Layouts/Sidebar.vue';

const props = defineProps({
    backgroundClass: {
        type: String,
        default: 'bg-default',
    },
});
const isDropdownOpen = ref(false);

// Function to handle click outside of dropdown
const handleClickOutside = (event) => {
    if (!event.target.closest('.dropdown-container')) {
        isDropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

const capitalizeWords = (str) => {
    return str.replace(/\b\w/g, char => char.toUpperCase());
};

</script>

<template>
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="navbar flex items-center justify-between p-2 w-full">
        <!-- Logo and Title -->
        <div class="flex items-center space-x-2 ml-2 text-sm">
            <img src="../Images/neust-logo.png" alt="logo" class="w-10 h-10" />
            <Link :href="route('dashboard')" class="font-bold text-[16px]">
            NEUST Gabaldon Library Attendance System
            </Link>
        </div>

        <!-- User Dropdown -->
        <div v-if="$page.props.auth.user" class="relative dropdown-container mr-4">
            <button
            class="flex items-center space-x-2 bg-transparent text-white rounded-md hover:bg-blue-700 transition relative z-10"
            @click="isDropdownOpen = !isDropdownOpen"
            >
            <div class="text-left">
                <span class="font-semibold text-sm">{{ capitalizeWords($page.props.auth.user.name) }}</span>
            </div>
            <i :class="{ 'fa-chevron-down': true, 'fa-chevron-up': isDropdownOpen }" class="fas"></i>
            </button>
            <div
            v-if="isDropdownOpen"
            class="absolute right-1 mt-1.5 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-20"
            >
            <Link :href="route('profile.edit')" class="dropdown-item">Profile Settings</Link>
            <hr />
            <Link href="/logout" method="post" as="button" class="dropdown-item">Logout</Link>
            </div>
        </div>
        </header>

        <!-- Body: Sidebar + Main Content -->
        <div class="flex flex-1">
        <!-- Sidebar -->
        <Sidebar class="fixed-sidebar" />

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-auto ml-64">
            <slot />
        </main>
        </div>
    </div>
</template>
  
  
<style scoped>
.fixed-sidebar {
  position: fixed;
  left: 0;
  height: 100%;
  width: 16rem; 
  /* background-color: #fff;  */
  z-index: 1000;
}

.main-content {
  margin-left: 16rem;
}

.dropdown-item {
  display: block;
  padding: 0.75rem 1rem;
  color: #1d4ed8;
  font-size: 0.875rem;
  text-decoration: none;
  width: 100%;
  text-align: left;
  transition: background-color 0.2s ease;
  z-index: 20;
}

.dropdown-item:hover {
  background-color: #f3f4f6;
}

.dropdown-item:active {
  background-color: #e5e7eb;
}

.navbar {
  background-color: #004aad;
  color: white;
  font-size: 12px;
  z-index: 1000;
  padding: 4px 0;
  position: sticky;
  top: 0;
  width: 100%;
}
</style>


