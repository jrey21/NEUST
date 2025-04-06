<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3'; 

const activeLink = ref(null);

const setActiveLink = (event) => {
    const link = event.target.closest('.menu');
    if (activeLink.value) {
        activeLink.value.classList.remove('active');
    }
    activeLink.value = link;
    activeLink.value.classList.add('active');
};
</script>

<template>
  <div class="sidebar">
    <ul>
        
        <li>
            <Link :href="route('dashboard')" class="menu" :class="{ active: route().current('dashboard') }" @click="setActiveLink($event)">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368" class="mr-2">
                    <path d="M520-600v-240h320v240H520ZM120-440v-400h320v400H120Zm400 320v-400h320v400H520Zm-400 0v-240h320v240H120Zm80-400h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z"/>
                </svg>
                Dashboard
            </Link>
        </li>

        <h3 class="categories">Manage</h3>
        <hr>
        <li>
            <Link :href="route('students')" class="menu" :class="{ active: route().current('students') }" @click="setActiveLink($event)">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#5f6368"  class="mr-2">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                Students
            </Link>
        </li>
        <li v-if="$page.props.auth.user.role === 'admin'">
            <Link :href="route('attendance')" class="menu" :class="{ active: route().current('attendance') }" @click="setActiveLink($event)">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#5f6368" class="mr-2">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 2h6v2h-6V5zm0 4h6v2h-6V9zm0 4h6v2h-6v-2zm-8-8h6v14H4V5z"/>
                </svg>
                Attendance
            </Link>
        </li>
        <h3 v-if="$page.props.auth.user.role === 'admin'"   class="categories">Record</h3>
        <hr v-if="$page.props.auth.user.role === 'admin'" >
        <li  v-if="$page.props.auth.user.role === 'admin'" >
            <Link :href="route('home')" class="menu" :class="{ active: route().current('home') }" @click="setActiveLink($event)">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#5f6368" class="mr-2">
                    <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm6 6h4v1h-4v-1zm0 2h4v1h-4v-1zm0 2h4v1h-4v-1zm2-10h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm10 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2z"/>
                </svg>
                Scan Attendance
            </Link>
        </li>
        <h3 v-if="$page.props.auth.user.role === 'admin'" class="categories">User Administration</h3>
        <hr  v-if="$page.props.auth.user.role === 'admin'" >
        <li  v-if="$page.props.auth.user.role === 'admin'" >
            <Link :href="route('confirm-users')" class="menu" :class="{ active: route().current('confirm-users') }" @click="setActiveLink($event)">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#5f6368" class="mr-2">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
               Confirm Users
            </Link>
        </li>
        <li  v-if="$page.props.auth.user.role === 'admin'" >
            <Link :href="route('user-management')" class="menu" :class="{ active: route().current('user-management') }" @click="setActiveLink($event)">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#5f6368" class="mr-2">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 2.02 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                </svg>
               User Management
            </Link>
        </li>
    </ul>
  </div>
</template>



<style scoped>
#sidebar {
    position: fixed;
    padding: 1% 0;
    width: 250px;
    font-size: 15px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    height: 100%;
    color: #333;
    overflow: hidden; 
    background-color: hwb(206 89% 5%);
    transition: transform 0.3s ease-in-out;
}

.sidebar-content {
    overflow-y: auto; 
    overflow-x: hidden; 
    height: calc(100vh - 100px); 
}

#sidebar.close {
    transform: translateX(-100%);
}

@media (max-width: 768px) {
    #sidebar {
        transform: translateX(-100%);
    }
    #sidebar.open {
        transform: translateX(0);
    }
    #mobile-toggle-btn {
        position: fixed;
        top: 10px;
        left: 10px;
        background: none;
        border: none;
        cursor: pointer;
        z-index: 1000;
    }
}

li.active {
    background-color: white;
    border-left: 4px solid #007bff;
    color: #007bff;
}

.menu.active {
    background-color: white;
    border-left: 4px solid #007bff;
    color: #007bff;
}

.categories {
    font-size: 15px;
    font-weight: bold;
    color: #0a407a;
    margin-top: 30px;
    margin-left: 5px;
    margin-bottom: 5px;
}

hr {
    border: 1px solid; 
    opacity: 10%;
}

#sidebar ul {
    list-style-type: none;
    padding: 0;
}

#sidebar ul li {
    align-items: center;
    padding: 10px;
}

#sidebar ul li a {
    text-decoration: none;
    display: flex;
    align-items: center;
    padding: 1px 0;
    gap: 10px; /* Alternative to margin-right for consistent spacing */
}

#sidebar ul li a svg {
    margin-right: 10px; /* Adjust this value as needed */
    flex-shrink: 0; /* Prevent the SVG from shrinking */
}

#sidebar ul li a:hover {
    @apply bg-blue-400 text-white;
}

#sidebar a.menu:hover svg {
    fill: white;
    @apply bg-blue-400 text-white;
}

.menu {
    padding: 0;
    margin: 0;
    display: flex;
    align-items: center;
}

.dropdown-btn {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    white-space: nowrap; 
}

.dropdown-btn svg:first-child {
    order: 1;
}

.dropdown-btn span {
    order: 2;
    flex-grow: 1;
    text-align: left;
}

.dropdown-btn:hover,
.dropdown-btn:hover svg {
    fill: white;
    @apply bg-blue-400 text-white;
}

.dropdown-btn svg:last-child {
    order: 3;
    transform: rotate(0);
}

.dropdown-btn.active svg:last-child {
    transform: rotate(180deg);
}

.sub-menu.show {
    max-height: 500px; 
    opacity: 1;
}

.arrow-down {
    margin-left: 50px;
}

.arrow-down2 {
    margin-left: 35px;
}

.arrow-down3 {
    margin-left: 39px;
}

.sub-menu {
    margin-left: 11%;
}

#mobile-toggle-btn {
    position: fixed;
    top: 10px;
    left: 10px;
    background: none;
    border: none;
    cursor: pointer;
    z-index: 1000;
}

.sidebar {
    width: 250px;
    height: 100%;
    min-height: 100vh;
    background-color: #f8f9fa;
    padding: 15px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    overflow-y: auto; 
    overflow-x: hidden; 
    height: calc(100vh - 100px);
}

.sidebar ul {
    list-style-type: none;
    padding: 0;
}

.sidebar ul li {
    margin: 15px 0;
}

.sidebar ul li a {
    text-decoration: none;
    color: #333;
    font-weight: bold;
}

.sidebar ul li a:hover {
    color: #007bff;
}
</style>
