<?php
session_start();
require_once 'config/database.php';
require_once 'utils/AdminAuth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$isAdmin = AdminAuth::isAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Event Manager</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" id="eventsTab">Events</a>
                    </li>
                    <?php if ($isAdmin): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="adminTab">Admin</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="navbar-text me-3">
                        Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?>
                    </span>
                    <a href="auth/logout.php" class="btn btn-outline-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col">
                <button type="button" class="btn btn-primary mb-3 mb-sm-0" data-bs-toggle="modal" data-bs-target="#createEventModal">
                    <i class="bi bi-plus-circle"></i> Create Event
                </button>
                <?php if ($isAdmin): ?>
                    <button type="button" class="btn btn-outline-primary ms-md-2" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="bi bi-download"></i> Export Attendees
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row mb-4 gap-2 gap-md-0">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search events...">
                    <button class="btn btn-outline-secondary" type="button" id="searchButton">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select h-100" id="dateFilter">
                    <option value="all">All Dates</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="today">Today</option>
                    <option value="past">Past</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select h-100" id="sortBy">
                    <option value="event_date">Sort by Date</option>
                    <option value="name">Sort by Name</option>
                    <option value="registered_count">Sort by Registrations</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select h-100" id="pageSize">
                    <option value="9">9 per page</option>
                    <option value="18">18 per page</option>
                    <option value="40">40 per page</option>
                </select>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div class="loading-spinner text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Events Grid -->
        <div id="eventsList" class="row">
        </div>

        <!-- Pagination -->
        <div class="row mt-4">
            <div class="col">
                <nav aria-label="Event pagination">
                    <ul class="pagination justify-content-center" id="pagination">
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Create Event Modal -->
    <div class="modal fade" id="createEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createEventForm">
                        <div class="mb-3">
                            <label for="eventName" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="eventName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="eventDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="eventDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="eventDate" class="form-label">Date & Time</label>
                            <input type="datetime-local" class="form-control" id="eventDate" name="event_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="maxCapacity" class="form-label">Maximum Capacity</label>
                            <input type="number" class="form-control" id="maxCapacity" name="max_capacity" required min="1">
                        </div>
                        <button type="submit" class="btn btn-primary">Create Event</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editEventForm">
                        <input type="hidden" id="editEventId" name="event_id">
                        <div class="mb-3">
                            <label for="editEventName" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="editEventName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEventDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editEventDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editEventDate" class="form-label">Date & Time</label>
                            <input type="datetime-local" class="form-control" id="editEventDate" name="event_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="editMaxCapacity" class="form-label">Maximum Capacity</label>
                            <input type="number" class="form-control" id="editMaxCapacity" name="max_capacity" required min="1">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Event</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modal (Admin Only) -->
    <?php if ($isAdmin): ?>
        <div class="modal fade" id="exportModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Export Attendee List</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="exportEvent" class="form-label">Select Event</label>
                            <select class="form-select" id="exportEvent">
                            </select>
                        </div>
                        <button id="exportButton" class="btn btn-primary">
                            <i class="bi bi-download"></i> Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load events
            let currentPage = 1;
            let currentSort = 'event_date';
            let currentOrder = 'ASC';

            // Load Events Function
            function loadEvents() {
                $('.loading-spinner').show();
                $('#eventsList').hide();

                const searchTerm = $('#searchInput').val();
                const dateFilter = $('#dateFilter').val();
                const sortBy = $('#sortBy').val();
                const pageSize = $('#pageSize').val();

                $.get('events/list.php', {
                    page: currentPage,
                    limit: pageSize,
                    search: searchTerm,
                    sort: sortBy,
                    order: currentOrder,
                    date_filter: dateFilter
                }, function(responses) {
                    const response = JSON.parse(responses);
                    if (response.success) {
                        displayEvents(response.events);
                        updatePagination(response.pagination);
                    } else {
                        alert(response.message);
                    }
                    $('.loading-spinner').hide();
                    $('#eventsList').show();
                });
            }

            // Display Events Function
            function displayEvents(events) {
                $('#eventsList').empty();
                if (events.length === 0) {
                    $('#eventsList').append(`
                        <div class="col-12 text-center">
                            <div class="alert alert-info" role="alert">
                                No events found.
                            </div>
                        </div>
                    `);
                } else {
                    events.forEach(function(event, index) {
                        const eventDate = new Date(event.event_date);
                        const delay = index * 100;
                        
                        const shortDesc = event.description.substring(0, 70);
                        const hasMoreText = event.description.length > 100;
                        
                        $('#eventsList').append(`
                            <div class="col-lg-4 col-md-6 mb-4 event-div">
                                <div class="card event-card" style="animation-delay: ${delay}ms">
                                    <div class="card-body p-4">
                                        <div class="registration-count">
                                            ${event.registered_count}/${event.max_capacity}
                                        </div>
                                        <h5 class="card-title mb-3">${event.name}</h5>
                                        <p class="card-text text-muted mb-4">
                                            <span class="description-text">
                                                ${shortDesc}${hasMoreText ? '...' : ''}
                                            </span>
                                            <span class="description-full d-none">
                                                ${event.description}
                                            </span>
                                            ${hasMoreText ? `
                                                <a href="#" class="text-primary description-toggle">
                                                    See more
                                                </a>
                                            ` : ''}
                                        </p>
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bi bi-calendar me-2 text-primary"></i>
                                            <span class="text-muted">${eventDate.toLocaleDateString()}</span>
                                            <i class="bi bi-clock ms-3 me-2 text-primary"></i>
                                            <span class="text-muted">${eventDate.toLocaleTimeString()}</span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            ${renderEventButtons(event)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                }
            }

            function renderEventButtons(event) {
                let buttons = '';

                if (event.is_registered) {
                    buttons += `
                    <button class="btn btn-sm btn-warning cancel-registration" data-id="${event.id}">
                        <i class="bi bi-x-circle"></i> Cancel Registration
                    </button>
                `;
                } else if (event.registered_count >= event.max_capacity && !event.is_admin) {
                    buttons += `
                    <button class="btn btn-sm btn-secondary" disabled>
                        <i class="bi bi-exclamation-circle"></i> Event Full
                    </button>
                `;
                } else if (!event.is_admin) {
                    buttons += `
                    <button class="btn btn-sm btn-success register-event" data-id="${event.id}">
                        <i class="bi bi-check-circle"></i> Register
                    </button>
                `;
                }

                if (event.can_edit || <?php echo $isAdmin ? 'true' : 'false'; ?>) {
                    buttons += `
                        <button class="btn btn-sm btn-primary ms-2 edit-event" data-id="${event.id}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger ms-2 delete-event" data-id="${event.id}">
                            <i class="bi bi-trash"></i>
                        </button>
                    `;
                }

                // Show attendee list button only for admin
                <?php if ($isAdmin): ?>
                    buttons += `
                        <a href="attendeeList.php?event_id=${event.id}" 
                        class="btn btn-sm btn-info ms-2">
                            <i class="bi bi-people"></i> Attendees
                        </a>
                    `;
                <?php endif; ?>

                return buttons;
            }

            // Update Pagination Function
            function updatePagination(pagination) {
                const $pagination = $('#pagination');
                $pagination.empty();

                if (pagination && pagination.total_pages > 1) {
                    // Previous button
                    $pagination.append(`
                        <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
                        </li>
                    `);

                    // Page numbers
                    for (let i = 1; i <= pagination.total_pages; i++) {
                        if (
                            i === 1 ||
                            i === pagination.total_pages ||
                            (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)
                        ) {
                            $pagination.append(`
                                <li class="page-item ${pagination.current_page === i ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                </li>
                            `);
                        } else if (
                            i === pagination.current_page - 3 ||
                            i === pagination.current_page + 3
                        ) {
                            $pagination.append(`
                                <li class="page-item disabled">
                                    <a class="page-link" href="#">...</a>
                                </li>
                            `);
                        }
                    }

                    // Next button
                    $pagination.append(`
                        <li class="page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
                        </li>
                    `);
                }

                // Add click handlers for pagination
                $('.page-link').on('click', function(e) {
                    e.preventDefault();
                    const page = $(this).data('page');
                    if (page && !$(this).parent().hasClass('disabled')) {
                        currentPage = page;
                        loadAttendees();
                    }
                });
            }

            // Event Handlers
            $('#searchButton').on('click', function(e) {
                if (e.type === 'click' || e.keyCode === 13) {
                    currentPage = 1;
                    loadEvents();
                }
            });
            $('#searchInput').on('keyup', function(e) {
                currentPage = 1;
                loadEvents();
            });

            $('#dateFilter, #sortBy, #pageSize').on('change', function() {
                currentPage = 1;
                loadEvents();
            });

            $('#pagination').on('click', '.page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page) {
                    currentPage = page;
                    loadEvents();
                }
            });


            // Create event
            $('#createEventForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                if (validateEventForm()) {
                    $.post('events/create.php', formData, function(response) {
                        const parsedResponse = JSON.parse(response);
                        if (parsedResponse.success) {
                            $('#createEventModal').modal('hide');
                            $('#createEventForm')[0].reset();
                            loadEvents();
                        }
                        if(parsedResponse.errors){
                            Object.keys(parsedResponse.errors).forEach(field => {
                                const input = $(`input[name="${field}"], textarea[name="${field}"]`);

                                input.siblings('.invalid-feedback').remove();
                    
                                input.addClass('is-invalid')
                                    .after(`
                                        <div class="invalid-feedback">
                                            ${parsedResponse.errors[field]}
                                        </div>
                                    `);
                            });
                        }
                        else{
                            alert(parsedResponse.message);
                        }
                    });
                }
            });

            // Delete event
            $(document).on('click', '.delete-event', function() {
                if (confirm('Are you sure you want to delete this event?')) {
                    const eventId = $(this).data('id');
                    $.post('events/delete.php', {
                        event_id: eventId
                    }, function(response) {
                        const parsedResponse = JSON.parse(response);
                        if (parsedResponse.success) {
                            loadEvents();
                        }
                        alert(parsedResponse.message);
                    });
                }
            });

            $(document).on('click', '.edit-event', function() {
                const eventId = $(this).data('id');
                $.get('events/get_event.php', {
                    event_id: eventId
                }, function(response) {
                    const event = JSON.parse(response);
                    if (event.success) {
                        $('#editEventId').val(event.data.id);
                        $('#editEventName').val(event.data.name);
                        $('#editEventDescription').val(event.data.description);
                        $('#editEventDate').val(event.data.event_date.replace(' ', 'T'));
                        $('#editMaxCapacity').val(event.data.max_capacity);
                        $('#editEventModal').modal('show');
                    } else {
                        alert(event.message);
                    }
                });
            });

            $('#editEventForm').on('submit', function(e) {
                e.preventDefault();
                $.post('events/update.php', $(this).serialize(), function(response) {
                    const parsedResponse = JSON.parse(response);
                    if (parsedResponse.success) {
                        $('#editEventModal').modal('hide');
                        loadEvents();
                    }
                    console.log(parsedResponse.errors);

                    if(parsedResponse.errors){
                        Object.keys(parsedResponse.errors).forEach(field => {
                            const input = $(`input[name="${field}"], textarea[name="${field}"]`);

                            input.siblings('.invalid-feedback').remove();
                
                            input.addClass('is-invalid')
                                .after(`
                                    <div class="invalid-feedback">
                                        ${parsedResponse.errors[field]}
                                    </div>
                                `);
                        });
                    }
                    else{
                        alert(parsedResponse.message);
                    }
                });
            });

            // Register for event
            $(document).on('click', '.register-event', function() {
                const eventId = $(this).data('id');
                $.post('events/register.php', {
                    event_id: eventId
                }, function(response) {
                    const parsedResponse = JSON.parse(response);
                    if (parsedResponse.success) {
                        loadEvents();
                    }
                    alert(parsedResponse.message);
                });
            });

            $(document).on('click', '.cancel-registration', function() {
                if (confirm('Are you sure you want to cancel your registration?')) {
                    const eventId = $(this).data('id');
                    $.post('events/cancel_registration.php', {
                        event_id: eventId
                    }, function(response) {
                        const parsedResponse = response;
                        if (parsedResponse.success) {
                            loadEvents();
                        }
                        alert(parsedResponse.message);
                    });
                }
            });

            // Load events for export dropdown
            function loadExportEvents() {
                $.get('events/list.php', {
                    limit: 1000
                }, function(responses) {
                    response = JSON.parse(responses);
                    if (response.success) {
                        const $select = $('#exportEvent');
                        $select.empty();

                        response.events.forEach(function(event) {
                            $select.append(`
                        <option value="${event.id}">
                            ${event.name} (${new Date(event.event_date).toLocaleDateString()})
                        </option>
                    `);
                        });
                    }
                });
            }

            // Handle export button click
            $('#exportButton').on('click', function() {
                const $btn = $(this);
                const eventId = $('#exportEvent').val();
                const originalText = $btn.html();

                $btn.prop('disabled', true)
                    .html('<i class="bi bi-arrow-repeat"></i> Exporting...');

                // Create and submit form
                const form = $('<form>', {
                    method: 'POST',
                    action: 'reports/export_attendees.php',
                    style: 'display: none'
                });

                form.append($('<input>', {
                    type: 'hidden',
                    name: 'event_id',
                    value: eventId
                }));

                $('body').append(form);
                form.submit();

                // Reset button after delay
                setTimeout(() => {
                    $btn.prop('disabled', false).html(originalText);
                    form.remove();
                }, 2000);
            });

            // Load export events when modal is shown
            $('#exportModal').on('show.bs.modal', function() {
                loadExportEvents();
            });

            // Add event count badge to export button
            function updateExportBadge() {
                $.get('reports/event_stats.php', function(response) {
                    if (response.success) {
                        $('.export-badge').text(response.total_events);
                    }
                });
            }

            // Load events on page load
            loadEvents();

            // Client-side validation for event forms
            function validateEventForm() {
                let isValid = true;
                const name = $('#eventName').val() || $('#editEventName').val();
                const description = $('#eventDescription').val() || $('#editEventDescription').val();
                const eventDate = $('#eventDate').val() || $('#editEventDate').val();
                const maxCapacity = $('#maxCapacity').val() || $('#editMaxCapacity').val();

                if (!name || name.trim() === '') {
                    isValid = false;
                    alert('Event name is required.');
                }
                if (!description || description.trim() === '') {
                    isValid = false;
                    alert('Event description is required.');
                }
                if (!eventDate || eventDate.trim() === '') {
                    isValid = false;
                    alert('Event date and time are required.');
                }
                if (!maxCapacity || maxCapacity <= 0) {
                    isValid = false;
                    alert('Maximum capacity must be a positive number.');
                }

                return isValid;
            }
        });
        $(document).on('click', '.description-toggle', function(e) {
            e.preventDefault();
            const card = $(this).closest('.card-body');
            const shortDesc = card.find('.description-text');
            const fullDesc = card.find('.description-full');
        
            if (shortDesc.hasClass('d-none')) {
                shortDesc.removeClass('d-none');
                fullDesc.addClass('d-none');
                $(this).text('See more');
            } else {
                shortDesc.addClass('d-none');
                fullDesc.removeClass('d-none');
                $(this).text('See less');
            }
        });
    </script>
</body>

</html>