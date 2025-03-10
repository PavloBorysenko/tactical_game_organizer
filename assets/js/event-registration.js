document.addEventListener('DOMContentLoaded', function () {
    // Function to update the participants list and role selector
    function updateParticipantsList(list) {
        const eventId = list.dataset.eventId;
        if (!eventId) return Promise.reject('No event ID found');

        return wp
            .apiFetch({
                path: `/tactical-game-organizer/v1/events/${eventId}/participants`,
            })
            .then(function (response) {
                const participants = response.participants;
                const maxParticipants = response.max_participants;
                const currentCount = response.current_count;
                const allowedRoles = response.allowed_roles || {};
                const defaultRole = response.default_role || 'assault';

                // Update role selector with allowed roles
                const roleSelect = document.querySelector(
                    'select[name="role"]'
                );
                if (roleSelect) {
                    // Clear existing options
                    roleSelect.innerHTML = '';

                    // Add default option
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = window.tgo_rest.select_role;
                    roleSelect.appendChild(defaultOption);

                    // Add allowed roles, ensuring 'assault' is always first
                    if (allowedRoles['assault']) {
                        const assaultOption = document.createElement('option');
                        assaultOption.value = 'assault';
                        assaultOption.textContent = allowedRoles['assault'];
                        roleSelect.appendChild(assaultOption);
                    }

                    // Add other allowed roles
                    Object.entries(allowedRoles).forEach(([value, label]) => {
                        if (value !== 'assault') {
                            const option = document.createElement('option');
                            option.value = value;
                            option.textContent = label;
                            roleSelect.appendChild(option);
                        }
                    });

                    // Set default role
                    if (defaultRole && allowedRoles[defaultRole]) {
                        roleSelect.value = defaultRole;
                    } else if (allowedRoles['assault']) {
                        roleSelect.value = 'assault';
                    }
                }

                if (!participants.length) {
                    list.innerHTML =
                        '<p>' + window.tgo_rest.no_participants + '</p>';
                    return;
                }

                let html = '<h3>' + window.tgo_rest.participants_title;
                if (maxParticipants > 0) {
                    html += ` (${currentCount}/${maxParticipants})`;
                    if (currentCount >= maxParticipants) {
                        html +=
                            ' <span class="tgo-event-full-text">' +
                            window.tgo_rest.event_full_inline +
                            '</span>';
                    }
                }
                html += '</h3>';
                html += '<table><thead><tr>';
                html +=
                    '<th>' + (window.tgo_rest.callsign || 'Callsign') + '</th>';
                html += '<th>' + (window.tgo_rest.role || 'Role') + '</th>';
                html += '<th>' + (window.tgo_rest.team || 'Team') + '</th>';
                html += '<th></th>'; // For cancel button
                html += '</tr></thead><tbody>';

                participants.forEach(function (participant) {
                    html += '<tr class="tgo-participant">';
                    html += `<td>${participant.callsign}</td>`;
                    html += `<td>${participant.role_label}</td>`;
                    html += `<td>${participant.team}</td>`;
                    html += '<td>';
                    if (participant.can_cancel) {
                        html += `<button class="tgo-cancel-registration">❌</button>`;
                    }
                    html += '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table>';

                // If there is a limit on the number of participants, show progress
                if (maxParticipants > 0) {
                    const percentage = (currentCount / maxParticipants) * 100;
                    const isFull = currentCount >= maxParticipants;
                    html += `<div class="tgo-progress-bar${
                        isFull ? ' full' : ''
                    }">
                              <div class="tgo-progress" style="width: ${percentage}%"></div>
                            </div>`;
                }

                list.innerHTML = html;

                // If no more spots available, hide registration form and show message
                const form = document.getElementById('tgo-event-registration');
                const message = document.getElementById(
                    'tgo-registration-message'
                );

                if (
                    !response.has_available_slots &&
                    form &&
                    form.style.display !== 'none'
                ) {
                    form.style.display = 'none';
                    if (message) {
                        message.innerHTML = window.tgo_rest.event_full_message;
                        message.classList.remove('success', 'error');
                        message.classList.add('info');
                        message.style.display = 'block';
                    }
                }

                // Add handler for cancel button
                list.querySelectorAll('.tgo-cancel-registration').forEach(
                    (button) => {
                        button.addEventListener('click', function (e) {
                            e.preventDefault();
                            cancelRegistration(this, eventId);
                        });
                    }
                );
            });
    }

    // Function to cancel registration
    function cancelRegistration(button, eventId) {
        const message = document.getElementById('tgo-registration-message');
        const list = document.querySelector('.tgo-participant-list');

        // Disable button
        button.disabled = true;
        if (message) {
            message.innerHTML = '';
            message.style.display = 'none';
        }

        wp.apiFetch({
            path: `/tactical-game-organizer/v1/events/${eventId}/cancel`,
            method: 'DELETE',
        })
            .then(function (response) {
                if (message) {
                    message.innerHTML = response.message;
                    message.classList.remove('error');
                    message.classList.add('success');
                    message.style.display = 'block';
                }

                // Update participants list
                return updateParticipantsList(list);
            })
            .then(function () {
                // Show registration form
                const form = document.getElementById('tgo-event-registration');
                if (form) {
                    form.querySelectorAll('input[type="text"]').forEach(
                        (input) => {
                            input.value = ''; // Clear form fields
                        }
                    );
                    fadeIn(form, 300);
                }
            })
            .catch(function (error) {
                if (message) {
                    message.innerHTML =
                        error.message || window.tgo_rest.error_message;
                    message.classList.remove('success');
                    message.classList.add('error');
                    message.style.display = 'block';
                }

                // Enable button in case of error
                button.disabled = false;
            });
    }

    // Function to toggle forms
    function toggleForms(showRegistration) {
        const registration = document.getElementById('tgo-event-registration');
        const cancellation = document.getElementById('tgo-event-cancellation');
        const message = document.getElementById('tgo-registration-message');

        if (message) {
            message.style.display = 'none'; // Hide message when switching forms
        }

        if (showRegistration) {
            if (cancellation) cancellation.style.display = 'none';
            if (registration) {
                registration.style.display = 'block';
                registration
                    .querySelectorAll('input[type="text"]')
                    .forEach((input) => {
                        input.value = ''; // Clear form fields
                    });
            }
        } else {
            if (registration) registration.style.display = 'none';
            if (cancellation) cancellation.style.display = 'block';
        }
    }

    // Utility function for fade in animation
    function fadeIn(element, duration) {
        element.style.opacity = 0;
        element.style.display = 'block';

        let start = null;
        function animate(timestamp) {
            if (!start) start = timestamp;
            const progress = timestamp - start;
            element.style.opacity = Math.min(progress / duration, 1);
            if (progress < duration) {
                window.requestAnimationFrame(animate);
            }
        }
        window.requestAnimationFrame(animate);
    }

    // Utility function for fade out animation
    function fadeOut(element, duration) {
        element.style.opacity = 1;

        let start = null;
        function animate(timestamp) {
            if (!start) start = timestamp;
            const progress = timestamp - start;
            element.style.opacity = Math.max(1 - progress / duration, 0);
            if (progress < duration) {
                window.requestAnimationFrame(animate);
            } else {
                element.style.display = 'none';
            }
        }
        window.requestAnimationFrame(animate);
    }

    // Handle registration form submission
    const registrationForm = document.getElementById('tgo-event-registration');
    if (registrationForm) {
        registrationForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const message = document.getElementById('tgo-registration-message');
            const list = document.querySelector('.tgo-participant-list');
            const eventId = list.dataset.eventId;

            const data = {
                callsign: this.querySelector('input[name="callsign"]').value,
                role: this.querySelector('select[name="role"]').value,
                team: this.querySelector('input[name="team"]').value,
            };

            this.querySelector('button').disabled = true;
            if (message) {
                message.innerHTML = '';
                message.style.display = 'none';
            }

            wp.apiFetch({
                path: `/tactical-game-organizer/v1/events/${eventId}/register`,
                method: 'POST',
                data: data,
            })
                .then(function (response) {
                    if (message) {
                        message.innerHTML = response.message;
                        message.classList.remove('error');
                        message.classList.add('success');
                        message.style.display = 'block';
                    }

                    // Update participants list and hide form
                    return updateParticipantsList(list).then(function () {
                        fadeOut(registrationForm, 300);
                    });
                })
                .catch(function (error) {
                    if (message) {
                        message.innerHTML =
                            error.message || window.tgo_rest.error_message;
                        message.classList.remove('success');
                        message.classList.add('error');
                        message.style.display = 'block';
                    }
                })
                .finally(function () {
                    registrationForm.querySelector('button').disabled = false;
                });
        });
    }

    // Update participants list every 30 seconds
    const participantList = document.querySelector('.tgo-participant-list');
    if (participantList) {
        // Initial update of the list
        updateParticipantsList(participantList);

        setInterval(function () {
            updateParticipantsList(participantList);
        }, 30000);
    }
});
