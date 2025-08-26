<?php
    if (!isset($_SESSION['isLogedin']) || $_SESSION['isLogedin'] != 1) {
        header("Location: /login");
        exit;
    }
    
?>
<?php
$message = ""; // Variable to store error or success messages

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    try {
        $db = new PDO("mysql:host=$mariaServer;dbname=$mariaDb", $mariaUser, $mariaPass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch input values
        $email = $_SESSION['userEmail'];
        $currentPassword = md5($_POST['current_password']); // Encrypt current password
        $newPassword = md5($_POST['new_password']);
        $confirmPassword = md5($_POST['confirm_password']);

        // Check if current password matches the database
        $stmt = $db->prepare("SELECT password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['password'] !== $currentPassword) {
            $message = '<div class="alert alert-danger">Current password is incorrect. Please try again.</div>';
        } elseif ($newPassword !== $confirmPassword) {
            $message = '<div class="alert alert-danger">New passwords do not match. Please try again.</div>';
        } else {
            // Update password
            $stmt = $db->prepare("UPDATE users SET password = :password WHERE email = :email");
            $stmt->bindParam(':password', $newPassword, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Password updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Failed to update password. Try again later.</div>';
            }
        }

    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>

<style>

    .profile-container {
        max-width: 600px;
        margin: 80px auto;
    }
    .profile-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }
    .profile-header {
        text-align: center;
        margin-bottom: 20px;
    }
    .profile-header img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin-bottom: 10px;
    }
    .btn-primary {
        background: #394fc0;
        border: none;
    }
    .btn-primary:hover {
        background: #2e3b8c;
    }
</style>


<div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <svg style="background-color: #37415110; border: 2px solid #374151;border-radius: 50%; padding: 3px; box-shadow: 0 0 10px 0;" width="100px" height="100px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <defs> <style>.cls-1{fill:#374151;}.cls-2{fill:#374151;}</style> </defs> <title></title> <g id="fill"> <circle class="cls-1" cx="16" cy="7.48" r="5.48"></circle> <path class="cls-2" d="M23.54,30H8.46a4.8,4.8,0,0,1-4.8-4.8h0A10.29,10.29,0,0,1,13.94,14.92h4.12A10.29,10.29,0,0,1,28.34,25.2h0A4.8,4.8,0,0,1,23.54,30Z"></path> </g> </g></svg>
            <h3 class="fw-bold mt-2">Welcome, <?php echo $_SESSION['userName']; ?>!</h3>
            <p class="text-muted">Email: <?php echo $_SESSION['userEmail']; ?></p>
        </div>
        <div class="mt-3">
            <h5>Change Password</h5><hr>
            <?= $message ?>
            <form method="POST">
                <div class="mb-3">
                    <input type="password" class="form-control" id="currentPassword" name="current_password" placeholder="Current Password" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="New Password" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm New Password" required>
                </div>
                <button name="update_password" type="submit" class="btn w-100 text-white" style="background-color: #374151">Update Password</button>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector("form");
        const newPassword = document.getElementById("newPassword");
        const confirmPassword = document.getElementById("confirmPassword");
        const submitBtn = document.querySelector("button[type='submit']");
        const errorMessage = document.createElement("small");

        errorMessage.style.color = "red";
        confirmPassword.parentNode.appendChild(errorMessage);

        confirmPassword.addEventListener("input", function () {
            if (newPassword.value !== confirmPassword.value) {
                errorMessage.style.color = "red";
                errorMessage.textContent = "Passwords do not match!";
                submitBtn.disabled = true;
            } else {
                errorMessage.style.color = "green";
                errorMessage.textContent = "Passwords matched!";
                submitBtn.disabled = false;
            }
        });

        form.addEventListener("submit", function (e) {
            if (newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                errorMessage.textContent = "Passwords do not match!";
                submitBtn.disabled = true;
            }
        });
    });
</script>
