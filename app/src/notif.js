let lastMessageId = null;

function notifyNewMessage(msg) {
    const sound = document.getElementById('notifSound');
    if (sound) sound.play();

    const notif = document.createElement('div');
    notif.textContent = `💬 Nouveau message de ${msg.username}`;
    notif.className = 'notification-toast';
    document.body.appendChild(notif);

    setTimeout(() => notif.remove(), 5000);
}

function checkNewMessages() {
    let id = localStorage.getItem('id');
    let token = localStorage.getItem('token');
    fetch(`/app/messages/latest/${id}`,{
                method: 'GET'  ,
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            }
    )
        .then(res => res.json())
        .then(messages => {
            if (messages.length > 0) {
                const newest = messages[messages.length - 1];
                if (newest.id !== lastMessageId) {
                    lastMessageId = newest.id;
                    notifyNewMessage(newest);
                }
            }
        });
}

setInterval(checkNewMessages, 5000);
