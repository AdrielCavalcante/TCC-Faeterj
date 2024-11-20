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

document.addEventListener('DOMContentLoaded', () => {
    
    darkTheme();
    adjustFooter();
    window.addEventListener('resize', adjustFooter);
    filtrarContatos();

    function darkTheme() {
        let sun = document.getElementById('sun');
        let moon = document.getElementById('moon');

        const themeColor = localStorage.getItem('theme-color');

        if(themeColor === 'dark') {
            document.documentElement.classList.add('dark-mode');
            moon.style.display = 'none';
            sun.style.display = 'block';
        } else {
            document.documentElement.classList.remove('dark-mode');
            sun.style.display = 'none';
            moon.style.display = 'block';
        }

        sun.addEventListener('click', () => {
            document.documentElement.classList.remove('dark-mode');
            if(localStorage.getItem('theme-color')) {
                localStorage.removeItem('theme-color');
            }
            sun.style.display = 'none';
            moon.style.display = 'block';
        });

        moon.addEventListener('click', () => {
            document.documentElement.classList.add('dark-mode');
            localStorage.setItem('theme-color', 'dark');
            moon.style.display = 'none';
            sun.style.display = 'block';
        });
    }
        
    function adjustFooter() {
        const footer = document.querySelector('footer');
        const hasScroll = document.documentElement.scrollHeight > document.documentElement.clientHeight;

        if (hasScroll) {
            footer.style.position = 'static'; // Remove o position fixed
        } else {
            footer.style.position = 'fixed'; // Reaplica o position fixed
            footer.style.bottom = '0';       // Certifica que está na parte inferior
            footer.style.width = '100%';     // Garante que ocupa a largura total
        }
    }

    function filtrarContatos() {
        const searchInput = document.getElementById('userSearchInput');
        const chatUsers = document.querySelectorAll('.chatUser');

        if(!searchInput) {
            return;
        }

        searchInput.addEventListener('input', () => {
            const searchValue = searchInput.value.toLowerCase();

            chatUsers.forEach(user => {

                const userName = user.getAttribute('data-name').toLowerCase();
                const userEmail = user.getAttribute('data-email').toLowerCase();

                // Exibe o usuário se o nome ou e-mail corresponder à busca
                if (userName.includes(searchValue) || userEmail.includes(searchValue)) {
                    user.style.display = 'flex';
                } else {
                    user.style.display = 'none';
                }
            });
        });
    }
});