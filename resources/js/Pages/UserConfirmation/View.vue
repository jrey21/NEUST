<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

defineOptions({ layout: DashboardLayout });

const pendingUsers = ref([]);
const successMessage = ref("");
const showRejectModal = ref(false);
const userToReject = ref(null);
const showApproveModal = ref(false);
const userToApprove = ref(null);

const alertType = ref("success");

const showAlert = (message, type = "success") => {
  successMessage.value = message;
  alertType.value = type;

  setTimeout(() => {
    successMessage.value = "";
  }, 3000);
};

const capitalizeWords = (str) => {
    return str.replace(/\b\w/g, char => char.toUpperCase());
};

const fetchPendingUsers = async () => {
    try {
        const response = await axios.get("/admin/pending-users");
        pendingUsers.value = response.data.map(user => ({
            ...user,
            name: capitalizeWords(user.name),
            role: capitalizeWords(user.role)
        }));
    } catch (error) {
        console.error("Error fetching pending users:", error);
    }
};

const approveUser = async (userId) => {
    try {
    await axios.post(`/admin/approve/${userId}`);
    showAlert("User approved successfully!", "success");
    fetchPendingUsers();
    closeApproveModal();
  } catch (error) {
    showAlert("Error approving user!", "error");
  }
};

const confirmReject = (user) => {
    userToReject.value = user;
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    userToReject.value = null;
};

const rejectUser = async (userId) => {
    try {
    await axios.post(`/admin/reject/${userId}`);
    showAlert("User rejected successfully!", "error");
    fetchPendingUsers();
    closeRejectModal();
  } catch (error) {
    showAlert("Error rejecting user!", "error");
  }
};

const confirmApprove = (user) => {
    userToApprove.value = user;
    showApproveModal.value = true;
};

const closeApproveModal = () => {
    showApproveModal.value = false;
    userToApprove.value = null;
};

onMounted(() => {
    fetchPendingUsers();
});
</script>

<template>
    <transition name="fade">
    <div v-if="successMessage" :class="['alert-box', alertType]">
      <span class="icon">
        <svg v-if="alertType === 'success'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
          <path fill="currentColor" d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2m-1 14l-4-4l1.41-1.41L11 12.17l4.59-4.58L17 9z"/>
        </svg>
        <svg v-if="alertType === 'error'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
          <path fill="currentColor" d="M13 13h-2V7h2zm0 4h-2v-2h2zm-1-15A10 10 0 1 0 22 12A10 10 0 0 0 12 2m0 18a8 8 0 1 1 8-8a8 8 0 0 1-8 8"/>
        </svg>
      </span>
      <span class="message ml-7">{{ successMessage }}</span>
    </div>
  </transition>
    <Head title=" | Confirm Users "/>
    <div class="header">
        <h2 class="text-xl font-semibold text-gray-700 mb-4 ml-6">Pending Users for Approval</h2>
    </div>
    <div class="data-container">
        <div v-if="pendingUsers.length === 0" class="text-gray-500">
            No pending users for approval.
        </div>
        <div v-else class="grid-container">
            <div v-for="user in pendingUsers" :key="user.id" class="grid-item">
                <div 
                    :class="['outside-box', user.role === 'Admin' ? 'bg-sky-600' : 'bg-sky-400']"
                >
                    <div class="inside-box">
                        <p><span class="labels">Approval ID:</span> <span class="data">{{ user.id }}</span> </p>
                        <p><span class="labels">Name:</span> <span class="data">{{ user.name }}</span></p>
                        <p><span class="labels">Username:</span> <span class="data">{{ user.username }}</span></p>
                        <p><span class="labels">Role:</span> <span class="data">{{ user.role }}</span></p>
                        <p><span class="labels">Status: </span><span class="data">{{ user.is_approved === 0 ? 'Pending' : Approved }}</span></p>
                    </div>
                    <div class="buttons mt-2">
                        <button 
                            @click="confirmReject(user)" 
                            class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700"
                        >
                            Reject
                        </button>
                        <button 
                            @click="confirmApprove(user)" 
                            class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700"
                        >
                            Approve
                        </button>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-if="showRejectModal" class="modal">
        <div class="modal-content">
            <h2 class="h2">Reject User Access?</h2>
            <hr style="margin-top: 10px; margin-bottom:10px; padding: 0;">
            <p>Once rejected, this user <strong style="color: #007bff;">{{ userToReject.name }}</strong> will not be able to log in. <normal style="font-size: 14px; color:red;">Do you want to proceed?</normal></p> 
            <div class="modal-buttons">
                <button @click="rejectUser(userToReject.id)">Confirm</button>
                <button @click="closeRejectModal">Cancel</button>
            </div>
        </div>
    </div>
    <div v-if="showApproveModal" class="modal">
        <div class="modal-content">
            <h2 class="h2">Confirm User Approval</h2>
            <hr style="margin-top: 10px; margin-bottom:10px; padding: 0;">
            <p>Are you sure you want to approve <strong style="color: #007bff;">{{ userToApprove.name }}</strong>?</p>
            <div class="modal-buttons">
                <button @click="approveUser(userToApprove.id)">Confirm</button>
                <button @click="closeApproveModal">Cancel</button>         
            </div>
        </div>
    </div>
