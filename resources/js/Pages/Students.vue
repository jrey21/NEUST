<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, onMounted, computed, watchEffect, watch } from 'vue';
import axios from 'axios';
import QRCode from 'qrcode';
import jsPDF from 'jspdf';
import AddStudentModal from '@/Components/AddStudentModal.vue';
import * as XLSX from 'xlsx';

defineOptions({ layout: DashboardLayout });

const students = ref([]);
const searchQuery = ref('');

const selectedFilter = ref('');
const sortOption = ref(''); 

const filterOptions = computed(() => {
    if (selectedFilter.value === 'year') {
        return [...new Set(students.value.map(student => student.year))];
    } else if (selectedFilter.value === 'course') {
        return [...new Set(students.value.map(student => student.course))];
    } else if (selectedFilter.value === 'adviser') {
        return [...new Set(students.value.map(student => student.adviser))];
    }
    return [];
});

const availableCourses = ref([]);
const availableYears = ref([]);

// Fetch available courses and years
const fetchAvailableFilters = async () => {
    try {
        const response = await axios.get(route('students-filters'));
        availableCourses.value = response.data.courses;
        availableYears.value = response.data.years;
    } catch (error) {
        console.error('Error fetching filter data:', error);
    }
};

const filteredData = computed(() => {
    if (!searchQuery.value && !selectedFilter.value && !sortOption.value) {
        return students.value;
    }

    return students.value.filter(student => {
        const matchesSearch = student.name.toLowerCase().includes(searchQuery.value.toLowerCase());

        if (selectedFilter.value === 'year') {
            return matchesSearch && student.year.toLowerCase().includes(searchQuery.value.toLowerCase());
        } else if (selectedFilter.value === 'course') {
            return matchesSearch && student.course.toLowerCase().includes(searchQuery.value.toLowerCase());
        } else if(selectedFilter.value === 'adviser') {
            return matchesSearch && student.adviser.toLowerCase().includes(searchQuery.value.toLowerCase());
        }

        return matchesSearch;
    }).filter(student => {
        if (sortOption.value) {
            if (selectedFilter.value === 'year') {
                return student.year === sortOption.value;
            } else if (selectedFilter.value === 'course') {
                return student.course === sortOption.value;
            } else if (selectedFilter.value === 'adviser') {
                return student.adviser === sortOption.value;
            }
        }
        return true;
    });
});

const currentPage = ref(1);
const itemsPerPage = 20;

const sortOrder = ref('asc'); 
const sortKey = ref('name'); 

const sortedData = computed(() => {
    let data = [...filteredData.value];

    data.sort((a, b) => {
        const aValue = a[sortKey.value]?.toString().toLowerCase() || '';
        const bValue = b[sortKey.value]?.toString().toLowerCase() || '';

        if (sortOrder.value === 'asc') {
            return aValue.localeCompare(bValue);
        } else {
            return bValue.localeCompare(aValue);
        }
    });

    return data;
});

const setSortKey = (key) => {
    if (sortKey.value === key) {
        toggleSortOrder();
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
};

const sortTable = (key) => {
    if (sortKey.value === key) {
        toggleSortOrder();
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
};

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return sortedData.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredData.value.length / itemsPerPage);
});

const changePage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const toggleSortOrder = () => {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
};

const fetchStudents = async () => {
    try {
        const response = await axios.get(route('students-data'));
        students.value = response.data;
    } catch (error) {
        console.error('Error fetching students data:', error);
    }
};

onMounted(async () => {
    await fetchAvailableFilters();
    await fetchStudents();
});

watchEffect(async () => {
    await fetchStudents();
});

const showDeleteModal = ref(false);
const studentToDelete = ref(null);

const confirmDelete = (student) => {
    studentToDelete.value = student;
    showDeleteModal.value = true;
};

const deleteStudent = async () => {
    try {
        await axios.delete(route('students-delete', { id: studentToDelete.value.id }));
        students.value = students.value.filter(item => item.id !== studentToDelete.value.id);
        flashMessage.value = 'Student deleted successfully!';
        showSuccessAlert.value = true;
        setTimeout(() => {
            showSuccessAlert.value = false;
        }, 3000);
        closeDeleteModal();
    } catch (error) {
        console.error('Error deleting student:', error);
        flashMessage.value = `Error deleting student: ${error.response?.data?.message || error.message}`;
        showErrorAlert.value = true;
        setTimeout(() => {
            showErrorAlert.value = false;
        }, 5000);
    }
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    studentToDelete.value = null;
};

