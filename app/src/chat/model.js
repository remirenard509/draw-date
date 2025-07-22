export const ChatModel = {
    async getMatches(userId, token) {
        const res = await fetch(`/app/match/${userId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        return res.ok ? await res.json() : [];
    },

    async getMessages(senderId, receiverId, token) {
        const res = await fetch(`/app/chat`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ sender_id: senderId, receiver_id: receiverId })
        });
        return res.ok ? await res.json() : [];
    },

    async sendMessage(senderId, receiverId, content, token) {
        return await fetch(`/app/send`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ sender_id: senderId, receiver_id: receiverId, content })
        });
    },

    async setMessageAsRead(userId, senderId, token) {
        return await fetch(`/app/messages/read/${userId}`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ sender_id: senderId })
        });
    },

    async getProfil(userId, token) {
        const res = await fetch(`/app/displayprofil/${userId}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        return res.ok ? await res.json() : [];
    }
};
