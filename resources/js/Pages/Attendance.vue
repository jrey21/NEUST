<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

defineOptions({ layout: DashboardLayout });

const scannedCodes = ref([]);
const selectedDate = ref(new Date().toISOString().split('T')[0]); // Default to current date
const searchQuery = ref(''); // New search query for filtering by name
const filteredCodes = computed(() => {
    let codes = scannedCodes.value;
    if (selectedDate.value) {
        codes = codes.filter(code => code.date === selectedDate.value);
    }
    if (searchQuery.value) {
        codes = codes.filter(code => code.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
    }
    return codes;
});

onMounted(async () => {
    try {
        const response = await axios.get('/attendance/scanned-codes-data');
        scannedCodes.value = response.data.attendances;
    } catch (error) {
        console.error('Error fetching scanned codes:', error);
    }
});

// Helper function to format time to 12-hour format with AM/PM
function formatTimeTo12Hour(time) {
    if (!time) return '-';
    const [hour, minute] = time.split(':');
    const h = parseInt(hour, 10);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const formattedHour = h % 12 || 12; // Convert 0 to 12 for 12-hour format
    return `${formattedHour}:${minute} ${ampm}`;
}

// Helper function to format date to "Month Day, Year"
function formatDateToMDY(date) {
    if (!date) return '-';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString(undefined, options);
}

const currentPage = ref(1);
const itemsPerPage = 20;

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredCodes.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredCodes.value.length / itemsPerPage);
});

const changePage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};
</script>
<template>
    <Head title="| Attendance" />
    <div class="header">
        <h2 class="text-xl font-semibold text-gray-700 mb-4 ml-7 mt-2">Library Attendance Sheet</h2>
    </div>
    <div class="container">
        <div class="filters">
            <div class="search-box">
                <i class="fas fa-search ml-3"></i>
                <input type="text" v-model="searchQuery" placeholder="Search by name ..." />
            </div>
            <div>
                <label for="date-filter" class="text-date">Select Date: </label>
                <input type="date" id="date-filter" v-model="selectedDate" class="display-date"/>
                <!-- <span class="formatted-date">{{ formatDateToMDY(selectedDate) }}</span> -->
            </div>
        </div>
        <table class="students-table">
            <thead>
                <tr>
                    <th style="width:5%;">No.</th>
                    <th>Name</th>
                    <th>Year</th>
                    <th>Course</th>
                    <th style="width:15%;">Time In</th>
                    <th style="width:15%;">Time Out</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="paginatedData.length === 0">
                    <td colspan="6">No records found</td>
                </tr>
                <tr v-for="(code, index) in paginatedData" :key="code.id">
                    <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                    <td>{{ code.name }}</td>
                    <td>{{ code.year }}</td>
                    <td>{{ code.course === 'None' ? '-' : code.course }}</td> 
                    <td>{{ formatTimeTo12Hour(code.time_in) }}</td>
                    <td>{{ formatTimeTo12Hour(code.time_out) }}</td>
                </tr>
            </tbody>
        </table>
        <div class="pagination">
            <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1">
                <i class="fas fa-chevron-left"></i>
            </button>
            <span>Page {{ currentPage }} of {{ totalPages }}</span>
            <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</template>

<style scoped>
.container {
    max-width: 1240px;
    margin: auto auto auto 2%;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    color: #333;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}

.filters {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    color: #333;
    justify-content: space-between; /* Align search bar to the left and date filter to the right */
    width: 100%;
}
.text-date {
    font-size: 16px;
    color: #333;
    font-weight: 500;
}
.display-date {
    padding: 5px;
    border: 2px solid #9dc0e6;
    border-radius: 10px;
    background-color: #fff;
    box-shadow: 0 4px 4px rgba(0, 0, 0, 0.1);
    height: 34px;
    width: 150px;
}

.search-box {
    display: flex;
    align-items: center;
    width: 280px;
    padding: 2px;
    border: 2px solid #9dc0e6;
    border-radius: 50px;
    background-color: #fff;
    box-shadow: 0 4px 4px rgba(0, 0, 0, 0.1);
    height: 34px;
}

.search-box i {
    font-size: 14px;
    color: #007bff;
    margin-right: 8px;
}

.search-box input {
    font-size: 14px;
    border: none;
    outline: none;
    color: #333;
    height: 20px;
    width: 80%;
}

.search-box input:focus {
    outline: none; 
    box-shadow: none; 
}

.students-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
    table-layout: fixed;
}

.students-table th, .students-table td {
    border: 1px solid #ddd;
    padding: 8px 15px;
    text-align: center;
}

.students-table thead th {
    background-color: #007bff;
    color: white;
    font-weight: bold;
    text-transform: uppercase;
    cursor: pointer;
    z-index: 1;
    position: sticky;
    top: 0;
}

.students-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.students-table tr:hover {
    background-color: #f1f1f1;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
}

.pagination button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 2px 8px;
    margin: 0 10px;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.pagination button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.pagination span {
    margin: 0 10px;
    font-size: 12px;
}

.pagination button i {
    font-size: 14px;
}
</style>