// QR Code generation and display
const showQR = ref(null);

const toggleQR = (id) => {
    showQR.value = showQR.value === id ? null : id;
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
        const qrCode = await generateQRCode(`${data.student_id}`);
        dataWithQR.push({ ...data, qrCode });
    }
    paginatedDataWithQR.value = dataWithQR;
});

const downloadQRCode = async (data) => {
    let courseAcronym = data.course;
    switch (data.course) {
        case 'Bachelor of Science in Agriculture':
            courseAcronym = 'BSA';
            break;
        case 'Bachelor of Elementary Education':
            courseAcronym = 'BEEd';
            break;
        case 'Bachelor of Secondary Education':
            courseAcronym = 'BSEd';
            break;
        case 'Bachelor of Science in Information Technology':
            courseAcronym = 'BSIT';
            break;
        case 'Bachelor of Science in Hospitality Management':
            courseAcronym = 'BSHM';
            break;
    }

    const qrCode = await generateQRCode(`${data.student_id}`);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();
    img.src = qrCode;
    img.onload = () => {
        const qrCodeSize = 300;
        canvas.width = qrCodeSize;
        canvas.height = qrCodeSize + 70; 
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 50, qrCodeSize, qrCodeSize);
        ctx.fillStyle = 'black';
        ctx.font = '20px Arial'; 
        ctx.textAlign = 'center';
        ctx.fillText(`${data.name}`, canvas.width / 2, 20);
        ctx.font = '12px Arial'; 
        ctx.textAlign = 'center';
        const courseText = data.course === 'None' ? `${data.year}`: `${data.year} - ${courseAcronym}`;
        ctx.fillText(courseText, canvas.width / 2, 38);
        ctx.fillStyle = 'rgba(0, 0, 0, 0.5)'; 
        ctx.font = '16px Arial'; 
        ctx.fillText('© NEUST 2025', canvas.width / 2, canvas.height - 18);
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/png', 1.0); 
        link.download = `${data.name}_QRCode.png`;
        link.click();
    };
};

const downloadAllQRCodesPDF = async () => {
    const doc = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'legal'
    });
    doc.setFontSize(12);
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const qrCodeSize = 50; 
    const margin = 10;
    const qrCodesPerRow = 3;
    const qrCodesPerColumn = 4;
    const qrCodesPerPage = qrCodesPerRow * qrCodesPerColumn;

    let xPosition = margin;
    let yPosition = margin;

    for (let i = 0; i < sortedData.value.length; i++) {
        const data = sortedData.value[i];
        let courseAcronym = data.course;
        switch (data.course) {
            case 'Bachelor of Science in Agriculture':
                courseAcronym = 'BSA';
                break;
            case 'Bachelor of Elementary Education':
                courseAcronym = 'BEEd';
                break;
            case 'Bachelor of Secondary Education':
                courseAcronym = 'BSEd';
                break;
            case 'Bachelor of Science in Information Technology':
                courseAcronym = 'BSIT';
                break;
            case 'Bachelor of Science in Hospitality Management':
                courseAcronym = 'BSHM';
                break;
        }
        const qrCode = await generateQRCode(`${data.student_id}`);
        const img = new Image();
        img.src = qrCode;

        await new Promise((resolve) => {
            img.onload = () => {
                if (i % qrCodesPerPage === 0 && i !== 0) {
                    doc.addPage();
                    xPosition = margin;
                    yPosition = margin;
                } else if (i % qrCodesPerRow === 0 && i !== 0) {
                    xPosition = margin;
                    yPosition += qrCodeSize + margin * 3;
                }

                doc.setFontSize(12);
                doc.setTextColor(0);
                doc.text(`${data.name}`, xPosition + qrCodeSize / 2, yPosition, { align: 'center' });
                doc.setFontSize(10); 
                doc.text(data.course === 'None' ? `${data.year}`: `${data.year} - ${courseAcronym}`, xPosition + qrCodeSize / 2, yPosition + 5, { align: 'center' });
                doc.addImage(img, 'PNG', xPosition, yPosition + 8, qrCodeSize, qrCodeSize);
                doc.setFontSize(10);
                doc.setTextColor(150);
                doc.text('© NEUST 2025', xPosition + qrCodeSize / 2, yPosition + 5 + qrCodeSize + 10, { align: 'center' });

                // Add border box around QR code and name
                doc.setDrawColor(0);
                doc.setLineWidth(0.5);
                doc.rect(xPosition - 2, yPosition - 4, qrCodeSize + 4, qrCodeSize + 30);

                xPosition += qrCodeSize + margin;
                resolve();
            };
        });
    }

    doc.save('All-Students-QRCodes.pdf');
};

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

