class ChatApp {
    constructor(token) {
        this.token = token;
        this.id = localStorage.getItem('id');
        this.selectedUserId = null;
        this.message = "";
        this.draw = "";

        this.userSelect = document.getElementById('userSelect');
        this.messageInput = document.getElementById('messageInput');
        this.sendButton = document.getElementById('sendButton');
        this.contentDiv = document.getElementById('content');
        this.sendDraw = document.getElementById('sendDraw');
        this.userProfil = document.getElementById('userprofil');

        this.addEventListeners();
        this.getMatch();
    }

    addEventListeners() {
        this.userSelect.addEventListener('change', () => {
            this.selectedUserId = this.userSelect.value;
            if (this.selectedUserId) {
               this.getMessages(this.id, this.selectedUserId);
               this.fetchProfil();
            }
        });

        this.sendButton.addEventListener('click', () => {
            this.message = this.messageInput.value.trim();
            if (this.message && this.selectedUserId) {
                this.sendMessage(this.id,this.selectedUserId, this.message);
                this.messageInput.value = '';
            }  
        });
        this.sendDraw.addEventListener('click', () => {
            this.draw = localStorage.getItem('draw');
            if (this.draw && this.selectedUserId) {
            this.sendMessage(this.id,this.selectedUserId, this.draw);
            }
        });
    }

    async fetchProfil() {
        try {
            const response = await fetch(`/app/displayprofil/${this.selectedUserId}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.displayProfil(data[0]);
            }
        } catch (error) {
            console.error('Erreur réseau :', error);
        }
    }

    displayProfil(data) {
        document.querySelector('#displayProfil').innerHTML = `
        ${data.username} ${data.bio}
        <img src="${data.avatar}" alt="Avatar de ${data.username}" width="100" height="100">
    `;
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
                this.getMessages(receiverId, senderId);
            } else {
                console.error('Erreur lors de l\'envoi');
            }
        } catch (error) {
            console.error('Erreur réseau :', error);
        }
    }

    async getMatch() {
        try {
            const response = await fetch(`/app/match/${this.id}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                }
            });
            if (response.ok) {
                const data = await response.json();
                this.populateUserSelect(data);

            } else {
                console.error('Erreur lors de la récupération des matchs');
            }
        } catch (error) {
            console.error('Erreur réseau :', error);
        }
    }

    async getMessages(receiverId, senderId) {
        try {
            const response = await fetch(`/app/chat`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                },
                body : JSON.stringify({sender_id: senderId, receiver_id: receiverId})
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
        const uniqueUsers = users
        .filter(user => user.id != this.id)
        .filter((user, index, self) =>
            index === self.findIndex(u => u.id === user.id)
        );
        
        uniqueUsers.forEach(user => {
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
            if (msg.content.includes('svg')){
                p.innerHTML = msg.content; 

            } else {
            p.textContent = `${msg.username}: ${msg.content}`;
            }
            this.contentDiv.appendChild(p);
            this.setMessageAsRead(msg.sender_id);
        });
        document.querySelector(".messageAndDraw").style.visibility = "visible";
        
    }

    async setMessageAsRead(senderId) {
        try {
            const response = await fetch(`/app/messages/read/${this.id}`, {
                method: 'PATCH'  ,
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ sender_id: senderId})
            });

            if (response.ok) {
            } else {
                console.error('Erreur messsage not set as read');
            }
        } catch (error) {
            console.error('Erreur réseau :', error);
        }
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