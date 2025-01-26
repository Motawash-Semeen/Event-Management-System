<?php
session_start();
require_once 'config/database.php';
require_once 'utils/AdminAuth.php';
require_once 'utils/Security.php';

if (!AdminAuth::isAdmin()) {
  header('Location: dashboard.php');
  exit();
}

$event_id = isset($_GET['event_id']) ? Security::decrypt($_GET['event_id']) : null;
if (!$event_id) {
  header('Location: dashboard.php');
  exit();
}

$database = new Database();
$db = $database->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
  $stmt = $db->prepare("SELECT name FROM events WHERE id = ?");
  $stmt->execute([$event_id]);
  $event = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$event) {
    header('Location: dashboard.php');
    exit();
  }
} catch (PDOException $e) {
  header('Location: dashboard.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendee List - <?php echo htmlspecialchars($event['name']); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #4f46e5;
      --primary-hover: #4338ca;
      --surface: #ffffff;
      --surface-hover: #f8fafc;
      --text: #1f2937;
      --text-light: #6b7280;
    }

    body {
      background: linear-gradient(135deg, #f6f7ff 0%, #eef1ff 100%);
      font-family: 'Plus Jakarta Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .navbar {
      backdrop-filter: blur(10px);
      background: rgba(79, 70, 229, 0.95) !important;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .table-container {
      background: var(--surface);
      border-radius: 1rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      animation: fadeIn 0.5s ease-out;
    }

    .table-responsive {
      overflow-y: visible;
      overflow-x: scroll;
    }

    .form-control,
    .form-select {
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      border: 2px solid #e5e7eb;
      transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .table th {
      color: var(--text-light);
      font-weight: 500;
      cursor: pointer;
    }

    .table th:hover {
      color: var(--primary);
    }

    .attendee-row {
      transition: all 0.3s ease;
    }

    .attendee-row:hover {
      background: var(--surface-hover);
      transform: translateX(5px);
    }

    .sort-icon {
      display: inline-block;
      margin-left: 0.5rem;
      transition: transform 0.2s ease;
    }

    .loading {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    .btn {
      border-radius: 0.75rem;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .pagination .page-link {
      border-radius: 0.5rem;
      margin: 0 0.25rem;
      border: none;
      color: var(--primary);
    }

    .pagination .page-item.active .page-link {
      background: var(--primary);
      color: white;
    }

    .empty-state {
      padding: 2rem;
      animation: fadeIn 0.5s ease-out;
    }

    .empty-state i {
      color: #e5e7eb;
    }

    .empty-state h5 {
      color: var(--text);
      font-weight: 500;
    }

    .empty-state p {
      font-size: 0.875rem;
    }

    .spinner-border-sm {
      width: 1rem;
      height: 1rem;
      margin-right: 0.5rem;
    }

    #exportButton {
      white-space: nowrap;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    #exportButton:hover {
      transform: translateY(-2px);
    }

    #exportButton:disabled {
      opacity: 0.7;
      transform: none;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php">Event Manager</a>
      <a href="dashboard.php" class="btn btn-outline-light">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
      </a>
    </div>
  </nav>

  <div class="container">
    <div class="row mb-4">
      <div class="col">
        <h2 class="mb-0">Attendees for: <?php echo htmlspecialchars($event['name']); ?></h2>
        <p class="text-muted">Manage event attendees</p>
      </div>
    </div>

    <div class="row mb-4 align-items-end gap-2 gap-md-0">
      <div class="col-md-4">
        <div class="input-group">
          <input type="text" id="searchInput" class="form-control" placeholder="Search attendees...">
          <button class="btn btn-outline-secondary" type="button" id="searchButton">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </div>

      <!-- <div class="col-md-3">
        <select class="form-select" id="sortBy">
          <option value="registration_date">Sort by Registration Date</option>
          <option value="name">Sort by Name</option>
          <option value="email">Sort by Email</option>
        </select>
      </div> -->
      <div class="col-md-3">
        <button class="btn btn-primary" id="exportButton">
          <i class="bi bi-download"></i> Export Attendees
        </button>
      </div>
      <div class="col-md-3 offset-md-2">
        <select class="form-select" id="pageSize">
          <option value="10">10 per page</option>
          <option value="25">25 per page</option>
          <option value="50">50 per page</option>
        </select>
      </div>
    </div>

    <div class="table-container position-relative">
      <div class="loading d-none">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th data-sort="name">Name <i class="bi bi-arrow-down-up sort-icon"></i></th>
              <th data-sort="email">Email <i class="bi bi-arrow-down-up sort-icon"></i></th>
              <th data-sort="registration_date">Registration Date <i class="bi bi-arrow-down-up sort-icon"></i></th>
            </tr>
          </thead>
          <tbody id="attendeesList"></tbody>
        </table>
      </div>
    </div>

    <nav aria-label="Attendee pagination" class="mt-4">
      <ul class="pagination justify-content-center" id="pagination"></ul>
    </nav>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
      let currentPage = 1;
      let currentSort = 'registration_date';
      let currentOrder = 'DESC';
      const eventId = "<?php echo htmlspecialchars(Security::encrypt($event_id), ENT_QUOTES, 'UTF-8'); ?>";

      function loadAttendees() {
        $('.loading').removeClass('d-none');
        $('.table-responsive').addClass('opacity-25');
        // const sortBy = $('#sortBy').val();

        $.get('events/attendees.php', {
          event_id: eventId,
          page: currentPage,
          sort: currentSort,
          order: currentOrder,
          search: $('#searchInput').val(),
          limit: $('#pageSize').val()
        }, function(response) {
          if (response.success) {
            displayAttendees(response.data);
            updatePagination(response.pagination);
          } else {
            alert('Failed to load attendees');
          }
          $('.loading').addClass('d-none');
          $('.table-responsive').removeClass('opacity-25');
        });


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

      function displayAttendees(attendees) {
        const tbody = $('#attendeesList');
        tbody.empty();

        if (!attendees || attendees.length === 0) {
          tbody.append(`
                        <tr>
                <td colspan="3" class="text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 mb-1">No Attendees Yet</h5>
                        <p class="text-muted mb-0">No one has registered for this event yet.</p>
                    </div>
                </td>
            </tr>
                    `);
          return;
        }

        attendees.forEach((attendee, index) => {
          const date = new Date(attendee.registration_date);
          tbody.append(`
                        <tr class="attendee-row" style="animation-delay: ${index * 50}ms">
                            <td class="align-middle py-3">${attendee.username }</td>
                            <td class="align-middle py-3">${attendee.email}</td>
                            <td class="align-middle py-3">${date.toLocaleString()}</td>
                        </tr>
                    `);
        });
      }

      // Event Handlers
      $('#searchButton').on('click', function(e) {
        if (e.type === 'click' || e.keyCode === 13) {
          currentPage = 1;
          loadAttendees();
        }
      });
      $('#searchInput').on('keyup', function(e) {
        currentPage = 1;
        loadAttendees();
      });

      $('#sortBy, #pageSize').on('change', function() {
        currentPage = 1;
        loadAttendees();
      });

      // Initial load
      loadAttendees();

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

      $('#exportButton').click(function() {
        const $btn = $(this);
        const originalText = $btn.html();

        $btn.prop('disabled', true)
          .html('<i class="bi bi-arrow-repeat"></i> Exporting...');

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

        setTimeout(() => {
          $btn.prop('disabled', false).html(originalText);
          form.remove();
        }, 2000);
      });
    });
  </script>
</body>

</html>