const doesStudentExist = (id, name) => {
    return students.value.some(student => student.student_id === id || student.name.toLowerCase() === name.toLowerCase());
};

const addStudent = async () => {
    newStudent.value.name = capitalizeWords(newStudent.value.name);
    newStudent.value.adviser = capitalizeWords(newStudent.value.adviser);

    if (doesStudentExist(newStudent.value.student_id, newStudent.value.name)) {
        flashMessage.value = 'Student record already exists!';
        showErrorAlert.value = true;
        setTimeout(() => (showErrorAlert.value = false), 5000);
        return;
    }

    try {
        const response = await axios.post(route('students'), newStudent.value);

        // Fetch updated students data
        await fetchStudents();

        // Reset filters so the student is visible
        searchQuery.value = '';
        selectedFilter.value = '';
        sortOption.value = '';
        currentPage.value = 1;

        // Show success
        flashMessage.value = 'Student added successfully!';
        showSuccessAlert.value = true;
        setTimeout(() => (showSuccessAlert.value = false), 3000);

        // Close modal
        closeAddStudentModal();
    } catch (error) {
        flashMessage.value = 'Failed to add student! ' + (error.response?.data?.message || error.message);
        showErrorAlert.value = true;
        setTimeout(() => (showErrorAlert.value = false), 5000);
    }
};


const category = ref(''); 

const yearOptions = computed(() => {
    if (category.value === 'JHS or SHS') {
        return ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'];
    } else if (category.value === 'College') {
        return ['1st Year', '2nd Year', '3rd Year', '4th Year'];
    }
    return [];
});

const courseOptions = computed(() => {
    if (category.value === 'JHS or SHS') {
        return ['AFA', 'GAS', 'None'];
    } else if (category.value === 'College') {
        return [
            'Bachelor of Science in Agriculture',
            'Bachelor of Elementary Education',
            'Bachelor of Secondary Education',
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Hospitality Management'
        ];
    }
    return [];
});

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

const displayCourse = (course) => {
    return course === 'None' ? '-' : course;
};

// Function to handle importing students from an Excel file
const importStudentsFromExcel = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    try {
        const data = await file.arrayBuffer();
        const workbook = XLSX.read(data, { type: 'array' });
        const sheetName = workbook.SheetNames[0];
        const sheet = workbook.Sheets[sheetName];
        const studentsData = XLSX.utils.sheet_to_json(sheet);

        for (const student of studentsData) {
            const newStudent = {
                student_id: student['Student ID'] || '',
                name: capitalizeWords(student['Name'] || ''),
                year: student['Year'] || '',
                course: student['Course'] || '',
                adviser: capitalizeWords(student['Adviser'] || ''),
            };

            if (!doesStudentExist(newStudent.student_id, newStudent.name)) {
                await axios.post(route('students'), newStudent);
            }
        }

        // Fetch updated students data
        await fetchStudents();

        // Show success message
        flashMessage.value = 'Students imported successfully!';
        showSuccessAlert.value = true;
        setTimeout(() => (showSuccessAlert.value = false), 3000);
    } catch (error) {
        console.error('Error importing students:', error);
        flashMessage.value = 'Failed to import students!';
        showErrorAlert.value = true;
        setTimeout(() => (showErrorAlert.value = false), 5000);
    } finally {
        event.target.value = ''; // Reset the file input
    }
};

const showEditStudentModal = ref(false);
const studentToEdit = ref(null);

const openEditStudentModal = (student) => {
    studentToEdit.value = { ...student };
    showEditStudentModal.value = true;
};

const closeEditStudentModal = () => {
    showEditStudentModal.value = false;
    studentToEdit.value = null;
};

