<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    backgroundClass: {
        type: String,
        default: 'bg-default',
    },
    username: {
        type: String,
        required: true,
    }
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
    <div>
        <header class="navbar flex items-center justify-between p-2 ml-auto">  
                <!-- Logo and Title -->
                <div class="flex items-center space-x-2" style="margin-left:5px; font-size:medium;">
                    <img src="../Images/neust-logo.png" alt="logo" class="w-10 h-10" />
                    <Link :href="route('dashboard')">NEUST Gabaldon Library QR-Code Attendance System</Link>
                </div>
         
            <!-- Navigation Links -->
                <div class="flex items-center space-x-6 ml-auto mr-4">
                    <div v-if="$page.props.auth.user" class="relative dropdown-container mr-4">
                        <!-- Profile Button -->
                        <button
                            class="flex items-center space-x-2 bg-transparent text-white rounded-md hover:bg-blue-700 transition relative z-10"
                            @click="isDropdownOpen = !isDropdownOpen"
                            >
                            <!-- <img 
                                :src="$page.props.auth.user.avatar ? ('storage/' + $page.props.auth.user.avatar) : ('storage/avatars/default.jpg')"
                                alt="User Profile"
                                class="w-8 h-8 rounded-full object-cover"
                            /> -->
                            <div class="text-left">
                                <span class="font-semibold text-sm">{{ $page.props.auth.user.name }}</span>
                                <!-- <div class="text-xs text-white">{{ capitalizeWords($page.props.auth.user.role) }}</div> -->
                            </div>
                            <i :class="{'fa-chevron-down': true, 'fa-chevron-up': isDropdownOpen}" class="fas"></i>
                        </button>
                        <!-- Dropdown Menu -->
                        <div
                            v-if="isDropdownOpen"
                            class="absolute right-1 mt-1.5 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-20"
                            >
                            <!-- <Link :href="route('profile.edit')" class="dropdown-item"> Profile Settings</Link> -->
                            <Link href="#" class="dropdown-item"> Profile Settings</Link>
                            <hr>
                            <Link href="/logout" method="post" as="button" class="dropdown-item">Logout</Link>
                        </div>
                    </div>
                </div>
        </header>
        <main>
            <slot />
        </main>
    </div>
</template>

<style scoped>
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
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    background-color: #004aad;
    color: white;
    padding-top: 1px;
    padding-bottom: 1px;
    padding: 4px 0;
    font-size: 12px; 
    display: flex;
    justify-content: space-between;
    align-items: center;
    /* z-index: 1000; */
}
</style>

