<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tel-U Virtual Assistant</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .chat-container { width: 100%; max-width: 600px; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); overflow: hidden; display: flex; flex-direction: column; height: 80vh; }
        .chat-header { background: #b71c1c; color: #fff; padding: 15px 20px; text-align: center; font-weight: bold; font-size: 1.2rem; }
        .chat-box { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; }
        .message { max-width: 80%; padding: 12px 16px; border-radius: 18px; line-height: 1.4; font-size: 0.95rem; }
        .user-message { background-color: #e3f2fd; color: #0d47a1; align-self: flex-end; border-bottom-right-radius: 4px; }
        .bot-message { background-color: #f1f1f1; color: #333; align-self: flex-start; border-bottom-left-radius: 4px; white-space: pre-wrap; }
        .chat-input { display: flex; padding: 15px; background: #fff; border-top: 1px solid #eee; gap: 10px; }
        .chat-input input { flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 25px; outline: none; }
        .chat-input button { background: #b71c1c; color: white; border: none; padding: 10px 20px; border-radius: 25px; cursor: pointer; font-weight: bold; }
        .chat-input button:hover { background: #9a0007; }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        Tel-U Virtual Assistant 🤖
    </div>
    <div class="chat-box" id="chatBox">
        <div class="message bot-message">Halo! Ada yang bisa saya bantu terkait informasi kampus Telkom University?</div>
    </div>
    <div class="chat-input">
    <input type="text" id="userInput" class="form-control" placeholder="Tanyakan sesuatu..." onkeydown="if(event.key === 'Enter') sendMessage()">        <button onclick="sendMessage()">Kirim</button>
    </div>
</div>

<script>
    async function sendMessage() {
    const input = document.getElementById('userInput');
    const chatBox = document.getElementById('chatBox');
    const text = input.value.trim();

    if (!text) return;

    chatBox.innerHTML += `<div class="message user-message">${text}</div>`;
    input.value = '';
    chatBox.scrollTop = chatBox.scrollHeight;

    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'message bot-message';
    loadingDiv.id = 'loading';
    loadingDiv.innerText = 'Tel-U Bot sedang mengetik...';
    chatBox.appendChild(loadingDiv);
    chatBox.scrollTop = chatBox.scrollHeight;

    const formData = new FormData();
    formData.append('message', text);

    try {
        const res = await fetch('/chat/send', { 
            method: 'POST', 
            body: formData 
        });
        
        const rawText = await res.text();
        let data;
        
        try {
            data = JSON.parse(rawText);
        } catch (e) {
            throw new Error("Respon server bukan JSON: " + rawText.substring(0, 100));
        }

        const loader = document.getElementById('loading');
        if (loader) loader.remove();

        if (data.status === 'success') {
            chatBox.innerHTML += `<div class="message bot-message">${data.reply}</div>`;
        } else {
            chatBox.innerHTML += `<div class="message bot-message" style="color:red;">Gagal: ${data.message}</div>`;
        }
    } catch (err) {
        const loader = document.getElementById('loading');
        if (loader) loader.remove();
        chatBox.innerHTML += `<div class="message bot-message" style="color:red;">${err.message}</div>`;
    }
    
    chatBox.scrollTop = chatBox.scrollHeight;
    } 

</script>

</body>
</html>