const editStudent = async () => {
    try {
        const response = await axios.put(route('students-update', { id: studentToEdit.value.id }), studentToEdit.value);

        // Update the student in the local list
        const index = students.value.findIndex(student => student.id === studentToEdit.value.id);
        if (index !== -1) {
            students.value[index] = { ...response.data }; // Use updated data from the server
        }

        // Show success message
        flashMessage.value = 'Student updated successfully!';
        showSuccessAlert.value = true;
        setTimeout(() => (showSuccessAlert.value = false), 3000);

        // Close modal
        closeEditStudentModal();
    } catch (error) {
        console.error('Error updating student:', error);
        flashMessage.value = 'Failed to update student! ' + (error.response?.data?.message || error.message);
        showErrorAlert.value = true;
        setTimeout(() => (showErrorAlert.value = false), 5000);
    }
};

const editCategory = ref(''); // Add category for edit modal

const editYearOptions = computed(() => {
    if (editCategory.value === 'JHS or SHS') {
        return ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'];
    } else if (editCategory.value === 'College') {
        return ['1st Year', '2nd Year', '3rd Year', '4th Year'];
    }
    return [];
});

const editCourseOptions = computed(() => {
    if (editCategory.value === 'JHS or SHS') {
        return ['AFA', 'GAS', 'None'];
    } else if (editCategory.value === 'College') {
        return [
            'Bachelor of Science in Agriculture',
            'Bachelor of Elementary Education',
            'Bachelor of Secondary Education',
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Hospitality Management'
        ];
    }
    return [];
});

const determineCategory = computed(() => {
    if (['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'].includes(studentToEdit.value?.year)) {
        return 'JHS or SHS';
    }
    return 'College';
});

// Watcher to update category when year changes
watch(() => studentToEdit.value?.year, (newYear) => {
    editCategory.value = determineCategory.value;
});
</script>

