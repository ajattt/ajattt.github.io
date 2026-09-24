/**
 * Public Website JavaScript
 * SMK Bangun Nusa Bangsa
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Toggle Anonim pada Form Komentar
    const anonCheckbox = document.getElementById('is_anonymous');
    const identityFields = document.getElementById('identity-fields');
    const nameInput = document.getElementById('comment_name');
    const emailInput = document.getElementById('comment_email');
    const anonAlert = document.getElementById('anon-info-alert');

    if (anonCheckbox && identityFields) {
        function toggleAnonymousState() {
            if (anonCheckbox.checked) {
                // Sembunyikan field nama & email atau disable
                if (identityFields) {
                    identityFields.style.opacity = '0.5';
                }
                if (nameInput) {
                    nameInput.required = false;
                    nameInput.disabled = true;
                    nameInput.dataset.oldVal = nameInput.value;
                    nameInput.value = 'Anonim';
                }
                if (emailInput) {
                    emailInput.required = false;
                    emailInput.disabled = true;
                    emailInput.dataset.oldVal = emailInput.value;
                    emailInput.value = '';
                }
                if (anonAlert) {
                    anonAlert.classList.remove('d-none');
                }
            } else {
                if (identityFields) {
                    identityFields.style.opacity = '1';
                }
                if (nameInput) {
                    nameInput.disabled = false;
                    nameInput.required = true;
                    nameInput.value = nameInput.dataset.oldVal || '';
                }
                if (emailInput) {
                    emailInput.disabled = false;
                    emailInput.required = true;
                    emailInput.value = emailInput.dataset.oldVal || '';
                }
                if (anonAlert) {
                    anonAlert.classList.add('d-none');
                }
            }
        }

        anonCheckbox.addEventListener('change', toggleAnonymousState);
        // Inisialisasi awal
        toggleAnonymousState();
    }

    // 2. Reply Comment Button Handler
    const replyButtons = document.querySelectorAll('.btn-reply-comment');
    const parentIdInput = document.getElementById('parent_comment_id');
    const replyingToBox = document.getElementById('replying-to-indicator');
    const replyingToName = document.getElementById('replying-to-name');
    const cancelReplyBtn = document.getElementById('btn-cancel-reply');

    if (replyButtons.length > 0 && parentIdInput) {
        replyButtons.forEach(button => {
            button.addEventListener('click', function () {
                const commentId = this.dataset.commentId;
                const authorName = this.dataset.authorName;

                parentIdInput.value = commentId;
                if (replyingToName) replyingToName.textContent = authorName;
                if (replyingToBox) replyingToBox.classList.remove('d-none');

                // Scroll to comment form
                const formSection = document.getElementById('comment-form-section');
                if (formSection) {
                    formSection.scrollIntoView({ behavior: 'smooth' });
                    const commentTextArea = document.getElementById('comment_content');
                    if (commentTextArea) commentTextArea.focus();
                }
            });
        });

        if (cancelReplyBtn) {
            cancelReplyBtn.addEventListener('click', function () {
                parentIdInput.value = '';
                if (replyingToBox) replyingToBox.classList.add('d-none');
            });
        }
    }

    // 3. Auto dismiss alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // 4. Back to top button
    const backToTopBtn = document.getElementById('backToTopBtn');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) {
                backToTopBtn.classList.remove('d-none');
            } else {
                backToTopBtn.classList.add('d-none');
            }
        });
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});
