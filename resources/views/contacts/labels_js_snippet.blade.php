<!-- JavaScript for Labels -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update color hex display
        const colorInput = document.getElementById('labelColor');
        const colorHex = document.getElementById('colorHex');

        colorInput.addEventListener('input', function() {
            colorHex.textContent = this.value;
        });

        // Open Label Modal
        const labelModal = document.getElementById('labelModal');
        labelModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const contactId = button.getAttribute('data-id');
            const labelText = button.getAttribute('data-text') || '';
            const labelColor = button.getAttribute('data-color') || '#0d6efd';

            document.getElementById('labelContactId').value = contactId;
            document.getElementById('labelText').value = labelText;
            document.getElementById('labelColor').value = labelColor;
            document.getElementById('colorHex').textContent = labelColor;
        });

        // Handle Label Form Submit
        const labelForm = document.getElementById('labelForm');
        labelForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const contactId = document.getElementById('labelContactId').value;
            const text = document.getElementById('labelText').value;
            const color = document.getElementById('labelColor').value;
            const modal = bootstrap.Modal.getInstance(labelModal);

            // Disable submit button
            const submitBtn = labelForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جاري الحفظ...';

            fetch(`/contacts/${contactId}/label`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        label_text: text,
                        label_color: color
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modal.hide();

                        // Update UI
                        updateLabelUI(contactId, data.label_text, data.label_color);

                        // Show toast
                        // If you have a toast function, use it. Otherwise uses simple alert or relies on UI update.
                        // Assuming Toaster or similar exists, or just minimal feedback.
                    } else {
                        alert(data.message || 'حدث خطأ ما');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ في الاتصال');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
        });

        function updateLabelUI(contactId, text, color) {
            // Update Desktop Row
            const desktopLabelContainer = document.querySelector(
                `.contact-row[data-id="${contactId}"] .label-container`);
            const editBtn = document.querySelector(`.btn-label[data-id="${contactId}"]`);

            if (text) {
                // Update badge
                if (desktopLabelContainer) {
                    desktopLabelContainer.innerHTML =
                        `<span class="badge rounded-pill" style="background-color: ${color}; font-size: 0.8rem;">${text}</span>`;
                }
                // Update button data attributes
                if (editBtn) {
                    editBtn.setAttribute('data-text', text);
                    editBtn.setAttribute('data-color', color);
                }
            } else {
                // Remove badge
                if (desktopLabelContainer) {
                    desktopLabelContainer.innerHTML = '';
                }
                if (editBtn) {
                    editBtn.setAttribute('data-text', '');
                    editBtn.setAttribute('data-color', '#0d6efd');
                }
            }

            // Update Mobile Card
            const mobileCard = document.querySelector(`.contact-card[data-id="${contactId}"]`);
            if (mobileCard) {
                const mobileLabelContainer = mobileCard.querySelector('.mobile-label-container');
                const mobileEditBtn = mobileCard.querySelector(`.btn-label-mobile[data-id="${contactId}"]`);

                if (text) {
                    if (mobileLabelContainer) {
                        mobileLabelContainer.innerHTML =
                            `<span class="badge rounded-pill" style="background-color: ${color};">${text}</span>`;
                    }
                    if (mobileEditBtn) {
                        mobileEditBtn.setAttribute('data-text', text);
                        mobileEditBtn.setAttribute('data-color', color);
                    }
                } else {
                    if (mobileLabelContainer) {
                        mobileLabelContainer.innerHTML = '';
                    }
                    if (mobileEditBtn) {
                        mobileEditBtn.setAttribute('data-text', '');
                        mobileEditBtn.setAttribute('data-color', '#0d6efd');
                    }
                }
            }
        }
    });
</script>
