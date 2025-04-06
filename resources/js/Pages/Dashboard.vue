<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ref, onMounted, computed, watchEffect, onUnmounted } from 'vue';
import axios from 'axios';
import RightSidebar from '@/Layouts/RightSidebar.vue'
import { Bar, Pie } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
} from 'chart.js'

defineOptions({ layout: DashboardLayout })

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement)

const attendanceData = ref({
  labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
  datasets: [
    {
      label: "College",
      data: [3, 8, 14, 1, 7, 2, 10], //  Sample data
      backgroundColor: "#3498db",
    },
    {
      label: "JHS",
      data: [7, 8, 5, 9, 10, 20, 16], // Sample data
      backgroundColor: "#ffa500",
    },
  ],
});

const fetchAttendanceData = async () => {
  try {
    const response = await axios.get("/attendance/weekly-attendance");
    const data = response.data;

    console.log("Fetched Data:", data); // Debugging log

    const fullToShortDayMap = {
      Monday: "Mon",
      Tuesday: "Tue",
      Wednesday: "Wed",
      Thursday: "Thu",
      Friday: "Fri",
      Saturday: "Sat",
      Sunday: "Sun",
    };

    // Ensure labels are mapped correctly
    attendanceData.value.labels = Object.values(fullToShortDayMap);

    // Map API data to chart format
    attendanceData.value.datasets[0].data = attendanceData.value.labels.map(
      shortDay => {
        const fullDay = Object.keys(fullToShortDayMap).find(fullDay => fullToShortDayMap[fullDay] === shortDay);
        return data[fullDay]?.College || 0;
      }
    );

    attendanceData.value.datasets[1].data = attendanceData.value.labels.map(
      shortDay => {
        const fullDay = Object.keys(fullToShortDayMap).find(fullDay => fullToShortDayMap[fullDay] === shortDay);
        return data[fullDay]?.JHS || 0;
      }
    );

    // Trigger reactivity
    attendanceData.value = { ...attendanceData.value };

    console.log("Updated Chart Data:", attendanceData.value); // Check if data updates correctly

  } catch (error) {
    console.error("Error fetching data:", error);
  }
};


// Fetch data when the component mounts
onMounted(fetchAttendanceData);

const chartOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: 'top',
    },
    title: {
      display: true,
      text: 'Weekly Attendance',
    },
  },
}

const pieChartData = {
  labels: ['BSA', 'BEEd', 'BSEd', 'BSIT', 'BSHM', 'AFA', 'GAS'],
  datasets: [
    {
      label: 'Students',
      backgroundColor: ['#D70C24ff', '#007BFF', '#FFC107', '#A12873', '#C2B11D', '#191FC5', '#038624'],
      data: [100, 120, 90, 150, 80, 70, 60], // Example data for courses
    },
  ],
}

const pieChartOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: 'top',
    },
    title: {
      display: true,
      text: 'Courses of Students Entered Library',
    },
  },
}

const totalVisitors = ref(0);

onMounted(async () => {
  try {
    const response = await axios.get('/attendance/total-visitors');
    totalVisitors.value = parseInt(response.data.total_visitors, 10);
  } catch (error) {
    console.error('Error fetching total visitors:', error);
  }
});

const totalVisitorsToday = ref(0);

onMounted(async () => {
  try {
    const response = await axios.get('/attendance/total-visitors-today');
    totalVisitorsToday.value = parseInt(response.data.visitor_count, 10);
  } catch (error) {
    console.error('Error fetching total visitors:', error);
  }
});

const totalStudents = ref(0);
onMounted(async () => {
  try {
    const response = await axios.get('/students/count');
    totalStudents.value = parseInt(response.data.total_students, 10);
  } catch (error) {
    console.error('Error fetching total students:', error);
  }
});

const currentlyInside = ref(0);
onMounted(async () => {
  try {
    const response = await axios.get('/attendance/currently-inside');
    currentlyInside.value = parseInt(response.data.currently_inside ?? 0, 10);
  } catch (error) {
    console.error('Error fetching currently inside:', error);
  }
});

const totalCollege = ref(0);
const totalJHS = ref(0);
onMounted(async () => {
  try {
    const response = await axios.get('/attendance/students-by-level');
    totalCollege.value = parseInt(response.data.total_college, 10);
    totalJHS.value = parseInt(response.data.total_JHS, 10);
  } catch (error) {
    console.error('Error fetching total students:', error);
  }
});
</script>


<template>
  <Head title=" | Dashboard" />

  <!-- Dashboard Overview -->
  <div class="bg-white rounded-lg shadow pl-6 pr-6 pt-1 w-[80%]">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">Dashboard</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-blue-100 rounded-lg shadow p-4 text-center">
        <h2 class="text-gray-500 text-m">Visitors Today</h2>
        <p class="text-2xl font-semibold text-blue-600">{{ totalVisitorsToday }}</p>
      </div>
      <div class="bg-green-100 rounded-lg shadow p-4 text-center">
        <h2 class="text-gray-500 text-m">Currently Inside</h2>
        <p class="text-2xl font-semibold text-green-600">{{ currentlyInside }}</p>
      </div>
      <div class="bg-orange-100 rounded-lg shadow p-4 text-center">
        <h2 class="text-gray-500 text-m">Registered Students</h2>
        <p class="text-2xl font-semibold text-orange-600">{{ totalStudents }}</p>
      </div>
      <div class="bg-purple-100 rounded-lg shadow p-4 text-center">
        <h2 class="text-gray-500 text-m">Total Visitors</h2>
        <p class="text-2xl font-semibold text-purple-600">{{totalVisitors}}</p>
      </div>
    </div>

     <!-- Records Overview -->
    <h2 class="text-xl font-semibold text-gray-700 mb-4 mt-14">Records Overview</h2>
    <div class="flex flex-wrap gap-6">
      
      <!-- Year of Students Entered Library -->
      <div class="bg-slate-50 rounded-lg shadow p-6 chart-container">
        <Pie :data="pieChartData" :options="pieChartOptions" />
      </div>

      <div class="bg-slate-50 rounded-lg shadow p-6 pt-14 chart-container">
        <Bar :data="attendanceData" :options="chartOptions"/>
      </div>
    </div>
  </div>

  <RightSidebar />
</template>


<style scoped>
.chart-container {
  height: 430px;
  width: 470px;
  flex: 1;
}
.dashboard-head {
 text-decoration: uppercase;
 font-size: 24px;
 margin-left: 20px;
}
.dashboard-wrapper {
  display: flex;
}

.dashboard-container {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center; 
}

.main-content {
  flex: 2;
  display: flex;
  justify-content: flex-start;
  align-items: center;
}

.content-wrapper {
  width: 100%;
  max-width: 1200px;
}

@media (max-width: 768px) {
  .dashboard-container {
    display: flex;
    max-width: 100%;
    justify-content: center; 
    align-items: center; 
    height: 100vh; 
  }
}
</style>

