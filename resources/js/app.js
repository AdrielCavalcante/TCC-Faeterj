import './bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css'; // Importa o CSS do Bootstrap
import 'bootstrap'; 

import { createApp, onMounted, ref } from 'vue';

createApp({
    setup() {
        const message = ref('Hello, Vue!');
        const text1 = ref('');

        onMounted(() => {
            text1.value = 'Hello, Laravel!';
        });

        return {
            message,
            text1,
        };
    },
}).mount('#app');