<template>
    <div v-if="show" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
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
        <p class="text-sm text-gray-700 mb-2">
            Thank you for registering.
        </p>
        <p class="text-sm text-gray-700 mb-4">
            Please take a picture of your QR code or get a printed copy from your adviser.
        </p>

         <!-- View QR Code Button -->
        <a class="block w-full mb-3">
            <button class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-1.5 px-6 rounded-full w-full">
            View QR Code
            </button>
        </a>
  
        <!-- Close Button -->
        <button @click="closeModal" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-1.5 px-6 rounded-full">
          CLOSE
        </button>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  
  const props = defineProps({
    show: Boolean
  })
  
  const emit = defineEmits(['close'])
  
  const closeModal = () => {
    emit('close')
  }
  </script>
  