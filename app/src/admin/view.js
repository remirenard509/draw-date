export const view = {
    hideLoginForm() {
        document.getElementById('loginForm').style.display = 'none';
    },
    showUsers(users) {
        const userList = document.getElementById('user-list');
        userList.innerHTML = '';
        users.forEach(user => {
            const li = document.createElement('li');
            li.innerHTML = `
                ${user.username} ${user.email}
                <button class="activated" data-id="${user.id}" data-activated="${user.activated ? 1 : 0}">
                    ${user.activated == 1 ? 'Désactiver' : 'Activer'}
                </button>
                <button class="delete" data-id="${user.id}">Supprimer</button>
            `;
            userList.appendChild(li);
        });
    }
};
