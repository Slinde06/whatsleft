
(function () {
    const base = window.WL.baseUrl;


    const hobbyInput = document.getElementById('hobby-input');
    const hobbyTags = document.getElementById('hobby-tags');

    function addHobbyTag(text) {
        text = text.trim();
        if (!text) return;
        const existing = Array.from(hobbyTags.querySelectorAll('[data-hobby]')).map(el => el.dataset.hobby.toLowerCase());
        if (existing.includes(text.toLowerCase())) return;

        const span = document.createElement('span');
        span.className = 'wl-tag';
        span.dataset.hobby = text;
        span.innerHTML = text + ' <a href="#" class="remove-hobby text-danger text-decoration-none">&times;</a>';
        hobbyTags.appendChild(span);
    }

    hobbyInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addHobbyTag(hobbyInput.value);
            hobbyInput.value = '';
        }
    });

    hobbyTags.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-hobby')) {
            e.preventDefault();
            e.target.closest('[data-hobby]').remove();
        }
    });

    function getHobbies() {
        return Array.from(hobbyTags.querySelectorAll('[data-hobby]')).map(el => el.dataset.hobby);
    }

    const photoGrid = document.getElementById('photo-grid');

    function bindRemoveButtons() {
        photoGrid.querySelectorAll('.remove-photo').forEach(btn => {
            btn.onclick = function (e) {
                e.preventDefault();
                const wrap = btn.closest('.photo-thumb-wrap');
                const photoId = wrap.dataset.photoId;
                fetch(base + 'api/delete_photo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ photo_id: photoId })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        wrap.remove();
                        ensureAddSlot();
                    } else {
                        alert(data.error || 'Could not remove photo.');
                    }
                });
            };
        });
    }

    function ensureAddSlot() {
        const count = photoGrid.querySelectorAll('.photo-thumb-wrap').length;
        let slot = document.getElementById('add-photo-slot');
        if (count < 6 && !slot) {
            slot = document.createElement('div');
            slot.className = 'col-3';
            slot.id = 'add-photo-slot';
            slot.innerHTML = '<label for="photo-upload" class="wl-photo-thumb d-flex align-items-center justify-content-center" style="cursor:pointer;border:2px dashed var(--white);background:transparent;">+ ADD</label>';
            photoGrid.appendChild(slot);
        }
        if (count >= 6 && slot) {
            slot.remove();
        }
    }

    document.body.addEventListener('change', function (e) {
        if (e.target.id !== 'photo-upload') return;
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('photo', file);

        fetch(base + 'api/upload_photo.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const wrap = document.createElement('div');
                    wrap.className = 'col-3 photo-thumb-wrap';
                    wrap.dataset.photoId = data.photo_id;
                    wrap.innerHTML = '<img class="wl-photo-thumb" src="' + data.photo_url + '" alt="Profile photo">' +
                                      '<a href="#" class="remove-photo small text-white d-block text-center">Remove</a>';
                    photoGrid.insertBefore(wrap, document.getElementById('add-photo-slot'));
                    bindRemoveButtons();
                    ensureAddSlot();


                    const primaryImg = document.getElementById('primary-photo-img');
                    if (photoGrid.querySelectorAll('.photo-thumb-wrap').length === 1) {
                        primaryImg.src = data.photo_url;
                    }
                } else {
                    alert(data.error || 'Could not upload photo.');
                }
                e.target.value = '';
            });
    });

    bindRemoveButtons();

    document.getElementById('save-changes-btn').addEventListener('click', function () {
        const payload = {
            first_name: document.getElementById('first_name').value,
            last_name: document.getElementById('last_name').value,
            email: document.getElementById('email').value,
            looking_for: document.getElementById('looking_for').value,
            location: document.getElementById('location').value,
            description: document.getElementById('description').value,
            hobbies: getHobbies(),
            personality: {
                introvert_extrovert: document.getElementById('slider-ie').value,
                analytical_creative: document.getElementById('slider-ac').value,
                tech_savvy: document.getElementById('slider-ts').value,
                sporty: document.getElementById('slider-sp').value
            }
        };

        const msgBox = document.getElementById('save-message');

        fetch(base + 'api/update_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                msgBox.innerHTML = '<div class="wl-alert-success">Changes saved.</div>';
                if (window.WL.welcome) {
                    setTimeout(function () { window.location.href = base + 'frontend/pages/home.php'; }, 900);
                }
            } else {
                msgBox.innerHTML = '<div class="wl-alert-error">' + (data.error || 'Could not save changes.') + '</div>';
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
})();
