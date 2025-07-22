<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { toast } from 'vue3-toastify';

import PayloadView from './PayloadView.vue';

const diffPayloadJson = ref()
const firstPayloadJson = ref(null);
const secondPayloadJson = ref(null);

onMounted(async () => {
    const firstPayload = await fetch('/data/firstPayload.json');
    const secondPayload = await fetch('/data/secondPayload.json');

    firstPayloadJson.value = await firstPayload.json();
    secondPayloadJson.value = await secondPayload.json();
});

const submitPayload = async () => {
    toast("First Payload has been sent.", {
        autoClose: 250,
        position: "top-center",
    });
    const confirmationForFirstPayload = await axios.post('/api/compare-payloads', { payload: firstPayloadJson.value });

    if (confirmationForFirstPayload.data.hasOnlyOneItem) {
        toast("Second Payload has been sent.", {
            autoClose: 250,
            position: "top-center",
        });
        const diffPayload = await axios.post('/api/compare-payloads', { payload: secondPayloadJson.value });

        diffPayloadJson.value = diffPayload.data.diff.payload.children;
    }
}
</script>


<template>
    <div class="min-h-screen flex flex-col items-center justify-start bg-gray-50 p-6">
        <div class="w-full max-w-90/100">
            <div class="text-center mb-8">
                <h1 class="font-semibold text-2xl mb-4">Payload Comparison Tool</h1>
                <button @click="submitPayload"
                    class="cursor-pointer bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-700 transition">
                    Compare Payloads
                </button>

                <div v-if="diffPayloadJson" class="p-4">
                    <div class="flex gap-1">
                        <div>
                            <div class="font-semibold text-lg mb-2">First Payload</div>
                            <div class="flex-1 border border-gray-300 rounded p-4 bg-white shadow-sm overflow-auto">
                                <PayloadView :data="firstPayloadJson" :diffs="diffPayloadJson" :isSecond="false"
                                    :level="1" />

                            </div>
                        </div>
                        <div>
                            <div class="font-semibold text-lg mb-2">Second Payload</div>
                            <div class="flex-1 border border-gray-300 rounded p-4 bg-white shadow-sm overflow-auto">
                                <PayloadView :data="secondPayloadJson" :diffs="diffPayloadJson" :isSecond="true"
                                    :level="1" />

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
