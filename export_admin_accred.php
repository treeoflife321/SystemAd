<?php
// Include database connection
include 'config.php';

// Check if necessary parameters are present
if (isset($_POST['filter'], $_POST['start_date'], $_POST['end_date'])) {
    $filter = $_POST['filter'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Format the start and end dates
    $formatted_start_date = DateTime::createFromFormat('Y-m-d', $start_date)->format('F j, Y');
    $formatted_end_date = DateTime::createFromFormat('Y-m-d', $end_date)->format('F j, Y');

    // Count the total active users
    $total_users_query = "SELECT COUNT(*) AS total_users FROM users WHERE status = 'Active'";
    $total_users_result = $mysqli->query($total_users_query);
    $total_users = $total_users_result ? $total_users_result->fetch_assoc()['total_users'] : 0;

    // Count the checked-in users within the date range
    $checked_in_users_query = "
        SELECT COUNT(DISTINCT chkin.info) AS checked_in_users 
        FROM chkin 
        JOIN users ON chkin.info = users.info 
        WHERE users.status = 'Active'
        AND STR_TO_DATE(chkin.date, '%m-%d-%Y') BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')
    ";
    $stmt = $mysqli->prepare($checked_in_users_query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $checked_in_users_result = $stmt->get_result();
    $checked_in_users = $checked_in_users_result ? $checked_in_users_result->fetch_assoc()['checked_in_users'] : 0;

    // Query to fetch the user logs
    $query = $filter === 'in' ? "
        SELECT 
            chkin.info, 
            users.idnum, 
            users.year_level, 
            COUNT(chkin.info) AS checkin_count 
        FROM chkin 
        JOIN users ON chkin.info = users.info 
        WHERE users.status = 'Active'
        AND STR_TO_DATE(chkin.date, '%m-%d-%Y') BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d') 
        GROUP BY chkin.info, users.idnum, users.year_level 
        ORDER BY chkin.info ASC
    " : "
        SELECT 
            users.info, 
            users.idnum, 
            users.year_level, 
            '0' AS checkin_count 
        FROM users 
        WHERE users.status = 'Active'
        AND NOT EXISTS (
            SELECT 1 
            FROM chkin 
            WHERE chkin.info = users.info 
            AND STR_TO_DATE(chkin.date, '%m-%d-%Y') BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')
        ) 
        ORDER BY users.info ASC
    ";

    // Get the heading
    $heading = $filter === 'in' ? 
        "Library Users Checked In from " . htmlspecialchars($formatted_start_date) . " to " . htmlspecialchars($formatted_end_date) : 
        "Library Users Not Checked In from " . htmlspecialchars($formatted_start_date) . " to " . htmlspecialchars($formatted_end_date);

    // Update count display based on filter
    if ($filter === 'in') {
        $count_display = "Checked In Users: " . $checked_in_users . " / " . $total_users;
    } elseif ($filter === 'not') {
        $not_checked_in_users = $total_users - $checked_in_users;
        $count_display = "Not Checked In Users: " . $not_checked_in_users . " / " . $total_users;
    }

    // Prepare and execute the query
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    // Output CSV headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="monitored_users.csv"');

    $output = fopen('php://output', 'w');
    
    // Write the heading and count display as CSV rows
    fputcsv($output, [$heading]);
    fputcsv($output, [$count_display]);

    // Write table column headers
    fputcsv($output, ['#', 'Info', 'Course', 'ID Number', 'Year Level', 'Check-In Count']);

    // Write table data
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        $course = extractCourse($row['info']);
        fputcsv($output, [
            $counter++,
            str_replace($course, '', $row['info']),  // Info without course
            $course,
            $row['idnum'],
            $row['year_level'],
            $row['checkin_count']
        ]);
    }

    fclose($output);
    exit();
}

// Function to extract the course
function extractCourse($info) {
    $courses = ['BSIT', 'BSNAME', 'BSTCM', 'BSMET', 'BSESM'];
    foreach ($courses as $course) {
        if (strpos($info, $course) !== false) {
            return $course;
        }
    }
    return 'N/A'; // Default if no course is found
}
?>
