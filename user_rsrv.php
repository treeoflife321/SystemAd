<?php
function checkAdminSession() {
    if (!isset($_GET['uid']) || empty($_GET['uid'])) {
        header("Location: login.php");
        exit;
    }
}

// Call the function at the top of your files
checkAdminSession();
?>
<?php
// Include database connection
include 'config.php';

// Initialize uid variable
$uid = "";

// Check if 'uid' parameter is present in the URL
if(isset($_GET['uid'])) {
    $uid = $_GET['uid'];
}

// Retrieve alert message if present
$alertMessage = isset($_GET['alert_message']) ? $_GET['alert_message'] : '';

// Check if 'uid' parameter is present in the URL
if(isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    // Query to fetch the username and profile image corresponding to the uid
    $query = "SELECT username, profile_image FROM users WHERE uid = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if the result is not empty
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_username = $user['username'];
        $profile_image_path = $user['profile_image']; // Fetch profile image path
        
        // Set the username for display
        $user_username_display = $user_username;
    } else {
        // Display default values if user data is not found
        $user_username_display = "Username";
        $profile_image_path = "uploads/default.jpg"; // Default profile image
    }
    // Close statement
    $stmt->close();
}

// Check if an alert message is present
$alertMessage = "";
if(isset($_GET['alert_message'])) {
    $alertMessage = $_GET['alert_message'];
}

// Fetch data from inventory table
$query = "SELECT i.bid, i.title, i.status, f.uid 
          FROM inventory i 
          LEFT JOIN fav f ON i.bid = f.bid AND f.uid = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

// Close database connection
$mysqli->close();
?>


<?php
// Include database connection
include 'config.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve UID and BID from the request
    $uid = $_POST['uid'];
    $bid = $_POST['bid'];

    // Prepare and execute the SQL statement to insert into the fav table
    $stmt = $mysqli->prepare("INSERT INTO fav (uid, bid) VALUES (?, ?)");
    $stmt->bind_param("ii", $uid, $bid);
    $stmt->execute();

    // Check if insertion was successful
    if ($stmt->affected_rows > 0) {
        // Return a success response
        http_response_code(200);
    } else {
        // Return an error response
        http_response_code(500);
    }

    // Close statement and database connection
    $stmt->close();
    $mysqli->close();
}
?>
<?php
// Query to count overdued books for the current user
$query_overdued_books = "SELECT COUNT(*) AS overdued_count FROM rsv WHERE uid = ? AND status = 'Overdue'";
$stmt_overdued_books = $mysqli->prepare($query_overdued_books);
$stmt_overdued_books->bind_param("i", $uid);
$stmt_overdued_books->execute();
$result_overdued_books = $stmt_overdued_books->get_result();

// Initialize overdued books count
$overdued_count = 0;

// Check if the result is not empty
if ($result_overdued_books && $result_overdued_books->num_rows > 0) {
    $row_overdued_books = $result_overdued_books->fetch_assoc();
    $overdued_count = $row_overdued_books['overdued_count'];
}
?>
<?php
// Query to count available favorite items for the current user
$query_available_favorites = "SELECT COUNT(*) AS available_favorites_count 
                               FROM fav 
                               INNER JOIN inventory ON fav.bid = inventory.bid 
                               WHERE fav.uid = ? AND inventory.status = 'Available'";
$stmt_available_favorites = $mysqli->prepare($query_available_favorites);
$stmt_available_favorites->bind_param("i", $uid);
$stmt_available_favorites->execute();
$result_available_favorites = $stmt_available_favorites->get_result();

// Initialize available favorites count
$available_favorites_count = 0;

// Check if the result is not empty
if ($result_available_favorites && $result_available_favorites->num_rows > 0) {
    $row_available_favorites = $result_available_favorites->fetch_assoc();
    $available_favorites_count = $row_available_favorites['available_favorites_count'];
}

// Close the statement
$stmt_available_favorites->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Reserve/Borrow</title>
    <link rel="icon" type="image/x-icon" href="css/pics/logop.png">
    <link rel="stylesheet" href="css/user_rsrv.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<style>
    /* Cart container styles */
.cart-header {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 10px;
    color: #444;
}

.cart-content {
    background-color: #f9f9f9;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 15px;
    max-width: 400px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Cart items list */
#cart_items_list {
    list-style-type: none;
    padding: 0;
    margin: 0 0 15px 0;
}

#cart_items_list li {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    margin-bottom: 5px;
    font-size: 1rem;
    color: #333;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Submit and Cancel buttons */
.cart-content button {
    display: inline-block;
    font-size: 1rem;
    padding: 10px 15px;
    margin-right: 10px;
    color: #fff;
    background-color: #007bff;
    border: none;
    border-radius: 15px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.cart-content button#cancelButton {
    background-color: #dc3545;
}

