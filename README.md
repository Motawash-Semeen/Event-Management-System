# Event Management System

A web-based event management system that allows users to create, manage, and register for events. Administrators can track attendees and export attendance data.

## Features

### User Features
- User authentication (login/register)
- Create and manage events
- Register for events
- View registered events
- Cancel event registration
- Search and filter events
- Responsive design

### Admin Features
- View all events
- Export attendee lists to CSV
- View event statistics
- Manage event registrations
- View attendee details

## Technology Stack
- PHP 8.2+
- jQuery 3.6.0
- Bootstrap 5.1.3
- HTML5/CSS3

## Installation

1. Clone the repository:
git clone https://github.com/yourusername/Event-Management-System.git

2. DB SQL
CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `max_capacity` int(11) NOT NULL,
  `event_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','cancelled') DEFAULT 'confirmed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_date` (`event_date`);


ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration` (`event_id`,`user_id`),
  ADD KEY `idx_event_user` (`event_id`,`user_id`);


ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `idx_email` (`email`);


ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;


ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;


ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;


## Test Credentials
- Admin User
  Email: admin@gmail.com
  Password: Password1
- Regular User
  Email: kete@mailinator.com
  Password: Pa$$w0rd!

## ToDo
- [x] Attende List
- [x] Attende List Download option in CSV
- [x] Event Sorting and Searching issue
- [x] Event Registration form and max size validation add
- [x] ID encryption and Decryption
- [x] Logout Funcional
- [ ] UI/UX Improve
- [ ] Code improve
- [ ] Ensure client-side and server-side validation.
- [ ] Use prepared statements to prevent SQL injection.
- [ ] Provide setup instructions for the project.
