<?php
// Include database connection
include 'config.php';

// Get search filters from POST data
$search = isset($_POST['search']) ? $_POST['search'] : '';
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';
$course = isset($_POST['course']) ? $_POST['course'] : '';
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$purpose = isset($_POST['purpose']) ? $_POST['purpose'] : '';
$idnum = isset($_POST['idnum']) ? $_POST['idnum'] : '';
$year_level = isset($_POST['year_level']) ? $_POST['year_level'] : '';
$gender = isset($_POST['gender']) ? $_POST['gender'] : '';

// Determine the title based on the filters
if (!empty($start_date) && !empty($end_date)) {
    $title = "Library Logs from '" . date("F j, Y", strtotime($start_date)) . "' to '" . date("F j, Y", strtotime($end_date)) . "'";
} else {
    $title = "Library Logs for All Dates";
}

// Prepare query with filters
$query = "SELECT * FROM chkin WHERE archived = ''";
if(!empty($search)) $query .= " AND info LIKE '%" . $mysqli->real_escape_string($search) . "%'";
if(!empty($course)) $query .= " AND info LIKE '%" . $mysqli->real_escape_string($course) . "%'";
if(!empty($user_type)) $query .= " AND user_type = '" . $mysqli->real_escape_string($user_type) . "'";
if(!empty($start_date)) {
    $start_date = date("Y-m-d", strtotime($start_date));
    $query .= " AND STR_TO_DATE(date, '%m-%d-%Y') >= '" . $start_date . "'";
}
if(!empty($end_date)) {
    $end_date = date("Y-m-d", strtotime($end_date));
    $query .= " AND STR_TO_DATE(date, '%m-%d-%Y') <= '" . $end_date . "'";
}
if(!empty($purpose)) $query .= " AND purpose = '" . $mysqli->real_escape_string($purpose) . "'";
if (!empty($idnum)) $query .= " AND idnum LIKE '%" . $mysqli->real_escape_string($idnum) . "%'";
if (!empty($year_level)) $query .= " AND year_level = '" . $mysqli->real_escape_string($year_level) . "'";
if (!empty($gender)) $query .= " AND gender = '" . $mysqli->real_escape_string($gender) . "'";

// Execute query
$result = $mysqli->query($query);
if (!$result) {
    die("Error executing query: " . $mysqli->error);
}

// Output CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=library_logs.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Write title as the first row
fputcsv($output, [$title]);

// Add a blank row for readability
fputcsv($output, []);

// Write column headers
fputcsv($output, ['#', 'User Info', 'ID Number', 'User Type', 'Year Level', 'Date', 'Time In', 'Time Out', 'Purpose']);

// Write rows to CSV
$counter = 0;
while ($row = $result->fetch_assoc()) {
    $counter++;
    fputcsv($output, [
        $counter,
        $row['info'],
        $row['idnum'],
        $row['user_type'],
        $row['year_level'],
        $row['date'],
        $row['timein'],
        $row['timeout'],
        $row['purpose']
    ]);
}

// Close output stream
fclose($output);
exit;
?>
