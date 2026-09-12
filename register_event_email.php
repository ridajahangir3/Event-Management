<?php

function sendEventNotificationToAllUsers(
    $conn,
    $eventTitle,
    $eventDate,
    $eventTime,
    $location
) {

    $users = $conn->query(
        "SELECT name, email
         FROM users
         WHERE email IS NOT NULL
         AND email != ''"
    );

    if (!$users || $users->num_rows == 0) {
        return 0;
    }

    $count = 0;

    $subject = "New Event Available - " . $eventTitle;

    while ($user = $users->fetch_assoc()) {

        $name = $user['name'];
        $email = $user['email'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            continue;
        }

        $body = "Hello " . $name . ",\n\n";

        $body .= "A new event has been added.\n\n";

        $body .= "EVENT DETAILS\n";
        $body .= "--------------------------------\n";
        $body .= "Event: " . $eventTitle . "\n";
        $body .= "Date: " . $eventDate . "\n";
        $body .= "Time: " . ($eventTime ?: "Not specified") . "\n";
        $body .= "Location: " . ($location ?: "Not specified") . "\n";
        $body .= "--------------------------------\n\n";

        $body .= "You can now register for this event.\n\n";

        $body .= "Thank you,\n";
        $body .= "Event Management System";


        $headers =
            "From: Event Management System <noreply@localhost.com>\r\n";

        $headers .=
            "Reply-To: noreply@localhost.com\r\n";

        $headers .=
            "MIME-Version: 1.0\r\n";

        $headers .=
            "Content-Type: text/plain; charset=UTF-8\r\n";


        if (
            mail(
                $email,
                $subject,
                $body,
                $headers
            )
        ) {
            $count++;
        }
    }

    return $count;
}

?>