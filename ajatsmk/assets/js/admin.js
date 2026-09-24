/**
 * Admin Dashboard JavaScript
 * SMK Bangun Nusa Bangsa
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Sidebar Toggle
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const adminSidebar = document.querySelector('.admin-sidebar');

    if (sidebarToggleBtn && adminSidebar) {
        sidebarToggleBtn.addEventListener('click', function () {
            adminSidebar.classList.toggle('show');
        });

        // Close sidebar when clicked outside on mobile
        document.addEventListener('click', function (event) {
            if (window.innerWidth < 992) {
                if (!adminSidebar.contains(event.target) && !sidebarToggleBtn.contains(event.target)) {
                    adminSidebar.classList.remove('show');
                }
            }
        });
    }

    // 2. Auto Slug Generator (Articles & Categories)
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    if (titleInput && slugInput) {
        let isSlugManuallyEdited = slugInput.value.trim() !== '';

        titleInput.addEventListener('input', function () {
            if (!isSlugManuallyEdited) {
                slugInput.value = generateSlug(this.value);
            }
        });

        slugInput.addEventListener('input', function () {
            isSlugManuallyEdited = this.value.trim() !== '';
        });
    }

    function generateSlug(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')           // Ganti spasi dengan -
            .replace(/[^\w\-]+/g, '')       // Hapus karakter non-word
            .replace(/\-\-+/g, '-')         // Ganti beberapa - dengan satu -
            .replace(/^-+/, '')             // Trim - dari awal
            .replace(/-+$/, '');            // Trim - dari akhir
    }

    // 3. Image Upload Live Preview
    const imageInput = document.getElementById('thumbnail_input');
    const previewContainer = document.getElementById('thumbnail_preview_container');
    const previewImage = document.getElementById('thumbnail_preview_img');

    if (imageInput && previewImage) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    if (previewContainer) previewContainer.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 4. Auto dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });
});