.cart-content button:hover {
    background-color: #0056b3;
}

.cart-content button#cancelButton:hover {
    background-color: #c82333;
}
</style>
</head>
<body class="bg">
    <div class="navbar" style = "position: fixed; top: 0;">
        <div class="navbar-container">
            <img src="css/pics/logop.png" alt="Logo" class="logo">
            <p style="margin-left: 7%;">EasyLib</p>
        </div>
</div>

    <div class="sidebar">
        <div>
            <!-- Display Profile Image -->
            <a href="user_pf.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>"><img src="<?php echo $profile_image_path; ?>" alt="Profile Image" class="profile-image" style="width:100px; height:100px; border-radius:50%;"></a>
        </div>
        <div class="hell">Hello, <?php echo $user_username_display; ?>!</div>
        <a href="user_dash.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">Dashboard</a>
        <a href="user_rsrv.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item active">Reserve/Borrow</a>
        <a href="user_ovrd.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item" style="position: relative;">
    Overdue
    <?php if ($overdued_count > 0): ?>
        <span style="position: absolute; top: 10%; right: 5%; background-color: red; color: white; border-radius: 50%; padding: 0.2em 0.6em; font-size: 0.8em;">
            <?php echo $overdued_count; ?>
        </span>
    <?php endif; ?>
</a>
<a href="user_fav.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item" style="position: relative;">
    Favorites
    <?php if ($available_favorites_count > 0): ?>
        <span style="position: absolute; top: 10%; right: 5%; background-color: red; color: white; border-radius: 50%; padding: 0.2em 0.6em; font-size: 0.8em;">
            <?php echo $available_favorites_count; ?>
        </span>
    <?php endif; ?>
</a>
        <a href="user_sebk.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="sidebar-item">E-Books</a>
        <a href="login.php" class="logout-btn">Logout</a>
    </div>

    <div class="content">
        <nav class="secondary-navbar">
            <a href="user_rsrv.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="secondary-navbar-item active">Reserve Books</a>
            <a href="user_brwd.php<?php if(isset($uid)) echo '?uid=' . $uid; ?>" class="secondary-navbar-item">Borrowed</a>
        </nav>
    </div>

    <!-- Fixed-position date and time display -->
    <div class="fixed-date-time">
        <p>Date: <span id="current-date"><?php echo date("m-d-Y"); ?></span></p>
        <p>Time: <span id="current-time"></span></p>
    </div>

    <div class="content-container">
    <div class="search-bar">
        <input type="text" id="titleInput" placeholder="Search by Title...">
        <input type="text" id="authorInput" placeholder="Search by Author...">
        <select id="genreSelect">
        <option value="">Select Genre</option>
        <?php
        // Fetch genres from the inventory table
        $query = "SELECT DISTINCT genre FROM inventory";
        $result = $mysqli->query($query);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['genre'] . "'>" . $row['genre'] . "</option>";
            }
        }
        ?>
    </select>
        <button type="button" id="searchButton"><i class="fas fa-search"></i> Search</button>
        <button type="button" id="cancelSearchButton" style="background-color:crimson; color:white;">Cancel</button>
    </div>

        <form id="reserveForm" action="handle_reservation.php" method="POST">
        <input type="hidden" name="uid" value="<?php echo isset($uid) ? $uid : ''; ?>">
        <table>
    <thead>
        <tr>
            <th>#</th>
            <th hidden>Bid</th>
            <th>Title</th>
            <th>Author</th>
            <th>Genre</th>
            <th>Status</th>
            <th>Reserve</th>
            <th>Favorite</th>
            <th>More Info:</th>
        </tr>
    </thead>
    <tbody>
    <?php
// Fetch data from inventory table
$query = "SELECT i.bid, i.title, i.author, i.status, i.genre, f.uid 
          FROM inventory i 
          LEFT JOIN fav f ON i.bid = f.bid AND f.uid = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $counter++ . "</td>";
        echo "<td style='display: none;'>" . $row['bid'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>" . $row['author'] . "</td>";
        echo "<td>" . $row['genre'] . "</td>";
        echo "<td style='text-align:center;'>" . $row['status'] . "</td>";
        echo "<td style='text-align:center;'><button type='button' class='reserve-btn' data-bid='" . $row['bid'] . "' data-title='" . $row['title'] . "'>Reserve</button></td>";
        echo "<td style='text-align:center;'>";
        if ($row['uid']) {
            echo "<button type='button' class='favorite-btn' data-uid='" . $uid . "' data-bid='" . $row['bid'] . "'><i class='fas fa-heart'></i></button>";
        } else {
            echo "<button type='button' class='favorite-btn' data-uid='" . $uid . "' data-bid='" . $row['bid'] . "'><i class='far fa-heart'></i></button>";
        }
        echo "</td>";
        echo "<td style='text-align:center;'><button type='button' class='more-details-btn' data-bid='" . $row['bid'] . "'>More Details</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No books available.</td></tr>";
}

