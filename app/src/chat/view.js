export const ChatView = {
    populateUserSelect(users, currentUserId) {
        const userSelect = document.getElementById('userSelect');
        userSelect.innerHTML = '<option value="">--Sélectionner un utilisateur--</option>';
        const uniqueUsers = users
            .filter(user => user.id != currentUserId)
            .filter((user, index, self) =>
                index === self.findIndex(u => u.id === user.id)
            );

        uniqueUsers.forEach(user => {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = user.username;
            userSelect.appendChild(option);
        });
    },

    displayMessages(messages, currentUserId, setAsReadCallback) {
        const contentDiv = document.getElementById('content');
        contentDiv.innerHTML = '';

        messages.forEach(msg => {
            const p = document.createElement('p');

            if (msg.content.includes('svg')) {
                const wrapper = document.createElement('div');
                wrapper.style.width = '300px';
                wrapper.style.height = '300px';
                wrapper.style.border = '1px solid #ccc';
                wrapper.style.margin = 'auto';
                wrapper.innerHTML = msg.content;
                p.appendChild(wrapper);
            } else {
                p.textContent = `${msg.username}: ${msg.content}`;
            }

            contentDiv.appendChild(p);

            if (msg.sender_id != currentUserId) {
                setAsReadCallback(msg.sender_id);
            }
        });

        document.querySelector('.messageAndDraw').style.visibility = 'visible';
    },

    displayProfil(data) {
        document.querySelector('#displayProfil').innerHTML = `
            ${data.username} ${data.bio}
            <img src="${data.avatar}" alt="Avatar de ${data.username}" width="100" height="100">
        `;
    }
};
