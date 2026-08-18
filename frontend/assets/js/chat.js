
(function () {
    const base = window.WL.baseUrl;
    const myId = window.WL.currentUserId;
    let lastMessageId = window.WL.lastMessageId || 0;

    const chatWindow = document.getElementById('chat-window');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');

    function appendMessage(m) {
        const bubble = document.createElement('div');
        bubble.className = 'wl-chat-bubble ' + (m.sender_id === myId ? 'mine' : 'theirs');
        bubble.textContent = m.message;
        chatWindow.appendChild(bubble);
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text) return;

        fetch(base + 'api/send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                appendMessage(data.message);
                lastMessageId = data.message.id;
                chatInput.value = '';
            } else {
                alert(data.error || 'Could not send message.');
            }
        });
    });

    function poll() {
        fetch(base + 'api/fetch_messages.php?since_id=' + lastMessageId)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                if (data.match_status !== 'active') {
                    window.location.href = base + 'frontend/pages/home.php';
                    return;
                }
                data.messages.forEach(m => {
                    appendMessage(m);
                    lastMessageId = m.id;
                });
            })
            .catch(() => {});
    }

    chatWindow.scrollTop = chatWindow.scrollHeight;
    setInterval(poll, 3000);
})();