$mysqli->close();
?>
    </tbody>
</table>
    <div class="cart-header">Cart:</div>
    <div class="cart-content">
        <ul id="cart_items_list"></ul>
        <button type="submit" name="reserve_submit">Submit</button>
        <button type="button" id="cancelButton">Cancel</button>
    </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Include SweetAlert library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const cartItemsList = document.getElementById('cart_items_list');

    // Function to handle the click event of the Reserve button
    document.addEventListener('DOMContentLoaded', function () {
    const reserveButtons = document.querySelectorAll('.reserve-btn');
    const uid = new URLSearchParams(window.location.search).get('uid'); // Extract UID from URL

    reserveButtons.forEach(button => {
        button.addEventListener('click', async function () {
            const bid = this.getAttribute('data-bid');
            const title = this.getAttribute('data-title');
            const status = this.parentNode.previousElementSibling.textContent.trim();

            // Check status
            if (status === 'Not Reservable' || status === 'Reserved' || status === 'Overdue') {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: `This book is ${status.toLowerCase()}.`,
                });
                return;
            }

            try {
                // Fetch the current transactions count for the user
                const response = await fetch(`getUserTransactions.php?uid=${uid}`);
                const result = await response.json();

                if (!response.ok || !result.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Unable to fetch user transaction data. Please try again later.',
                    });
                    return;
                }

                const activeTransactions = result.activeCount;
                const remainingLimit = 5 - activeTransactions;

                if (remainingLimit <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Maximum Limit Reached',
                        text: 'You have reached the maximum limit of active transactions.',
                    });
                    return;
                }

                // Check if the book is already in the cart
                const cartItems = Array.from(cartItemsList.children);
                const isAlreadyInCart = cartItems.some(item => item.dataset.bid === bid);

                if (isAlreadyInCart) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Attention',
                        text: 'This book is already in the cart.',
                    });
                    return;
                }

                // Check if adding this book exceeds the remaining limit
                if (cartItems.length >= remainingLimit) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Maximum Limit Reached',
                        text: `You can only reserve ${remainingLimit} books.`,
                    });
                    return;
                }

                // Add the book to the cart list
                const listItem = document.createElement('li');
                listItem.textContent = `${title}`;
                listItem.dataset.bid = bid;
                cartItemsList.appendChild(listItem);

                Swal.fire({
                    icon: 'success',
                    title: 'Added to Cart',
                    text: 'Book successfully added to your cart.',
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing your request. Please try again later.',
                });
            }
        });
    });
});

    // Function to handle the click event of the Submit button
    const reserveForm = document.getElementById('reserveForm');
    const reserveSubmitButton = document.querySelector('[name="reserve_submit"]');
    reserveSubmitButton.addEventListener('click', function(event) {
        // Prevent the default form submission
        event.preventDefault();

        // Check if the cart is empty
        const cartItems = Array.from(cartItemsList.children);
        if (cartItems.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Your cart is empty. Please add books to your cart before submitting.',
            });
        } else {
            // Show reservation success alert
            Swal.fire({
                icon: 'success',
                title: 'Reservation Pending',
                text: 'Your reservation is now pending. After librarian confirmation, book is reserved for 3 days for you to claim.',
                showConfirmButton: false, // Remove the confirm button
                timer: 2000, // Timer for 2 seconds
            }).then(() => {
                // Populate a hidden input with cart items and submit the form
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'cart_items';
                hiddenInput.value = cartItems.map(item => item.dataset.bid).join(',');
                reserveForm.appendChild(hiddenInput);
                reserveForm.submit();
            });
        }
    });

    // Handle the Cancel button
    const cancelButton = document.getElementById('cancelButton');
    cancelButton.addEventListener('click', function () {
        cartItemsList.innerHTML = ''; // Clear the cart list
    });

    // Function to handle the click event of the Favorite button
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const uid = this.getAttribute('data-uid');
            const bid = this.getAttribute('data-bid');
            const icon = this.querySelector('i'); // Get the icon element

            // Send AJAX request to check if the combination of uid and bid already exists
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_favorites.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.exists) {
                            // Display alert if the combination already exists
                            Swal.fire({
                                icon: 'info',
                                title: 'Book is already in Favorites.',
                            });
                        } else {
                            // If combination does not exist, add to favorites
                            addToFavorites(uid, bid, icon); // Call addToFavorites function
                        }
                    } else {
                        // Display error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to check if book is in favorites.',
                        });
                    }
                }
            };
            xhr.send('uid=' + uid + '&bid=' + bid);
        });

    // Function to add to favorites
    function addToFavorites(uid, bid, icon) {
        // Send AJAX request to insert into fav table
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'handle_add_to_favorites.php', true); // Adjusted URL
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Change the icon to a solid star
                    icon.classList.remove('far'); // Remove the 'far' class
                    icon.classList.add('fas'); // Add the 'fas' class
                    // Show success alert
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Book added to Favorites.',
                    });
                } else {
                    // Display error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Failed to add to favorites.',
                        showConfirmButton: false, // Remove the confirm button
                        timer: 1500, // Timer for 1.5 seconds
                    }).then((result) => {
                        // Reload the page
                        location.reload();
                    });
                }
            }
        };
        xhr.send('uid=' + uid + '&bid=' + bid);
    }
});
    </script>