<template>
    <Head title=" | Students" />
    
    <div class="students-container">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">List of Students</h2>
        <div role="alert" class="alert alert-success" v-if="showSuccessAlert">
        <div class="alert-content">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ flashMessage }}</span>
        </div>
        </div>
        <div role="alert" class="alert alert-error" v-if="showErrorAlert">
            <div class="alert-content">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ flashMessage }}</span>
            </div>
        </div>
        <div class="action-bar">
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search" style="margin-left: 8px;"></i>
                    <input type="text" v-model="searchQuery" placeholder="Search by name ..." />
                </div>
            </div>
            <div>
                <!-- Filter Dropdown -->
                <select v-model="selectedFilter" class="sort-select">
                    <option value="" selected>Select Filter</option> 
                    <option value="year">Year</option>
                    <option value="course">Course</option>
                    <option value="adviser">Adviser</option>
                </select>
                <select v-model="sortOption" class="sort-select">
                    <option value="asc" selected>Sort by</option> 
                    <option v-for="option in filterOptions" :key="option" :value="option">{{ option }}</option>
                </select>
                <button @click="downloadAllQRCodesPDF" class="download-button">
                    <i class="fas fa-download"></i> QR Codes
                </button>
                <input type="file" accept=".xlsx, .xls" @change="importStudentsFromExcel" style="display: none;" id="importExcel" />
                <label for="importExcel" class="import-button">
                    <i class="fas fa-file-import"></i> Excel
                </label>
                <button @click="openAddStudentModal" class="add-button">
                    <i class="fas fa-plus"></i> Student
                </button>
                
            </div>
        </div>
        <div class="table-wrapper">
            <table class="students-table">
                <thead>
                    <tr>
                        <th style="width:10%;" @click="sortTable('student_id')">ID</th>
                        <th style="width:20%;" @click="sortTable('name')">Name</th>
                        <th style="width:10%;" @click="sortTable('year')">Year Level</th>
                        <th style="width:20%;" @click="sortTable('course')">Course</th>
                        <th style="width:20%;" @click="sortTable('adviser')">Adviser</th>
                        <th style="text-align: center;">QR Code</th>
                        <th >Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="paginatedDataWithQR.length === 0">
                        <td colspan="7">No data found</td>
                    </tr>
                    <tr v-for="student in paginatedDataWithQR" :key="student.id" style="font-size: 15px;">
                        <td>{{ student.student_id }}</td>
                        <td>{{ student.name }}</td>
                        <td>{{ student.year }}</td>
                        <td>{{ displayCourse(student.course) }}</td>
                        <td>{{ student.adviser }}</td>
                        <td>
                            <div style="text-align: center;">
                                <span @click="toggleQR(student.id)" style="cursor: pointer; color: #007bff; font-size: 12px;">
                                    {{ showQR === student.id ? 'Hide' : 'View' }}
                                </span>
                                <div v-if="showQR === student.id" class="qr-wrapper">
                                    <img :src="student.qrCode" alt="QR Code" style="height: 50px;"/>
                                    <button @click="downloadQRCode(student)" class="download-btn">Download</button>
                                </div>
                            </div>
                        </td>

                        <td>
                            <button @click="openEditStudentModal(student)" class="edit-button mr-2">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button v-if="$page.props.auth.user.role === 'admin'" @click="confirmDelete(student)" class="delete-button">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
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
    <AddStudentModal 
        v-if="showAddStudentModal" 
        :show="showAddStudentModal" 
        :new-student="newStudent" 
        @close="closeAddStudentModal" 
        @add-student="addStudent" 
    />
    <div v-if="showDeleteModal" class="modal-overlay">
        <div class="modal-content-delete">
            <h2>Confirm Delete</h2>
            <hr style="margin-bottom:10px; padding: 0;">
            <p>Are you sure you want to delete <strong style="color: #007bff;">{{ studentToDelete.name }}</strong>?</p>
            <div class="modal-buttons">
                <button @click="deleteStudent">Delete</button>
                <button @click="closeDeleteModal">Cancel</button>
            </div>
        </div>
    </div>
    <div v-if="showEditStudentModal" class="modal-overlay">
        <div class="modal-content">
            <h2>Edit Student</h2>
            <hr style="margin-bottom:10px; padding: 0;">
            <label for="editName">Name</label>
            <input id="editName" v-model="studentToEdit.name" type="text" />
            <div class="form-row">
                <div class="form-group">
                    <label for="editCategory">Category</label>
                    <select id="editCategory" v-model="editCategory">
                        <option value="" disabled>Select Category</option>
                        <option value="JHS or SHS">JHS or SHS</option>
                        <option value="College">College</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editYear">Year</label>
                    <select id="editYear" v-model="studentToEdit.year">
                        <option value="" disabled>Select Year</option>
                        <option v-for="year in editYearOptions" :key="year" :value="year">{{ year }}</option>
                    </select>
                </div>
            </div>
            <label for="editCourse">Course</label>
            <select id="editCourse" v-model="studentToEdit.course">
                <option value="" disabled>Select Course</option>
                <option v-for="course in editCourseOptions" :key="course" :value="course">{{ course }}</option>
            </select>
            <label for="editAdviser">Adviser</label>
            <input id="editAdviser" v-model="studentToEdit.adviser" type="text" />
            
            <div class="modal-buttons mt-3" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button @click="editStudent" class="update-button">Update</button>
                <button @click="closeEditStudentModal" class="cancel-button">Cancel</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.students-container{
    margin: 0 2%;
    padding: 5px;
    max-width: 100%;
}
.alert-success {
    background-color:#00D395;
    border-left: 4px solid #38a169;
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    font-family: sans-serif;
    font-size: 14px;
    max-width:100%;
    margin-bottom: 4px;
  }
.alert-error {
    background-color: #ff4d4d;
    border-left: 4px solid #ff1a1a;
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    font-family: sans-serif;
    font-size: 14px;
    max-width: 100%;
    margin-bottom: 4px;
}
.alert-content {
    display: flex;
    align-items: center;
}
.alert-content svg {
    margin-right: 8px;
}
/* h2{
    color: #488a99;
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 23px;
} */
.qr-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 5px;
}

.qr-wrapper img {
    max-width: 150px;
    margin-bottom: 5px;
}

.download-btn {
    font-size: 10px;
    color: maroon;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    margin-top: 4px;
}

