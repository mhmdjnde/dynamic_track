<?php
// 1) Autoload via Composer
require __DIR__ . '/vendor/autoload.php';

// 2) Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 3) Collect form data from POST
$firstName    = $_POST['firstName'] ?? '';
$lastName     = $_POST['lastName']  ?? '';
$email        = $_POST['email']     ?? '';
$mailSubject  = $_POST['subject']   ?? '';
$body         = $_POST['message']   ?? '';

// Where you (admin) want to receive the user’s message
$to = "dynamictrack17@gmail.com";  // <-- Put your admin email address here

// Auto-reply subject/body
$autoReplySubject = "Thank you for your message!";
$autoReplyBody    = "Hello $firstName,\n\nWe received your message and will get back to you soon.\n\nBest regards,\nDynamicTrack Team";

// 4) Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Debugging (optional). Uncomment if needed:
    // $mail->SMTPDebug = 2;
    // $mail->Debugoutput = 'html';

    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';      // Replace with your SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'dynamictrack17@gmail.com'; // Replace with your SMTP username
    $mail->Password   = 'yfigmcoxgugrbtru';    // Replace with your SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // or PHPMailer::ENCRYPTION_SMTPS for SSL
    $mail->Port       = 587;                     // Adjust as needed (587 for TLS, 465 for SSL)

    // -- Send the main email to admin (you)
    $mail->setFrom('dynamictrack17@gmail.com', 'DynamicTrack');       // "From" address/label
    $mail->addAddress($to);                                     // Admin recipient
    $mail->addReplyTo($email, $firstName . ' ' . $lastName);    // Reply-To is the user

    // Email content
    $mail->isHTML(false);                       // Plain text (change to true if you want HTML)
    $mail->Subject = $mailSubject;
    $mail->Body    = $body;
    $mail->send();                              // Send the main message

    // -- Now send auto-reply to the user
    $mail->clearAddresses();                    // Remove previous recipient(s)
    $mail->addAddress($email);                  // The user’s email
    $mail->Subject = $autoReplySubject;
    $mail->Body    = $autoReplyBody;
    $mail->send();                              // Send the auto-reply

    $submissionResult = "Thank you! Your message has been sent successfully.";
} catch (Exception $e)
{
    // In production, you might hide $mail->ErrorInfo for security
    $submissionResult = "Error: Failed to send your message. Please try again later. " 
                        . $mail->ErrorInfo;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact Us | Fitness Website</title>
  <link rel="stylesheet" href="styles.css"/>
  <!-- EMBEDDED CSS -->
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar">
    <a href="#" class="logo">DynamicTrack</a>
    <div class="menu-toggle" onclick="toggleMenu()">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <div class="nav-links">
      <a href="../index.php">Home</a>
      <a href="../ExercisePage/index.html">Workouts</a>
      <a href="../primarypage/Primary.php">Nutrition</a>
      <a href="../ContactPage/contact.php">Contact</a>
    </div>
  </nav>

  <!-- MAIN CONTENT CONTAINER -->
  <div class="main-content">
    <section class="contact-section">
      <h1 class="animate-fade-in-down">Get in Touch</h1>
      <p class="animate-fade-in-down-delay">
        We'd love to hear from you. Please fill out the form below and we'll get back to you soon!
      </p>

      <!-- CONTACT FORM -->
      <form 
        id="contactForm"
        class="contact-form animate-fade-in-up shake-on-error"
        action="contact.php" 
        method="POST"
      >
        <div class="form-group">
          <label for="firstName">First Name <span>*</span></label>
          <input 
            type="text" 
            id="firstName" 
            name="firstName"
            required 
            placeholder="First Name"
          >
        </div>

        <div class="form-group">
          <label for="lastName">Last Name <span>*</span></label>
          <input 
            type="text" 
            id="lastName" 
            name="lastName"
            required
            placeholder="Last Name"
          >
        </div>

        <div class="form-group">
          <label for="email">Email <span>*</span></label>
          <input 
            type="email" 
            id="email" 
            name="email"
            required
            placeholder="example@example.com"
          >
        </div>

        <div class="form-group">
          <label for="subject">Subject <span>*</span></label>
          <input 
            type="text" 
            id="subject" 
            name="subject"
            required
            placeholder="Subject of your message"
          >
        </div>

        <div class="form-group">
          <label for="message">Message <span>*</span></label>
          <textarea 
            id="message" 
            name="message" 
            rows="5" 
            required
            placeholder="Write your message here..."
          ></textarea>
        </div>

        <button type="submit" class="btn-submit">Send Message</button>
      </form>
    </section>
  </div>
  <script src="script.js"></script>
</body>
</html>
