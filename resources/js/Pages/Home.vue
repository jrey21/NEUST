<script setup>
import { ref, onMounted, computed, watchEffect } from 'vue';
import QRCode from 'qrcode';
import QRScanner from './QRScanner.vue'
import Layout from '@/Layouts/Layout.vue'
import Footer from '@/Layouts/Footer.vue'
import Header from '@/Layouts/Header.vue'
import AddStudentModal from '@/Components/AddStudentModal.vue' 
import axios from 'axios'

defineOptions({
  layout: Layout
})
const emit = defineEmits(['close'])
  
  const closeModal = () => {
    emit('close')
  }

const studentId = ref('')
const attendanceList = ref([])
const dateTime = ref('')

function updateDateTime() {
  const now = new Date()
  const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
  const timeStr = now.toLocaleTimeString()
  const dateStr = now.toLocaleDateString('en-US', dateOptions)
  dateTime.value = `${dateStr} • ${timeStr}`
}

onMounted(() => {
  updateDateTime()
  setInterval(updateDateTime, 1000)
})

function checkIn() {
  const newEntry = {
    id: Date.now(),
    studentId: studentId.value,
    time: new Date().toLocaleTimeString()
  }
  attendanceList.value.push(newEntry)
  studentId.value = ''
}

const showAddStudentModal = ref(false);
const newStudent = ref({ 
    student_id:'', 
    name: '', 
    year: '', 
    course: '', 
    adviser: '' 
});

const openAddStudentModal = () => {
    newStudent.value = { 
        student_id:'', 
        name: '', 
        year: '', 
        course: '', 
        adviser: '' 
    };
    showAddStudentModal.value = true;
};

const closeAddStudentModal = () => {
    showAddStudentModal.value = false;
};

const showSuccessAlert = ref(false);
const showErrorAlert = ref(false);
const showFlashMessage = ref(false);
const flashMessage = ref('');

const showFlashModal = ref(false);

const students = ref([]);

const fetchStudents = async () => {
    try {
        const response = await axios.get(route('students-data'));
        students.value = response.data;
    } catch (error) {
        console.error('Error fetching students data:', error);
    }
};

const generateQRCode = async (text) => {
    try {
        return await QRCode.toDataURL(text);
    } catch (error) {
        console.error('Error generating QR code:', error);
        return '';
    }
};

const paginatedDataWithQR = ref([]);

watchEffect(async () => {
    const dataWithQR = [];
    for (const data of paginatedData.value) {
        const qrCode = await generateQRCode(`${data.name} ${data.year} - ${data.course}`);
        dataWithQR.push({ ...data, qrCode });
    }
    paginatedDataWithQR.value = dataWithQR;
});

const doesStudentExist = (id, name) => {
    return students.value.some(student => student.student_id === id || student.name.toLowerCase() === name.toLowerCase());
};

const showQRCodeModal = ref(false);
const generatedQRCode = ref('');

const closeQRCodeModal = () => {
    showQRCodeModal.value = false;
};

const addStudent = async () => {
    newStudent.value.name = capitalizeWords(newStudent.value.name);
    newStudent.value.adviser = capitalizeWords(newStudent.value.adviser);

    if (doesStudentExist(newStudent.value.student_id, newStudent.value.name)) {
        flashMessage.value = 'Student record already exists!';
        showErrorAlert.value = true;
        showFlashModal.value = true;
        setTimeout(() => {
            showErrorAlert.value = false;
            showFlashModal.value = false;
        }, 5000);
        return;
    }

    try {
        const response = await axios.post(route('students'), newStudent.value);

        // Close modal immediately
        closeAddStudentModal();

        // Generate QR code string (e.g., name, year, course)
        const qrText = `${newStudent.value.name}  ${newStudent.value.year} - ${newStudent.value.course}`;
        generatedQRCode.value = await generateQRCode(qrText);

        // Show modal with the generated QR code
        showQRCodeModal.value = true;

        flashMessage.value = 'Student added successfully!';
        showSuccessAlert.value = true;
        showFlashModal.value = true;

    } catch (error) {
        flashMessage.value = 'Failed to add student! ' + (error.response?.data?.message || error.message);
        showErrorAlert.value = true;
        showFlashModal.value = true;
        setTimeout(() => {
            showErrorAlert.value = false;
            showFlashModal.value = false;
        }, 5000);
    } finally {
        isButtonDisabled.value = false;
    }
};


const capitalizeWords = (str) => {
    return str.replace(/\b\w/g, char => char.toUpperCase());
};

const capitalizedStudentName = computed({
    get() {
        return newStudent.value.name;
    },
    set(value) {
        newStudent.value.name = capitalizeWords(value);
    }
});