<script>
// Update the handleSearch function to include title, author, and genre search
function handleSearch() {
    const titleInput = document.getElementById('titleInput').value.toLowerCase();
    const authorInput = document.getElementById('authorInput').value.toLowerCase(); // Get author search input
    const genreSelect = document.getElementById('genreSelect').value.toLowerCase(); // Get selected genre
    const bookRows = document.querySelectorAll('table tbody tr');

    // Loop through each book row
    bookRows.forEach(row => {
        const titleText = row.querySelector('td:nth-child(3)').innerText.toLowerCase();
        const authorText = row.querySelector('td:nth-child(4)').innerText.toLowerCase(); // Get author text
        const genreText = row.querySelector('td:nth-child(5)').innerText.toLowerCase(); // Get genre text

        // Check if title, author, and genre match the search input
        if ((titleText.includes(titleInput) || titleInput === '') &&
            (authorText.includes(authorInput) || authorInput === '') &&
            (genreText.includes(genreSelect) || genreSelect === '')) {
            row.style.display = 'table-row'; // Display the row
        } else {
            row.style.display = 'none'; // Hide the row if it doesn't match the search input
        }
    });
}

// Add event listeners to the search button and input fields for title, author, and genre
document.getElementById('searchButton').addEventListener('click', handleSearch);
document.getElementById('titleInput').addEventListener('keyup', function(event) {
    if (event.keyCode === 13) { // Check if Enter key is pressed
        handleSearch();
    }
});
document.getElementById('authorInput').addEventListener('keyup', function(event) {
    if (event.keyCode === 13) { // Check if Enter key is pressed
        handleSearch();
    }
});
document.getElementById('genreSelect').addEventListener('change', handleSearch); // Add genre filter change event
</script>
<script>
        // Event listener for cancel search button
        document.getElementById('cancelSearchButton').addEventListener('click', function() {
            // Clear search inputs
            document.getElementById('titleInput').value = '';
            document.getElementById('authorInput').value = '';
            // Reset search results
            const bookRows = document.querySelectorAll('table tbody tr');
            bookRows.forEach(row => {
                row.style.display = 'table-row'; // Display all rows
            });
        });
    </script>

<script>
    // Function to update date and time
    function updateTime() {
        var currentDate = new Date();
        var month = (currentDate.getMonth() + 1).toString().padStart(2, '0'); // Adding 1 to month since it's zero-based index
        var day = currentDate.getDate().toString().padStart(2, '0');
        var year = currentDate.getFullYear().toString();
        var dateString = month + '-' + day + '-' + year;
        var timeString = currentDate.toLocaleTimeString();
        document.getElementById("current-date").textContent = dateString;
        document.getElementById("current-time").textContent = timeString;
    }
    
    updateTime();// Call the function to update time immediately
    setInterval(updateTime, 1000);// Update time every second
</script>
<script>
    // Add event listener for "More Details" buttons
document.addEventListener('DOMContentLoaded', function() {
    const moreDetailsButtons = document.querySelectorAll('.more-details-btn');
    moreDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bid = this.getAttribute('data-bid');
            
            // Fetch book details using AJAX
            fetch(`get_book_details.php?bid=${bid}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        // Show error alert if book details could not be retrieved
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.error,
                        });
                    } else {
                        // Show book details in a SweetAlert2 popup
                        Swal.fire({
                            title: data.title,
                            html: `
                            <span style="text-align:left;">
                                <p><strong>Author:</strong> ${data.author}</p>
                                <p><strong>Year:</strong> ${data.year}</p>
                                <p><strong>Genre:</strong> ${data.genre}</p>
                                <p><strong>Dewey Number:</strong> ${data.dew_num}</p>
                                <p><strong>ISBN:</strong> ${data.ISBN}</p>
                                <p><strong>Shelf Number:</strong> ${data.shlf_num}</p>
                                <p><strong>Condition:</strong> ${data.cndtn}</p>
                                <p><strong>Cataloging Notes:</strong> ${data.cataloging_notes}</p>
                                <p><strong>Status:</strong> ${data.status}</p>
                            </span>
                            `,
                            confirmButtonText: 'Close'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Failed to fetch book details.',
                    });
                });
        });
    });
});

</script>
</body>
</html>
