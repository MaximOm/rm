<h3 class="mt-2 mb-4">Send messege for us</h3>
<form id="contact-form" class="mt-2">
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" placeholder="Your name" required>
    </div>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" placeholder="Your email" required>
    </div>
    <div class="form-group">
        <label for="email">Phone:</label>
        <input type="tel" id="phone" name="phone" required placeholder="Your phone">
    </div>
    <div class="form-group">
        <label for="message">Message:</label>
        <textarea id="message" name="message" required placeholder="Your message"></textarea>
    </div>
    <button type="submit">Send</button>
</form>
<div id="status-message"></div>



<script>
    document.getElementById('contact-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm();
    });

    function submitForm() {
        var formData = new FormData(document.getElementById('contact-form'));

        fetch('/api/send-email', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                var statusMessage = document.getElementById('status-message');
                if (data.success) {
                    statusMessage.textContent = 'Message sent successfully!';
                    statusMessage.className = 'success';
                    document.getElementById('contact-form').reset();
                } else {
                    statusMessage.textContent = 'An error occurred while sending the message.';
                    statusMessage.className = 'error';
                }
                statusMessage.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                var statusMessage = document.getElementById('status-message');
                statusMessage.textContent = 'An error occurred while sending the message.';
                statusMessage.className = 'error';
                statusMessage.style.display = 'block';
            });
    }
</script>