.download-btn:hover {
    text-decoration: underline;
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

.students-table td {
    background-color: #fff;
}

.students-table td:first-child {
    word-wrap: break-word;
    max-width: 100px; 
}

.delete-button {
    background: none; 
    color: #ff4d4d;
    border: none;
    padding: 5px; 
    font-size: 19px; 
    cursor: pointer;
    border-radius: 4px;
    transition: color 0.3s ease, transform 0.2s ease;
    box-shadow: none; 
}

.delete-button:hover {
    color: #ff1a1a;
    transform: scale(1.05);
}
button {
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.download-button {
    background-color: #17a2b8; 
    border: none;
    color: white;
    padding: 6px 20px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.download-button:hover {
    background-color: #0056b3; 
}

.table-wrapper {
    max-height: 528px; 
    overflow-y: auto;
}

.button-wrapper {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
}

.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0;
    margin-bottom: 10px;
}

.search-container {
    display: flex;
    align-items: center;
}

.search-box {
    display: flex;
    align-items: center;
    width: 240px;
    padding: 2px;
    border: 2px solid #9dc0e6;
    border-radius: 50px;
    background-color: #fff;
    box-shadow: 0 4px 4px rgba(0, 0, 0, 0.1);
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
    height: 25px;
    box-shadow: none;
}

.sort-select {
    padding: 5px;
    margin-right: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    width: 150px;
}

.filter-container {
    display: flex;
    align-items: center;
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

.add-button {
    background-color: #28a745; 
    border: none;
    color: white;
    padding: 6px 20px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
    margin-left: 10px;
}

.add-button:hover {
    background-color: #218838; 
}

.add-student-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1001;
}

.add-student-modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    max-width: 100%;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    animation: slide-down 0.3s ease-out;
}

.add-student-modal-content h2 {
    margin-top: 0;
    margin-bottom: 5px;
    font-size: 18px;
    color: #094b6e;
}

.add-student-modal-content .button-container {
    display: flex;
    justify-content: flex-end;
    gap: 5px;
}

.add-student-modal-content .button-container button {
    margin-top: 20px;
    padding: 8px 12px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.add-student-modal-content .button-container button:hover {
    background-color: #218838;
}

.add-student-modal-content .button-container button:last-child {
    background-color: #007bff;
    margin-left: 10px;
}

.add-student-modal-content .button-container button:last-child:hover {
    background-color: #0056b3;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1001;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 400px; 
    width: 30%; 
    height:500px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    animation: slide-down 0.3s ease-out;
}

.modal-content h2 {
    margin-top: 0;
    font-size: 18px;
    color: #488a99;
    font-weight: bold;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}
.modal-content-delete h2 {
    margin-top: 0;
    font-size: 18px;
    font-weight: bold;
    color: #488a99;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}

.modal-content label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
    color: #555;
}

.modal-content input,
.modal-content select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

.modal-content .button-container {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.modal-content .button-container button {
    margin-left: 10px;
    padding: 8px 12px;
    font-size: 14px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.modal-content .button-container button[type="submit"] {
    background-color: #4CAF50;
    color: white;
}

.modal-content .button-container button[type="submit"]:hover {
    background-color: #45a049;
}

.modal-content .button-container button[type="button"] {
    background-color: #f44336;
    color: white;
}

.modal-content .button-container button[type="button"]:hover {
    background-color: #e53935;
}

@keyframes slide-down {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.form-row {
    display: flex;
    justify-content: space-between;
    padding:0;
    margin:0;
}

.form-group {
    flex: 1;
    margin-right: 10px;
}

.form-group:last-child {
    margin-right: 0;
}
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1001;
}

.modal-content-delete {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    max-width: 100%;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    animation: slide-down 0.3s ease-out;
}

.modal-content-delete h2 {
    margin-top: 0;
    margin-bottom:5px;
    font-size: 18px;
    color: #094b6e;
}

.modal-content-delete .modal-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 5px;
}

.modal-content-delete .modal-buttons button {
    margin-top: 20px;
    padding: 8px 12px;
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.modal-content-delete .modal-buttons button:hover {
    background-color: #c82333;
}

.modal-content-delete .modal-buttons button:last-child {
    background-color: #007bff;
    margin-left: 10px;
}

.modal-content-delete .modal-buttons button:last-child:hover {
    background-color: #0056b3;
}

.import-button {
    background-color: #ffc107;
    border: none;
    color: white;
    padding: 6px 20px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
    margin-left: 10px;
}

.import-button:hover {
    background-color: #e0a800;
}

.edit-button {
    background: none;
    color: #ffc107;
    border: none;
    padding: 5px; 
    font-size: 19px; 
    cursor: pointer;
    border-radius: 4px;
    transition: color 0.3s ease, transform 0.2s ease;
    box-shadow: none; 
}

.edit-button:hover {
    color: #e0a800; 
    transform: scale(1.05);
}

.update-button {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.update-button:hover {
    background-color: #218838;
}

.cancel-button {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.cancel-button:hover {
    background-color: #c82333;
}
</style>