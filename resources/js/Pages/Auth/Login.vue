<script setup>
import { ref, onMounted } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import LoginLayout from '@/Layouts/LoginLayout.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEye, faEyeSlash } from '@fortawesome/free-solid-svg-icons';

defineOptions({
  layout: LoginLayout
})

const form = useForm({
  username: null,
  password: null
})

const error = ref('')
const showPassword = ref(false);
const flashMessage = ref('');

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
// Load remembered email from localStorage when component mounts
onMounted(() => {
    const rememberedUsername = localStorage.getItem('rememberedUsername');
    if (rememberedUsername) {
        form.username = rememberedUsername;
        form.remember = true;
    }
});

const handleLogin = () => {
    form.post(route('login'), {
        onSuccess: () => {
            flashMessage.value = 'Successfully logged in!';
            
            // Store username if "Remember Me" is checked
            if (form.remember) {
                localStorage.setItem('rememberedUsername', form.username);
            } else {
                localStorage.removeItem('rememberedUsername');
            }
        },
        onError: (errors) => {
            error.value = errors.username || errors.password || 'Login failed. Please try again.';
            form.reset("password");
        }
    });
};
</script>

<template>
  <Head title=" | Login" />
  <div class="home-container">
    <div class="login-box">
      <h1 class="text-2xl font-bold text-center text-gray-800 mb-4">NEUST Gabaldon Library Attendance System</h1>
      <img src="../../Images/neust-logo.png" style="width: 80px; height: 80px; margin: 0 auto;" alt="NEUST Logo" class="logo" />
      <hr class="mb-4 mt-3" />
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="username">Username</label>
          <input
            type="text"
            id="username"
            v-model="form.username"
            required
          />
          <p v-if="error" class="error text-small">{{ error }}</p>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <div class="relative">
            <input
              :type="showPassword ? 'text' : 'password'"
              id="password"
              v-model="form.password"
              required
              class="pr-10"
            />
            <FontAwesomeIcon 
                :icon="showPassword ? faEyeSlash : faEye" 
                class="absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer text-slate-600"
                @click="togglePasswordVisibility"
            />
          </div>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <input type="checkbox" v-model="form.remember" id="remember">
                <label for="remember" class="text-gray-700 text-sm mt-2" >Remember me</label>
            </div>
        </div>
        <button type="submit">Login</button>
        <p class="text-gray-600 mt-3 text-center">Need an account? <Link :href="route('register')" class="text-link">Register</Link></p>
      </form>
      <div v-if="flashMessage" class="text-green-500 mt-2">{{ flashMessage }}</div>
    </div>
  </div>
</template>

<style scoped>
.text-link {
  color: #007BFF;
  font-weight: bold;
}
.home-container {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 7% 0 7.5% 0;
}
.login-box {
  background-color: white;
  padding: 2rem;
  border-radius: 10px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.form-group {
  margin-bottom: 1rem;
}
label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
}
input {
  width: 100%;
  padding: 0.6rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-sizing: border-box;
  font-size: 1rem;
}
input[type="checkbox"] {
  transform: scale(0.8); 
  width: auto; 
  height: auto; 
}
button {
  width: 100%;
  padding: 0.7rem;
  background-color: #007BFF;
  color: white;
  font-size: 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 1rem;
  transition: background-color 0.3s;
}
button:hover {
  background-color: #0056b3;
}
.error {
  color: red;
  font-size: smaller;
  margin-top: 3px;
  text-align: left;
}
</style>
