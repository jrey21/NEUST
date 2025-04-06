<template>
    <transition name="fade">
        <div v-if="show" class="modal-overlay">
            <div class="modal-content">
                <h2 class="add-student" style="color: #488a99; padding:0; margin:0 0 5px 0; font-weight: bold;">Add New Student</h2>
                <form @submit.prevent="$emit('add-student')">
                    <div class="form-row">
                        <div class="form-group w-1/2">
                            <label for="new-student-id">Student ID:</label>
                            <input type="text" v-model="newStudent.student_id" id="new-student-id" required>
                        </div>
                        <div class="form-group w-1/2">
                            <label for="new-name">Student Name:</label>
                            <input type="text" v-model="capitalizedStudentName" id="new-name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group w-1/2">
                            <label for="category">Category:</label>
                            <select v-model="category" id="category" required>
                                <option value="" disabled>Select Category</option>
                                <option value="JHS or SHS">JHS or SHS</option>
                                <option value="College">College</option>
                            </select>
                        </div>
                        <div class="form-group w-1/2">
                            <label for="new-year">Year:</label>
                            <select v-model="newStudent.year" id="new-year" required>
                                <option value="" disabled>Select Year</option>
                                <option v-for="year in yearOptions" :key="year" :value="year">{{ year }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group w-1/2">
                            <label for="new-course">Course/Strand:</label>
                            <select v-model="newStudent.course" id="new-course" required>
                                <option value="" disabled>Select Course</option>
                                <option v-for="course in courseOptions" :key="course" :value="course">{{ course }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group w-full">
                            <label for="new-adviser">Adviser:</label>
                            <input type="text" v-model="capitalizedStudentAdviser" id="new-adviser" placeholder="Prof. Juan Dela Cruz" required>
                        </div>
                    </div>
                    <div class="button-container">
                        <button type="submit">Save</button>
                        <button type="button" @click="$emit('close')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    newStudent: Object
});

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
        return props.newStudent.name;
    },
    set(value) {
        props.newStudent.name = capitalizeWords(value);
    }
});

const capitalizedStudentAdviser = computed({
    get() {
        return props.newStudent.adviser;
    },
    set(value) {
        props.newStudent.adviser = capitalizeWords(value);
    }
});
</script>

<style scoped>
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
    width: 500px; 
    width: 35%; 
    height:500px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    animation: slide-down 0.3s ease-out;
}

.modal-content h2 {
    margin-top: 0;
    font-size: 18px;
    color: #333;
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
</style>
