import { model } from './model.js';
import { view } from './view.js';

function bindEvents() {
    document.getElementById('logout').addEventListener('click', () => {
        localStorage.removeItem('token');
        window.location.href = '/app/src/login.html';
    });

    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const response = await model.login(email, password);
        if (response.ok) {
            const result = await response.json();
             // Vérifie si l'utilisateur est admin
            if (!result.admin) {
                alert("Accès réservé aux administrateurs.");
                return;
            }
            localStorage.setItem('token', result.token);
            view.hideLoginForm();
            loadUsers();
        } else {
            const error = await response.json();
            alert("Erreur : " + error.error);
        }
    });

    document.getElementById('user-list').addEventListener('click', async (e) => {
        const target = e.target;

        if (target.classList.contains('activated')) {
            const id = target.dataset.id;
            const current = parseInt(target.dataset.activated);
            await model.toggleUserActivation(id, current === 1 ? 0 : 1);
            loadUsers();
        }

        if (target.classList.contains('delete')) {
            const id = target.dataset.id;
            await model.deleteUser(id);
            loadUsers();
        }
    });
}

async function loadUsers() {
    const users = await model.getUsers();
    view.showUsers(users);
}

document.addEventListener('DOMContentLoaded', () => {
    bindEvents();
});
