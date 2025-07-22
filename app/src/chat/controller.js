import { ChatModel } from './model.js';
import { ChatView } from './view.js';

class ChatController {
    constructor(token) {
        this.token = token;
        this.id = localStorage.getItem('id');
        this.selectedUserId = null;

        this.userSelect = document.getElementById('userSelect');
        this.messageInput = document.getElementById('messageInput');
        this.sendButton = document.getElementById('sendButton');
        this.sendDraw = document.getElementById('sendDraw');

        this.addEventListeners();
        this.loadMatches();
    }

    addEventListeners() {
        this.userSelect.addEventListener('change', async () => {
            const userId = this.userSelect.value;
            if (userId) {
                this.selectedUserId = userId;
                await this.loadMessages();
                await this.loadProfil();
            }
        });

        this.sendButton.addEventListener('click', async () => {
            const message = this.messageInput.value.trim();
            if (message && this.selectedUserId) {
                await ChatModel.sendMessage(this.id, this.selectedUserId, message, this.token);
                this.messageInput.value = '';
                await this.loadMessages();
            }
        });

        this.sendDraw.addEventListener('click', async () => {
            const draw = localStorage.getItem('draw');
            if (draw && this.selectedUserId) {
                await ChatModel.sendMessage(this.id, this.selectedUserId, draw, this.token);
                await this.loadMessages();
            }
        });
    }

    async loadMatches() {
        const matches = await ChatModel.getMatches(this.id, this.token);
        ChatView.populateUserSelect(matches, this.id);
    }

    async loadMessages() {
        const messages = await ChatModel.getMessages(this.id, this.selectedUserId, this.token);
        ChatView.displayMessages(messages, this.id, (senderId) => {
            ChatModel.setMessageAsRead(this.id, senderId, this.token);
        });
    }

    async loadProfil() {
        const profil = await ChatModel.getProfil(this.selectedUserId, this.token);
        if (profil.length > 0) {
            ChatView.displayProfil(profil[0]);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const token = localStorage.getItem('token');
    if (token) {
        new ChatController(token);
    } else {
        window.location.href = '/login';
    }
});
