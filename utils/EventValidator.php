<?php
class EventValidator {
    public static function validateEvent($data) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'Event name is required';
        } elseif (strlen($data['name']) < 3) {
            $errors['name'] = 'Event name must be more than 3 characters';
        }

        if (empty($data['max_capacity'])) {
            $errors['max_capacity'] = 'Maximum capacity is required';
        } elseif (!filter_var($data['max_capacity'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
            $errors['max_capacity'] = 'Maximum capacity must be a positive number';
        }

        if (empty($data['event_date'])) {
            $errors['event_date'] = 'Event date is required';
        } else {
            $eventDate = strtotime($data['event_date']);
            $now = time();
            if ($eventDate === false) {
                $errors['event_date'] = 'Invalid date format';
            } elseif ($eventDate < $now) {
                $errors['event_date'] = 'Event date must be in the future';
            }
        }

        return $errors;
    }
}