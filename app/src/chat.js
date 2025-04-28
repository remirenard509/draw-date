class ChatApp {
    constructor(token) {
        this.token = token;
        this.id = localStorage.getItem('id');
        this.selectedUserId = null;
        this.message = "";

        this.userSelect = document.getElementById('userSelect');
        this.messageInput = document.getElementById('messageInput');
        this.sendButton = document.getElementById('sendButton');
        this.contentDiv = document.getElementById('content');

        this.addEventListeners();
        this.getUsers();
    }

    addEventListeners() {
        this.userSelect.addEventListener('change', () => {
            this.selectedUserId = this.userSelect.value;
            if (this.selectedUserId) {
               this.getMessages(this.selectedUserId);
            }
        });

        this.sendButton.addEventListener('click', () => {
            this.message = this.messageInput.value.trim();
            if (this.message && this.selectedUserId) {
                this.sendMessage(this.id,this.selectedUserId, this.message);
                this.messageInput.value = '';
            }  
        });
    }

    async sendMessage(senderId, receiverId, message) {
        try {
            const response = await fetch(`/app/send`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ sender_id: senderId, receiver_id: receiverId, content: message })
            });

            if (response.ok) {
                this.getMessages(receiverId);
            } else {
                console.error('Erreur lors de l\'envoi');
            }
        } catch (error) {
            console.error('Erreur réseau :', error);
        }
    }

    async getMessages(userId) {
        try {
            const response = await fetch(`/app/chat/${userId}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                }
            });
            if (response.ok) {
                const messages = await response.json();
                this.displayMessages(messages);
            } else {
                console.error('Erreur lors de la récupération des messages');
            }
        } catch (error) {
            console.error('Erreur réseau :', error);
        }
    }

    async getUsers() {
        try {
            const response = await fetch(`/app/username`, {
                method: 'GET'  ,
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.populateUserSelect(data);
            } else {
                console.error('Erreur lors de la récupération des utilisateurs');
            }
        } catch (error) {
            console.error('Erreur réseau :', error);
        }
    }

    populateUserSelect(users) {
        this.userSelect.innerHTML = '<option value="">--Sélectionner un utilisateur--</option>';

        users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = user.username;
            this.userSelect.appendChild(option);
        });
    }

    displayMessages(messages) {
        this.contentDiv.innerHTML = '';
        messages.forEach(msg => {
            const p = document.createElement('p');
            p.textContent = `${msg.username}: ${msg.content}`;
            this.contentDiv.appendChild(p);
        });
    }
}

window.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('token');
    if (token) {
        new ChatApp(token);
    } else {
        window.location.href = '/login';
    }
});