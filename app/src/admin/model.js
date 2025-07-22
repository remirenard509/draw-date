export const model = {
    async login(email, password) {
        const response = await fetch('/app/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        return response;
    },

    async getUsers() {
        const response = await fetch('/app/users', {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        });
        return response.ok ? await response.json() : [];
    },

    async toggleUserActivation(userId, activated) {
        return await fetch(`/app/user/${userId}/activated`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ activated })
        });
    },

    async deleteUser(userId) {
        return await fetch(`/app/user/${userId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
    }
};
