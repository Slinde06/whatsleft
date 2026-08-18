
(function () {
    const base = window.WL.baseUrl;
    let currentCandidateId = null;

    const loadingState   = document.getElementById('loading-state');
    const noMoreState    = document.getElementById('no-more-state');
    const candidateState = document.getElementById('candidate-state');
    const skipBadge      = document.getElementById('skip-badge');
    const actionMessage  = document.getElementById('action-message');
    const skipBtn        = document.getElementById('skip-btn');

    function showState(name) {
        loadingState.style.display = name === 'loading' ? '' : 'none';
        noMoreState.style.display = name === 'none' ? '' : 'none';
        candidateState.style.display = name === 'candidate' ? '' : 'none';
    }

    function renderCandidate(c) {
        currentCandidateId = c.id;
        document.getElementById('c-photo').src = c.photo_url;
        document.getElementById('c-name').textContent = c.first_name + (c.age ? ', ' + c.age : '');
        document.getElementById('c-location').textContent = c.location || '';
        document.getElementById('c-description').textContent = c.description || '';

        const hobbiesBox = document.getElementById('c-hobbies');
        hobbiesBox.innerHTML = '';
        (c.hobbies || []).forEach(h => {
            const span = document.createElement('span');
            span.className = 'wl-tag';
            span.textContent = h;
            hobbiesBox.appendChild(span);
        });

        document.getElementById('p-ie').value = c.personality.introvert_extrovert;
        document.getElementById('p-ac').value = c.personality.analytical_creative;
        document.getElementById('p-ts').value = c.personality.tech_savvy;
        document.getElementById('p-sp').value = c.personality.sporty;
    }

    function loadNext() {
        showState('loading');
        actionMessage.innerHTML = '';
        fetch(base + 'api/next_candidate.php')
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    actionMessage.innerHTML = '<div class="wl-alert-error">' + (data.error || 'Something went wrong.') + '</div>';
                    showState('none');
                    return;
                }
                skipBadge.style.display = data.skip_used ? 'none' : '';
                if (data.done) {
                    showState('none');
                } else {
                    renderCandidate(data.candidate);
                    skipBtn.disabled = !!data.skip_used;
                    skipBtn.textContent = data.skip_used ? 'Skip already used' : 'Next (use 1-time skip)';
                    showState('candidate');
                }
            })
            .catch(() => {
                actionMessage.innerHTML = '<div class="wl-alert-error">Could not reach the server.</div>';
                showState('none');
            });
    }

    document.getElementById('like-btn').addEventListener('click', function () {
        if (!currentCandidateId) return;
        fetch(base + 'api/like_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ target_id: currentCandidateId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.matched) {
                window.location.href = base + 'frontend/pages/match_space.php';
            } else {
                actionMessage.innerHTML = '<div class="wl-alert-error">' + (data.error || 'Could not like this profile.') + '</div>';
            }
        });
    });

    document.getElementById('skip-btn').addEventListener('click', function () {
        if (!currentCandidateId) return;
        fetch(base + 'api/skip_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ target_id: currentCandidateId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadNext();
            } else {
                actionMessage.innerHTML = '<div class="wl-alert-error">' + (data.error || 'Could not skip.') + '</div>';
            }
        });
    });


    const dislikeModalEl = document.getElementById('dislikeModal');
    const dislikeModal = new bootstrap.Modal(dislikeModalEl);

    document.getElementById('dislike-btn').addEventListener('click', function () {
        document.getElementById('reason-short').value = '';
        document.getElementById('reason-long').value = '';
        document.getElementById('reason-error').style.display = 'none';
        dislikeModal.show();
    });

    document.getElementById('confirm-dislike-btn').addEventListener('click', function () {
        const reasonShort = document.getElementById('reason-short').value.trim();
        const reasonLong = document.getElementById('reason-long').value.trim();
        const errorBox = document.getElementById('reason-error');

        if (!reasonShort || !reasonLong) {
            errorBox.textContent = 'Both a short and a detailed reason are required.';
            errorBox.style.display = '';
            return;
        }

        fetch(base + 'api/dislike_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ target_id: currentCandidateId, reason_short: reasonShort, reason_long: reasonLong })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                dislikeModal.hide();
                loadNext();
            } else {
                errorBox.textContent = data.error || 'Could not save that reason.';
                errorBox.style.display = '';
            }
        });
    });

    loadNext();
})();
