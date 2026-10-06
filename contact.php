<?php
// contact.php
session_start();

// Database connection
require_once 'config.php';


// Handle form submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($message) < 10) {
        $error = 'Message should be at least 10 characters long.';
    } else {
        try {
            // Prepare SQL statement
            $sql = "INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent, status, created_at) 
                    VALUES (:name, :email, :subject, :message, :ip_address, :user_agent, 'unread', NOW())";
            
            $stmt = $pdo->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
            $stmt->bindParam(':message', $message, PDO::PARAM_STR);
            $stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
            $stmt->bindParam(':user_agent', $user_agent, PDO::PARAM_STR);
            
            // Execute the query
            if ($stmt->execute()) {
                $message_id = $pdo->lastInsertId();
                $success = true;
                
                // Optional: Send email notification
                // $this->sendNotificationEmail($name, $email, $subject, $message);
                
            } else {
                $error = 'There was an error saving your message. Please try again.';
            }
            
        } catch (PDOException $e) {
            // Log the error
            error_log("Contact Form Error: " . $e->getMessage());
            $error = 'A system error occurred. Please try again later or contact support directly.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MatchTicketing</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/contact-style.css">
 
</head>
<body>
    <!-- ===== Navigation ===== -->
    <nav class="navbar">
        <div class="container">
            <a href="index.html" class="logo">
                <i class="fas fa-ticket-alt"></i>
                <span>MatchTicketing</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.html"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="tickets.php" class="active"><i class="fas fa-ticket-alt"></i> Match Tickets</a></li>
                <li><a href="hotels.php"><i class="fas fa-hotel"></i> Hotels</a></li>
                <li><a href="transport.php"><i class="fas fa-bus"></i> Transport</a></li>
                 <li><a href="tour-guides.php"><i class="fas fa-bus"></i> Tour Guides</a></li>
                <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php" class="btn-login">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['full_name'] ?? 'Dashboard'; ?>
                    </a></li>
                    <li><a href="logout.php" class="btn-register">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a></li>
                    <li><a href="register.php" class="btn-register">
                        <i class="fas fa-user-plus"></i> Register
                    </a></li>
                <?php endif; ?>
            </ul>
            <div class="mobile-menu">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header fade-in">
        <div class="container">
            <h1>Contact Us</h1>
            <p>Get in touch with our support team. We're here to help you with any questions or concerns.</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="contact-container">
            <?php if ($success): ?>
            <div class="alert-success fade-in">
                <i class="fas fa-check-circle fa-2x"></i>
                <div>
                    <h4 class="mb-2">Message Sent Successfully!</h4>
                    <p class="mb-0">Thank you for contacting us. Your message has been saved (Reference ID: <?php echo $message_id ?? 'N/A'; ?>). We'll get back to you within 24 hours.</p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert-danger fade-in">
                <i class="fas fa-exclamation-circle fa-2x"></i>
                <div>
                    <h4 class="mb-2">Error</h4>
                    <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="contact-grid fade-in">
                <!-- Contact Information -->
                <div class="contact-info">
                    <h2 class="section-title mb-4">Get in Touch</h2>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="contact-card">
                                <i class="fas fa-map-marker-alt"></i>
                                <h3>Visit Us</h3>
                                <p>123 Stadium Avenue</p>
                                <p>Sports City, SC 10001</p>
                                <p>United States</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="contact-card">
                                <i class="fas fa-phone"></i>
                                <h3>Call Us</h3>
                                <p>+1 (234) 567-8900</p>
                                <p>Mon-Fri: 9AM-6PM</p>
                                <p>Sat-Sun: 10AM-4PM</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="contact-card">
                                <i class="fas fa-envelope"></i>
                                <h3>Email Us</h3>
                                <p>support@matchticketing.com</p>
                                <p>sales@matchticketing.com</p>
                                <p>info@matchticketing.com</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="contact-card">
                                <i class="fas fa-headset"></i>
                                <h3>Live Support</h3>
                                <p>24/7 Customer Support</p>
                                <p>Live Chat Available</p>
                                <p>Quick Response Time</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="contact-form">
                    <h2 class="section-title mb-4">Send us a Message</h2>
                    
                    <form method="POST" id="contactForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="form-group">
                            <label class="form-label" for="name">
                                <i class="fas fa-user"></i> Full Name *
                            </label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   placeholder="Enter your full name" required
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                   minlength="2" maxlength="100">
                            <small class="text-muted">Minimum 2 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="email">
                                <i class="fas fa-envelope"></i> Email Address *
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="Enter your email address" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="subject">
                                <i class="fas fa-tag"></i> Subject *
                            </label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="General Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Ticket Support" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Ticket Support') ? 'selected' : ''; ?>>Ticket Support</option>
                                <option value="Hotel Booking" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Hotel Booking') ? 'selected' : ''; ?>>Hotel Booking</option>
                                <option value="Transportation" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Transportation') ? 'selected' : ''; ?>>Transportation</option>
                                <option value="Payment Issue" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Payment Issue') ? 'selected' : ''; ?>>Payment Issue</option>
                                <option value="Technical Support" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Technical Support') ? 'selected' : ''; ?>>Technical Support</option>
                                <option value="Feedback" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Feedback') ? 'selected' : ''; ?>>Feedback</option>
                                <option value="Other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="message">
                                <i class="fas fa-comment"></i> Message *
                            </label>
                            <textarea class="form-control" id="message" name="message" 
                                      placeholder="Enter your message here..." required
                                      rows="5" minlength="10" maxlength="2000"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            <small class="text-muted">Minimum 10 characters, maximum 2000 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn-primary w-100" id="submitBtn">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </div>
                        
                        <div class="form-group text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Your message will be stored in our database and our team will respond within 24 hours.
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2 class="section-title fade-in">Frequently Asked Questions</h2>
            
            <div class="accordion fade-in delay-1">
                <!-- FAQ 1 -->
                <div class="accordion-item">
                    <button class="accordion-button">
                        How can I purchase match tickets?
                        <span></span>
                    </button>
                    <div class="accordion-content">
                        <p>You can purchase match tickets by visiting our Tickets page, selecting your desired match, choosing your seats, and completing the checkout process. We accept all major credit cards, PayPal, and other secure payment methods.</p>
                    </div>
                </div>
                
                <!-- FAQ 2 -->
                <div class="accordion-item">
                    <button class="accordion-button">
                        Can I cancel or reschedule my hotel booking?
                        <span></span>
                    </button>
                    <div class="accordion-content">
                        <p>Yes, most hotel bookings can be cancelled or modified up to 48 hours before check-in without any penalty. Please check the specific cancellation policy of your chosen hotel at the time of booking.</p>
                    </div>
                </div>
                
                <!-- FAQ 3 -->
                <div class="accordion-item">
                    <button class="accordion-button">
                        What transportation options are available?
                        <span></span>
                    </button>
                    <div class="accordion-content">
                        <p>We offer various transportation options including stadium shuttles, private car services, metro tickets, and rental cars. You can book transportation along with your match tickets or separately through our Transport page.</p>
                    </div>
                </div>
                
                <!-- FAQ 4 -->
                <div class="accordion-item">
                    <button class="accordion-button">
                        Is my payment information secure?
                        <span></span>
                    </button>
                    <div class="accordion-content">
                        <p>Absolutely! We use industry-standard SSL encryption to protect all your personal and payment information. We never store your credit card details on our servers.</p>
                    </div>
                </div>
                
                <!-- FAQ 5 -->
                <div class="accordion-item">
                    <button class="accordion-button">
                        How can I get a refund for my tickets?
                        <span></span>
                    </button>
                    <div class="accordion-content">
                        <p>Refund policies vary by event organizer. Generally, tickets are non-refundable unless the event is cancelled or postponed. Please contact our support team with your booking details for specific refund inquiries.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- Footer -->
    <footer class="fade-in">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>MatchTicketing</h3>
                    <p>Your one-stop solution for sports events</p>
                    <p><i class="fas fa-envelope"></i> support@matchticketing.com</p>
                    <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Stadium Avenue, Sports City</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.html">Home</a></li>
                        <li><a href="tickets.php">Tickets</a></li>
                        <li><a href="hotels.php">Hotels</a></li>
                        <li><a href="transport.php">Transport</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <a href="#" class="text-white" style="font-size: 1.2rem;"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white" style="font-size: 1.2rem;"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white" style="font-size: 1.2rem;"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white" style="font-size: 1.2rem;"><i class="fab fa-linkedin"></i></a>
                    </div>
                    <div class="mt-4">
                        <h4>Newsletter</h4>
                        <p class="small">Subscribe for updates and offers</p>
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your email">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 MatchTicketing. All rights reserved. | 
                   <a href="privacy.php" class="text-light">Privacy Policy</a> | 
                   <a href="terms.php" class="text-light">Terms of Service</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.querySelector('.mobile-menu');
            const navLinks = document.querySelector('.nav-links');
            
            if (mobileMenu) {
                mobileMenu.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                });
            }

            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.navbar')) {
                    navLinks.classList.remove('active');
                }
            });

            // Add active class to current page link
            const currentPage = window.location.pathname.split('/').pop();
            const navLinksList = document.querySelectorAll('.nav-links a');
            
            navLinksList.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });

            // FAQ Accordion
            const accordionButtons = document.querySelectorAll('.accordion-button');
            
            accordionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const content = this.nextElementSibling;
                    
                    // Close all other accordion items
                    accordionButtons.forEach(otherButton => {
                        if (otherButton !== this) {
                            otherButton.classList.remove('active');
                            otherButton.nextElementSibling.classList.remove('active');
                        }
                    });
                    
                    // Toggle current item
                    this.classList.toggle('active');
                    content.classList.toggle('active');
                });
            });

            // Form validation and submission
            const contactForm = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            
            if (contactForm && submitBtn) {
                // Client-side validation
                contactForm.addEventListener('submit', function(e) {
                    const name = document.getElementById('name').value.trim();
                    const email = document.getElementById('email').value.trim();
                    const subject = document.getElementById('subject').value;
                    const message = document.getElementById('message').value.trim();
                    
                    let isValid = true;
                    let errorMessage = '';
                    
                    // Validate name
                    if (name.length < 2) {
                        isValid = false;
                        errorMessage = 'Name must be at least 2 characters long.';
                    }
                    
                    // Validate email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address.';
                    }
                    
                    // Validate subject
                    if (!subject) {
                        isValid = false;
                        errorMessage = 'Please select a subject.';
                    }
                    
                    // Validate message
                    if (message.length < 10) {
                        isValid = false;
                        errorMessage = 'Message must be at least 10 characters long.';
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert(errorMessage);
                        return false;
                    }
                    
                    // Show loading state
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                    submitBtn.disabled = true;
                    
                    // Allow form submission to proceed
                });
            }

            // Auto-hide success message after 5 seconds
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.opacity = '0';
                    successAlert.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        successAlert.style.display = 'none';
                    }, 500);
                }, 5000);
            }

            // Auto-hide error message after 10 seconds
            const errorAlert = document.querySelector('.alert-danger');
            if (errorAlert) {
                setTimeout(() => {
                    errorAlert.style.opacity = '0';
                    errorAlert.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        errorAlert.style.display = 'none';
                    }, 500);
                }, 10000);
            }
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observe all fade-in elements
        document.querySelectorAll('.fade-in').forEach(element => {
            observer.observe(element);
        });
    </script>
</body>
</html>