const capitalizedStudentAdviser = computed({
    get() {
        return newStudent.value.adviser;
    },
    set(value) {
        newStudent.value.adviser = capitalizeWords(value);
    }
});

onMounted(async () => {
    await fetchStudents();
});
</script>

<template>
  <Head title=" | Home" />

    <div role="alert" class="alert alert-error" v-if="showErrorAlert">
        <div class="alert-content">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ flashMessage }}</span>
        </div>
    </div>   
  <Header/>
  <div class="home-container">
    <div class="card">
      <img src="../Images/neust-logo.png" alt="NEUST Logo" class="logo" />
      <h1>Welcome to the <span class="neust">NEUST Gabaldon</span> Library</h1>
      <div class="phrase">
        <p>
          Kindly scan your QR code to record your attendance. This allows us to grant you access and track library usage respectively.
        </p>
      </div>
      <div class="datetime">{{ dateTime }}</div>
      <div class="webcam-feed">
        <section class="section-1">
          <QRScanner />
        </section>
      </div>
      <div>
        <p class="phrase-2">If you require assistance, please don't hesitate to approach our staff.</p>
        <p class="phrase-3">Not yet registered? Click <button style="color:#004aad;font-weight:bold;" @click="openAddStudentModal">here</button> to register.</p>
      </div>
    </div>
  </div>
  <Footer style="margin-top:18%;"/>
  <AddStudentModal 
        v-if="showAddStudentModal" 
        :show="showAddStudentModal" 
        :new-student="newStudent" 
        @close="closeAddStudentModal" 
        @add-student="addStudent" 
    />

  <div v-if="showQRCodeModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 animate-fade-in">
    <div class="bg-white rounded-lg shadow-lg p-6 w-[350px] text-center">
      <!-- Check Icon -->
      <div class="flex justify-center mb-4">
          <div class="bg-green-500 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
  
        <!-- Success Message -->
        <h2 class="text-lg font-semibold mb-1">Registration Successful!</h2>

        <p class="text-sm text-gray-700 mb-4 mt-5">
            Please take a picture of your QR code or get a printed copy from your adviser.
        </p>

        <!-- Display QR Code -->
        <img :src="generatedQRCode" alt="Generated QR Code" class="mx-auto mb-4" />

  
        <!-- Close Button -->
        <button @click="closeQRCodeModal" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-1.5 px-6 rounded-full">
          CLOSE
        </button>
    </div>
  </div>

</template>

<style scoped>
.section-1{
  display: flex;
  justify-content: left;
  align-items: left;
  height: 100%;
  width: 100%;
  background-color: white;
}
body {
  margin: 0;
  font-family: helvetica;
  background: linear-gradient(to right, #edf1f7, #f9fcff);
}
.phrase{
  font-size: 16px;
  color: rgb(239, 239, 239);
}
.phrase-2{
  font-size: 14px;
  margin-top: 20px;
  color: #484848;
}
.phrase-3{
  font-size: 14px;
  color: #484848;
}
.home-container {
  display: flex;
  justify-content: center;
  align-items: flex-start; 
  /* min-height: 100vh; */
}
.card { 
  background: #f3b004;
  padding: 20px 40px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
  text-align: center;
  width: 100%;
  height: 390px;
  margin: 0; 
}
.logo {
  width: 100px;
  margin-bottom: 20px;
  display: block;
  margin-left: auto;
  margin-right: auto;
}
h1 {
  font-size: 26px;
  margin-bottom: 10px;
  color: rgb(255, 255, 255);
}
.neust {
  font-weight: bold;
  color: #004aad;
}
.datetime {
  font-size: 15px;
  color: #666;
  margin-top:20px;
  margin-bottom: 25px;
}
.webcam-feed{
  width: 100%;
  max-width: 404px;
  height: 100%; 
  /* border-radius: 12px; */
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  border: 2px solid white;
  margin: 0 auto;
  padding: 0;
  max-height: 300px;
}
.copyright {
  text-align: center;
  font-size: 12px;
  color: #666;
  margin-top: 18%;
}

.modal {
  display: flex;
  justify-content: center;
  align-items: center;
  position: fixed;
  z-index: 1;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgb(0,0,0);
  background-color: rgba(0,0,0,0.4);
}

.modal-content {
  background-color: #fefefe;
  margin: auto;
  padding: 20px;
  border: 1px solid #888;
  width: 80%;
  max-width: 500px;
  text-align: center;
}

.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.animate-fade-in {
  animation: fadeIn 0.5s ease-in-out;
}
</style>
