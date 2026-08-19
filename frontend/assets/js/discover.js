/**
 * frontend/assets/js/discover.js
 * Laai een kandidaat op 'n slag en hanteer Like / Dislike / Skip.
 */
(function () {
    const base = window.WL.baseUrl;
    let currentCandidateId = null;
    let skipUsedFlag = false;
    let repeatSkipMode = false;
    let dislikeUndoUsedFlag = false;      // has the 1-time "go back" already been spent?

    const loadingState   = document.getElementById('loading-state');
    const noMoreState    = document.getElementById('no-more-state');
    const candidateState = document.getElementById('candidate-state');
    const skipBadge      = document.getElementById('skip-badge');
    const actionMessage  = document.getElementById('action-message');
    // const skipBtn        = document.getElementById('skip-btn'); // skip button disabled
    const nextBtn        = document.getElementById('next-btn');
    const likeBtn        = document.getElementById('like-btn');
    const dislikeBtn     = document.getElementById('dislike-btn');
    const rewindBtn      = document.getElementById('rewind-btn');

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
            span.className = 'appletag';
            span.textContent = h;
            hobbiesBox.appendChild(span);
        });

        document.getElementById('p-ie').value = c.personality.introvert_extrovert;
        document.getElementById('p-ac').value = c.personality.analytical_creative;
        document.getElementById('p-ts').value = c.personality.tech_savvy;
        document.getElementById('p-sp').value = c.personality.sporty;
    }

    function loadNext(targetId) {
        showState('loading');
        actionMessage.innerHTML = '';

        // targetId is only passed right after a successful "go back" - it re-opens
        // that one profile instead of pulling the next candidate in line.
        const url = targetId ? base + 'api/next_candidate.php?target_id=' + targetId
                              : base + 'api/next_candidate.php';

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    actionMessage.innerHTML = '<div class="uhohbox">' + (data.error || 'Something went wrong.') + '</div>';
                    showState('none');
                    return;
                }
                skipBadge.style.display = data.skip_used ? 'none' : '';
                dislikeUndoUsedFlag = !!data.dislike_undo_used;

                if (data.done) {
                    showState('none');
                } else {
                    renderCandidate(data.candidate);

                    const alreadyDecided = !!data.candidate.already_decided;
                    skipUsedFlag = !!data.skip_used;
                    repeatSkipMode = alreadyDecided && data.candidate.previous_action === 'skip';

                    if (repeatSkipMode) {
                        likeBtn.style.display = 'none';
                        dislikeBtn.style.display = '';
                        dislikeBtn.textContent = 'Skip';
                        // skipBtn.style.display = 'none'; // skip button disabled
                        nextBtn.style.display = 'none';
                        rewindBtn.style.display = 'none';
                    } else if (alreadyDecided) {
                        likeBtn.style.display = 'none';
                        dislikeBtn.style.display = 'none';
                        // skipBtn.style.display = 'none'; // skip button disabled
                        nextBtn.style.display = '';
                        rewindBtn.style.display = 'none';
                    } else {
                        likeBtn.style.display = '';
                        dislikeBtn.style.display = '';
                        nextBtn.style.display = 'none';
                        // skipBtn.style.display = skipUsedFlag ? 'none' : ''; // skip button disabled
                        // skipBtn.disabled = false; // skip button disabled
                        // skipBtn.textContent = 'Next (use 1-time skip)'; // skip button disabled
                        dislikeBtn.textContent = skipUsedFlag ? 'Skip' : 'Dislike';

                        // Only ever offered once per account - hide it for good once it's spent.
                        rewindBtn.style.display = dislikeUndoUsedFlag ? 'none' : '';
                    }
                    showState('candidate');
                }
            })
            .catch(() => {
                actionMessage.innerHTML = '<div class="uhohbox">Could not reach the server.</div>';
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
                actionMessage.innerHTML = '<div class="uhohbox">' + (data.error || 'Could not like this profile.') + '</div>';
            }
        });
    });

    // skip button disabled - handler kept for reference
    // document.getElementById('skip-btn').addEventListener('click', function () {
    //     if (!currentCandidateId) return;
    //     fetch(base + 'api/skip_action.php', {
    //         method: 'POST',
    //         headers: { 'Content-Type': 'application/json' },
    //         body: JSON.stringify({ target_id: currentCandidateId })
    //     })
    //     .then(r => r.json())
    //     .then(data => {
    //         if (data.success) {
    //             loadNext();
    //         } else {
    //             actionMessage.innerHTML = '<div class="uhohbox">' + (data.error || 'Could not skip.') + '</div>';
    //         }
    //     });
    // });

    // ---- Dislike vloei (maak die rede modal oop) ----
    const dislikeModalEl = document.getElementById('dislikeModal');
    const dislikeModal = new bootstrap.Modal(dislikeModalEl);

    document.getElementById('dislike-btn').addEventListener('click', function () {
        if (!currentCandidateId) return;

        if (repeatSkipMode) {
            // Reeds op hierdie persoon geskip - moenie weer 'n swipe-ry probeer maak nie, gaan net aan.
            loadNext();
            return;
        }

        if (skipUsedFlag) {
            // Skip is al gebruik - Dislike-knoppie tree nou op as 'n vinnige skip, geen rede nodig nie.
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
                    actionMessage.innerHTML = '<div class="uhohbox">' + (data.error || 'Could not skip.') + '</div>';
                }
            });
            return;
        }

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

    nextBtn.addEventListener('click', function () {
        loadNext();
    });

    // ---- Go back (1-time-per-account rewind to the last person you disliked) ----
    rewindBtn.addEventListener('click', function () {
        rewindBtn.disabled = true;

        fetch(base + 'api/undo_dislike_action.php', { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    loadNext(data.target_id);
                } else {
                    rewindBtn.disabled = false;
                    actionMessage.innerHTML = '<div class="uhohbox">' + (data.error || 'Could not go back.') + '</div>';
                }
            })
            .catch(() => {
                rewindBtn.disabled = false;
                actionMessage.innerHTML = '<div class="uhohbox">Could not reach the server.</div>';
            });
    });

    loadNext();
})();
