<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import { useRoute } from 'vue-router';
import { axiosInstance } from '@/plugins/axios';

const route = useRoute();
const isOpen = ref(false);
const message = ref('');
const messages = ref([
    { role: 'assistant', content: 'Halo! Saya asisten AI Saffnco. Ada yang bisa saya bantu hari ini?' }
]);
const isLoading = ref(false);
const chatContainer = ref(null);

const isCreateTicketPage = computed(() => route.name === 'app.ticket.create');

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        nextTick(() => {
            scrollToBottom();
        });
    }
};

const scrollToBottom = () => {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const sendMessage = async () => {
    if (!message.value.trim() || isLoading.value) return;

    const userMessage = message.value;
    messages.value.push({ role: 'user', content: userMessage });
    message.value = '';
    isLoading.value = true;

    nextTick(scrollToBottom);

    try {
        const response = await axiosInstance.post('/chat', {
            message: userMessage,
            history: messages.value.slice(0, -1)
        });

        messages.value.push({ role: 'assistant', content: response.data.response });
    } catch (error) {
        console.error('AI Error:', error);
        messages.value.push({ 
            role: 'assistant', 
            content: 'Maaf, terjadi kesalahan saat menghubungi AI. Pastikan API key sudah terpasang dengan benar.' 
        });
    } finally {
        isLoading.value = false;
        nextTick(() => {
            scrollToBottom();
        });
    }
};
</script>

<template>
    <div :class="[
        'fixed bottom-8 left-8 z-[9999] flex flex-col items-start text-left transition-opacity duration-300',
        isCreateTicketPage ? 'hidden lg:flex' : 'flex'
    ]">
        <!-- Chat Window -->
        <div v-if="isOpen" 
             v-motion
             :initial="{ opacity: 0, y: 50, scale: 0.9 }"
             :enter="{ opacity: 1, y: 0, scale: 1 }"
             class="mb-4 w-80 sm:w-96 h-[550px] bg-white rounded-2xl shadow-2xl border border-gray-100 flex flex-col overflow-hidden">
            
            <!-- Header -->
            <div class="p-5 bg-gradient-to-r from-blue-600 to-blue-700 text-white flex justify-between items-center shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-md border border-white/30">
                        <!-- Sparkles Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor" class="text-white"><path d="M19,9l1.25-2.75L23,5l-2.75-1.25L19,1l-1.25,2.75L15,5l2.75,1.25L19,9z M11.5,9.5L9,4L6.5,9.5L1,12l5.5,2.5L9,20l2.5-5.5l5.5-2.5L11.5,9.5z M19,15l-1.25,2.75L15,19l2.75,1.25L19,23l1.25-2.75L23,19l-2.75-1.25L19,15z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base tracking-wide leading-tight">SAFF & Co. AI</h3>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse shadow-[0_0_8px_rgba(74,222,128,0.5)]"></span>
                            <span class="text-[11px] text-blue-100 uppercase tracking-widest font-semibold">Online</span>
                        </div>
                    </div>
                </div>
                <button @click="isOpen = false" class="hover:bg-white/10 p-2 rounded-xl transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <!-- Messages Area -->
            <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4 scroll-smooth bg-gray-50/50">
                <div v-for="(msg, index) in messages" :key="index" 
                     :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']">
                    <div :class="[
                        'max-w-[85%] px-4 py-3 rounded-2xl text-[13px] leading-relaxed shadow-sm transition-all duration-300',
                        msg.role === 'user' 
                            ? 'bg-blue-600 text-white rounded-tr-none' 
                            : 'bg-white text-gray-800 rounded-tl-none border border-gray-100'
                    ]">
                        {{ msg.content }}
                    </div>
                </div>
                <!-- Loading Indicator -->
                <div v-if="isLoading" class="flex justify-start">
                    <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none border border-gray-100 flex gap-1.5 items-center">
                        <span class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce"></span>
                        <span class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce [animation-delay:0.2s]"></span>
                        <span class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-bounce [animation-delay:0.4s]"></span>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-4 bg-white border-t border-gray-100">
                <form @submit.prevent="sendMessage" class="flex items-center gap-2">
                    <input v-model="message" 
                           type="text" 
                           placeholder="Tanyakan sesuatu..." 
                           class="flex-1 bg-gray-100 border-none rounded-2xl px-5 py-3.5 text-sm focus:ring-2 focus:ring-blue-500/10 transition-all outline-none"
                           :disabled="isLoading">
                    <button type="submit" 
                            :disabled="isLoading"
                            class="bg-blue-600 hover:bg-blue-700 text-white w-12 h-12 min-w-[48px] flex items-center justify-center rounded-2xl transition-all shadow-[0_4px_15px_rgba(37,99,235,0.3)] active:scale-95 disabled:opacity-50 disabled:active:scale-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white ml-0.5"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </form>
            </div>
        </div>

        <button @click="isOpen = !isOpen" 
                v-motion
                :initial="{ scale: 0.8, opacity: 0 }"
                :enter="{ scale: 1, opacity: 1 }"
                :hovered="{ scale: 1.1 }"
                :tapped="{ scale: 0.9 }"
                class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-[0_8px_25px_rgba(37,99,235,0.4)] hover:shadow-blue-500/60 transition-all">
            <svg v-if="!isOpen" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="currentColor" class="text-white"><path d="M19,9l1.25-2.75L23,5l-2.75-1.25L19,1l-1.25,2.75L15,5l2.75,1.25L19,9z M11.5,9.5L9,4L6.5,9.5L1,12l5.5,2.5L9,20l2.5-5.5l5.5-2.5L11.5,9.5z M19,15l-1.25,2.75L15,19l2.75,1.25L19,23l1.25-2.75L23,19l-2.75-1.25L19,15z"/></svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
</template>

<style scoped>
/* Custom scrollbar for chat */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