</template>



<style scoped>
.alert-box {
  display: flex;
  align-items: center;
  justify-content: flex-start; 
  text-align: center; 
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  width: 400px;
  padding: 12px 20px;
  border-radius: 5px;
  box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
  font-size: 14px;
  font-weight: 500;
  z-index: 1001;
}

.success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.icon {
  font-size: 18px;
  display: flex;
  align-items: center;
  margin-right: 10px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  color: inherit;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
.h2 {
    color: #488a99;
    font-size: 18px;
    font-weight: bold;
}
.data-container {
    width: fit-content;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin: 0 2%;
    padding: 5px;
    max-width: 100%;
}
.outside-box {
    padding: 15px 15px 8px 15px;
    width: 295px;
    border-radius: 10px;
    border: none;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
.inside-box {
    background-color: white;
    border: none;
    color: #011b76;
    width: 100%;
    height: 140px;
    border-radius: 10px;
    position: relative;
    padding: 10px;
}
.labels {
    font-size: 14px;
    color: #010b2d;
    margin: 0;
}
.data {
    font-size: 14px;
    color: #0034f0;
    margin: 0;
}
.buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
    font-size: 14px;
}
.flash-modal {
    display: flex;
    justify-content: center;
    align-items: center;
    position: fixed;
    z-index: 1001;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
}
.flash-content {
    position: fixed;
    top: 10px;
    background-color: #28a745;
    color: #fff;
    padding: 20px 30px;
    border: 1px solid #28a745;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    font-size: 16px;
    max-width: 500px;
    margin: 0 auto;
    animation: slideDown 0.5s ease-in-out, fadeOut 3s ease-in-out 2s;
    display: flex;
    align-items: center;
    gap: 10px;
}
.flash-content.success {
    background-color: #28a745;
    border-color: #28a745;
}
.flash-content.error {
    background-color: #dc3545;
    border-color: #dc3545;
}
.flash-content .icon {
    font-size: 20px;
    display: flex;
    align-items: center;
}
.flash-content.success .icon {
    color: #fff;
}
.flash-content.error .icon {
    color: #fff;
}
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to {
    opacity: 0;
}
@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.grid-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}
.grid-item {
    display: flex;
    justify-content: center;
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
    background-color: rgba(0, 0, 0, 0.4);
    z-index: 1000;
}

.modal-content {
    background-color: #fff;
    margin: auto;
    padding: 20px;
    border: 1px solid #ddd;
    width: 80%;
    max-width: 400px; 
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.3s ease-in-out;
    z-index: 1001;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 5px;
}

.modal-content button {
    margin-top: 20px;
    padding: 8px 12px;
    width: 70px; 
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px; 
    transition: background-color 0.3s ease;
}

.modal-content button:hover {
    opacity: 0.9;
}

.modal-content button:last-child {
    background-color: #dc3545; /* Switch to red for confirm */
    color: white;
    margin-left: 10px;
}

.modal-content button:last-child:hover {
    background-color: #c82333;
}

.modal-content button:first-child {
    background-color: #007bff; /* Switch to blue for cancel */
    color: white;
}

.modal-content button:first-child:hover {
    background-color: #0056b3;
}
</style>
