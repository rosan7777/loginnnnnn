
<?php
session_start();
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include("connection/connection.php");

function generateOTP($conn) {
    $attempts = 0;
    while ($attempts < 10) {
        $otp = sprintf("%06d", random_int(0, 999999));
        $check = oci_parse($conn, "SELECT COUNT(*) FROM TRADER WHERE VERIFICATION_CODE = :code");
        oci_bind_by_name($check, ":code", $otp);
        oci_execute($check);
        $row = oci_fetch_row($check);
        if ($row[0] == 0) return $otp;
        $attempts++;
    }
    throw new Exception("Failed to generate unique OTP after 10 attempts.");
}

function sendOTP($email, $user_id, $conn) {
    try {
        $otp = generateOTP($conn);
        $update = oci_parse($conn, "UPDATE TRADER SET VERIFICATION_CODE = :code WHERE USER_ID = :user_id");
        oci_bind_by_name($update, ":code", $otp);
        oci_bind_by_name($update, ":user_id", $user_id);
        oci_execute($update);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'adhikariroshankumar7@gmail.com';
        $mail->Password = 'nbei mnqe qgvp lpcy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your_email@gmail.com', 'ClickFax Traders');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code';
        $mail->Body = "Your verification code is: <b>$otp</b>. It expires in 10 minutes.";

        $mail->send();
        $_SESSION['trader_otp_sent_time'] = time();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

if (isset($_GET['user_id']) && isset($_GET['email'])) {
    $user_id = filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $email = filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);
    $_SESSION['trader_user_id'] = $user_id;
    $_SESSION['trader_email'] = $email;

    if (!isset($_SESSION['trader_otp_sent_time'])) {
        sendOTP($email, $user_id, $conn);
    }

    if (isset($_GET['resend'])) {
        if (time() - $_SESSION['trader_otp_sent_time'] < 60) {
            $resend_error = "Please wait 60 seconds before resending OTP.";
        } else {
            if (sendOTP($email, $user_id, $conn)) {
                $resend_success = "OTP resent successfully!";
            } else {
                $resend_error = "Failed to resend OTP.";
            }
        }
    }

    if (isset($_POST['verify'])) {
        $code = trim($_POST['verification_code']);
        $stmt = oci_parse($conn, "SELECT VERIFICATION_CODE FROM TRADER WHERE USER_ID = :user_id");
        oci_bind_by_name($stmt, ":user_id", $user_id);
        oci_execute($stmt);
        $row = oci_fetch_assoc($stmt);

        if ($row && $row['VERIFICATION_CODE'] === $code) {
           $update = oci_parse($conn, "UPDATE TRADER SET VERIFICATION_STATUS = 1, VERIFICATION_CODE = NULL WHERE USER_ID = :user_id");
            oci_bind_by_name($update, ":user_id", $user_id);
            oci_execute($update);
            unset($_SESSION['trader_otp_sent_time']);
            header("Location: customer_signin.php");
            exit();
        } else {
            $verification_error = "Incorrect verification code!";
        }
    }
} else {
    header("Location: customer_signin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Trader Email Verification</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  <style>
    body {
      background-color: #f0f8ff;
      font-family: Arial, sans-serif;
    }
    .verify-container {
      max-width: 500px;
      margin: 3rem auto;
      background-color: #fff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      padding: 2rem;
      border-radius: 8px;
    }
    .error-message {
      color: #ff3860;
      text-align: center;
      margin-top: 1rem;
    }
    .success-message {
      color: #23d160;
      text-align: center;
      margin-top: 1rem;
    }
    .resend-link {
      display: block;
      text-align: center;
      margin-top: 1rem;
      color: #3273dc;
    }
  </style>
</head>
<body>
  <section class="section">
    <div class="verify-container">
      <h2 class="title has-text-centered">Verify Your Email</h2>
      <p class="has-text-centered">Please enter the verification code sent to your email: <strong><?php echo htmlspecialchars($email); ?></strong></p>

      <?php if (!empty($verification_error)) { ?>
        <p class="error-message"><?php echo htmlspecialchars($verification_error); ?></p>
      <?php } elseif (!empty($resend_error)) { ?>
        <p class="error-message"><?php echo htmlspecialchars($resend_error); ?></p>
      <?php } elseif (!empty($resend_success)) { ?>
        <p class="success-message"><?php echo htmlspecialchars($resend_success); ?></p>
      <?php } ?>

      <form method="POST">
        <div class="field">
          <label class="label">Verification Code</label>
          <div class="control">
            <input class="input" type="text" name="verification_code" maxlength="6" placeholder="Enter 6-digit code" required>
          </div>
        </div>
        <div class="field">
          <button class="button is-primary is-fullwidth" name="verify" type="submit">Verify Code</button>
        </div>
      </form>

      <a class="resend-link" href="?user_id=<?php echo urlencode($user_id); ?>&email=<?php echo urlencode($email); ?>&resend=1">Didn't receive the code? Resend Code</a>
    </div>
  </section>
</body>
</html>
