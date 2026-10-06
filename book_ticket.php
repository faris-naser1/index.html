<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get match_id from URL if coming from tickets page
$selected_match_id = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;

// Fetch all matches from database
$matches = getAllMatches();

$success = false;
$ticket_data = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $match_id = isset($_POST['match_id']) ? (int)$_POST['match_id'] : 0;
    $seat_category = $_POST['seat_category'];
    $quantity = (int)$_POST['quantity'];
    $payment_method = $_POST['payment_method'];
    
    // Fetch match details from database
    $match = getMatchById($match_id);
    
    if (!$match) {
        setAlert('error', 'Match not found');
        header('Location: book_ticket_simple.php');
        exit;
    }
    
    // Check if enough tickets available
    if ($match['available_tickets'] < $quantity) {
        setAlert('error', 'Not enough tickets available. Only ' . $match['available_tickets'] . ' tickets left.');
        header('Location: book_ticket_simple.php?match_id=' . $match_id);
        exit;
    }
    
    // Calculate prices
    $prices = [
        'standard' => $match['base_price'],
        'premium' => $match['base_price'] * 1.5,
        'vip' => $match['base_price'] * 2,
        'corporate' => $match['base_price'] * 3
    ];
    
    $unit_price = $prices[$seat_category] ?? $match['base_price'];
    $total_amount = $unit_price * $quantity;
    
    try {
        // Process payment
        $payment = processPayment($total_amount, $payment_method);
        
        if ($payment['success']) {
            // Insert ticket into database
            $stmt = $pdo->prepare("INSERT INTO tickets (
                user_id, match_id, team1, team2, match_date,  venue, 
                seat_category, quantity, unit_price, total_amount,
                payment_method, transaction_id, booking_date, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,  ?, ?, NOW(), 'confirmed')");
            
            $stmt->execute([
                $user_id, $match_id, $match['team1'], $match['team2'], 
                $match['match_date'],  $match['stadium'],
                $seat_category, $quantity, $unit_price, $total_amount,
                $payment_method, $payment['transaction_id']
            ]);
            
            $ticket_id = $pdo->lastInsertId();
            
            // Update available tickets
            $new_available = $match['available_tickets'] - $quantity;
            $updateStmt = $pdo->prepare("UPDATE matches SET available_tickets = ? WHERE id = ?");
            $updateStmt->execute([$new_available, $match_id]);
            
            // Store ticket data for display
            $ticket_data = [
                'ticket_id' => $ticket_id,
                'match_name' => $match['team1'] . ' vs ' . $match['team2'],
                'match_date' => $match['match_date'],
                 'venue' => $match['stadium'],
                'seat_category' => $seat_category,
                'quantity' => $quantity,
                'unit_price' => $unit_price,
                'total_amount' => $total_amount,
                'payment_method' => $payment_method,
                'transaction_id' => $payment['transaction_id'],
                'booking_date' => date('Y-m-d H:i:s')
            ];
            
            $success = true;
            setAlert('success', 'Ticket booked successfully!');
        } else {
            setAlert('error', 'Payment failed. Please try again.');
        }
        
    } catch (Exception $e) {
        setAlert('error', 'Error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ticket - MatchTicketing</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #283593;
            --accent: #ff6b35;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }
        
        .booking-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 2rem auto;
        }
        
        .section-title {
            color: var(--primary);
            border-bottom: 3px solid var(--accent);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
        }
        
        .btn-booking {
            background: linear-gradient(to right, var(--accent), #ff8b35);
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-booking:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255,107,53,0.3);
        }
        
        .ticket-preview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .payment-method {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 10px;
        }
        
        .payment-method:hover {
            border-color: var(--primary);
            background: rgba(26,35,126,0.05);
        }
        
        .payment-method.selected {
            border-color: var(--primary);
            background: rgba(26,35,126,0.1);
        }
        
        .success-card {
            background: white;
            border-radius: 15px;
            border-top: 5px solid #28a745;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .ticket-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .match-option {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .match-option:hover {
            border-color: var(--primary);
            background: rgba(26,35,126,0.05);
        }
        
        .match-option.selected {
            border-color: var(--primary);
            background: rgba(26,35,126,0.1);
        }
        
        .availability-badge {
            font-size: 0.8rem;
            padding: 3px 8px;
            border-radius: 10px;
        }
        
        .availability-high {
            background: #d4edda;
            color: #155724;
        }
        
        .availability-medium {
            background: #fff3cd;
            color: #856404;
        }
        
        .availability-low {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-ticket-alt"></i> MatchTicketing
            </a>
        </div>
    </nav>

    <div class="container py-4">
        <?php if($alert = getAlert()): ?>
        <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show">
            <i class="fas fa-<?php echo $alert['type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $alert['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if($success && $ticket_data): ?>
        <!-- Success Message -->
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check fa-2x text-white"></i>
            </div>
            <h2 class="text-success mb-3">Booking Successful!</h2>
            <p class="lead mb-4">Your ticket has been booked and payment processed successfully.</p>
            
            <!-- Ticket Details -->
            <div class="ticket-details text-start">
                <h4 class="mb-3">Ticket Details</h4>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Ticket ID:</strong> <?php echo $ticket_data['ticket_id']; ?></p>
                        <p><strong>Match:</strong> <?php echo htmlspecialchars($ticket_data['match_name']); ?></p>
                        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($ticket_data['match_date'])); ?></p>
                      
                    </div>
                    <div class="col-md-6">
                        <p><strong>Venue:</strong> <?php echo htmlspecialchars($ticket_data['venue']); ?></p>
                        <p><strong>Seat Category:</strong> <?php echo ucfirst($ticket_data['seat_category']); ?></p>
                        <p><strong>Quantity:</strong> <?php echo $ticket_data['quantity']; ?></p>
                        <p><strong>Unit Price:</strong> $<?php echo number_format($ticket_data['unit_price'], 2); ?></p>
                        <p><strong>Total Amount:</strong> $<?php echo number_format($ticket_data['total_amount'], 2); ?></p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Payment Method:</strong> <?php echo ucfirst($ticket_data['payment_method']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Transaction ID:</strong> <?php echo $ticket_data['transaction_id']; ?></p>
                    </div>
                </div>
                <p class="text-muted mt-3"><small>Booking Date: <?php echo $ticket_data['booking_date']; ?></small></p>
            </div>
            
            <div class="mt-4">
                <a href="tickets.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-ticket-alt"></i> Book More Tickets
                </a>
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-user"></i> View in Dashboard
                </a>
            </div>
        </div>
        
        <?php else: ?>
        
        <!-- Booking Form -->
        <div class="booking-card p-4">
            <h2 class="section-title">Book Your Ticket</h2>
            
            <form method="POST" id="bookingForm">
                <!-- Match Selection -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Select Match</label>
                        <?php if(empty($matches)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> No matches available at the moment.
                            </div>
                        <?php else: ?>
                            <select class="form-select" name="match_id" id="match_id" required>
                                <option value="">Select a match...</option>
                                <?php foreach($matches as $match): 
                                    $match_date = date('M j, Y', strtotime($match['match_date']));
                         
                                    
                                    // Determine availability badge
                                    if ($match['available_tickets'] > 100) {
                                        $availability_class = 'availability-high';
                                        $availability_text = 'Good availability';
                                    } elseif ($match['available_tickets'] > 50) {
                                        $availability_class = 'availability-medium';
                                        $availability_text = 'Limited availability';
                                    } else {
                                        $availability_class = 'availability-low';
                                        $availability_text = 'Few tickets left';
                                    }
                                    
                                    $isSelected = ($selected_match_id == $match['id']) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $match['id']; ?>" 
                                        data-base-price="<?php echo $match['base_price']; ?>"
                                        data-available="<?php echo $match['available_tickets']; ?>"
                                        <?php echo $isSelected; ?>>
                                    <?php echo htmlspecialchars($match['team1']); ?> vs <?php echo htmlspecialchars($match['team2']); ?>
                                    - <?php echo $match_date; ?> 
                                    (<?php echo htmlspecialchars($match['stadium']); ?>)
                                    <span class="<?php echo $availability_class; ?> availability-badge">
                                        <?php echo $match['available_tickets']; ?> available
                                    </span>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Number of Tickets</label>
                        <select class="form-select" name="quantity" id="quantity" required>
                            <?php for($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> ticket<?php echo $i > 1 ? 's' : ''; ?></option>
                            <?php endfor; ?>
                        </select>
                        <div class="mt-2">
                            <small class="text-muted" id="availability_note"></small>
                        </div>
                    </div>
                </div>
                
                <!-- Seat Category -->
                <div class="mb-4">
                    <label class="form-label">Select Seat Category</label>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="payment-method" data-category="standard">
                                <div class="text-center">
                                    <i class="fas fa-chair fa-2x text-primary mb-2"></i>
                                    <h6>Standard</h6>
                                    <p class="small text-muted" id="standard_price">Loading...</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="payment-method" data-category="premium">
                                <div class="text-center">
                                    <i class="fas fa-crown fa-2x text-warning mb-2"></i>
                                    <h6>Premium</h6>
                                    <p class="small text-muted" id="premium_price">Loading...</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="payment-method" data-category="vip">
                                <div class="text-center">
                                    <i class="fas fa-star fa-2x text-danger mb-2"></i>
                                    <h6>VIP</h6>
                                    <p class="small text-muted" id="vip_price">Loading...</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="payment-method" data-category="corporate">
                                <div class="text-center">
                                    <i class="fas fa-building fa-2x text-success mb-2"></i>
                                    <h6>Corporate</h6>
                                    <p class="small text-muted" id="corporate_price">Loading...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="seat_category" id="seat_category" value="standard" required>
                </div>
                
                <!-- Payment Method -->
                <div class="mb-4">
                    <label class="form-label">Select Payment Method</label>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="payment-method" data-method="card">
                                <div class="text-center">
                                    <i class="fas fa-credit-card fa-2x text-primary mb-2"></i>
                                    <h6>Credit/Debit Card</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-method" data-method="paypal">
                                <div class="text-center">
                                    <i class="fab fa-paypal fa-2x text-primary mb-2"></i>
                                    <h6>PayPal</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="payment-method" data-method="wallet">
                                <div class="text-center">
                                    <i class="fas fa-wallet fa-2x text-primary mb-2"></i>
                                    <h6>Wallet</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="payment_method" id="payment_method" value="card" required>
                </div>
                
                <!-- Card Details (shown only for card payment) -->
                <div id="cardDetails" class="mb-4">
                    <h5>Card Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">CVV</label>
                            <input type="text" class="form-control" placeholder="123" maxlength="4">
                        </div>
                    </div>
                </div>
                
                <!-- Price Preview -->
                <div class="ticket-preview">
                    <h5>Order Summary</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <p class="mb-1">Match:</p>
                            <h6 id="preview_match">Select a match</h6>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1">Seat Category:</p>
                            <h6 id="preview_category">Standard</h6>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1">Tickets:</p>
                            <h6 id="preview_quantity">1</h6>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1">Total Amount:</p>
                            <h4 id="preview_total">$0.00</h4>
                        </div>
                    </div>
                </div>
                
                <!-- Terms and Conditions -->
                <div class="form-check mb-4 mt-3">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-booking btn-lg" <?php echo empty($matches) ? 'disabled' : ''; ?>>
                        <i class="fas fa-lock"></i> Book Now & Pay
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>1. Tickets are non-refundable once purchased.</p>
                    <p>2. All sales are final.</p>
                    <p>3. Please arrive 60 minutes before the match.</p>
                    <p>4. Valid ID required for entry.</p>
                    <p>5. Prices are subject to change.</p>
                    <p>6. E-tickets will be sent to your registered email.</p>
                    <p>7. Children under 3 enter free (no ticket required).</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const matchSelect = document.getElementById('match_id');
            const seatOptions = document.querySelectorAll('.payment-method[data-category]');
            const seatCategoryInput = document.getElementById('seat_category');
            const quantitySelect = document.getElementById('quantity');
            const paymentOptions = document.querySelectorAll('.payment-method[data-method]');
            const paymentMethodInput = document.getElementById('payment_method');
            const cardDetails = document.getElementById('cardDetails');
            const availabilityNote = document.getElementById('availability_note');
            
            // Price elements
            const priceElements = {
                'standard': document.getElementById('standard_price'),
                'premium': document.getElementById('premium_price'),
                'vip': document.getElementById('vip_price'),
                'corporate': document.getElementById('corporate_price')
            };
            
            // Preview elements
            const previewMatch = document.getElementById('preview_match');
            const previewCategory = document.getElementById('preview_category');
            const previewQuantity = document.getElementById('preview_quantity');
            const previewTotal = document.getElementById('preview_total');
            
            let currentBasePrice = 0;
            let currentAvailable = 0;
            
            // Update prices based on selected match
            function updatePrices(basePrice) {
                currentBasePrice = basePrice;
                
                // Update seat category prices
                const prices = {
                    'standard': basePrice,
                    'premium': basePrice * 1.5,
                    'vip': basePrice * 2,
                    'corporate': basePrice * 3
                };
                
                for (const [category, element] of Object.entries(priceElements)) {
                    element.textContent = '$' + prices[category].toFixed(2) + ' per ticket';
                }
                
                // Update preview
                updatePreview();
            }
            
            // Update availability note
            function updateAvailability(available) {
                currentAvailable = available;
                const quantity = parseInt(quantitySelect.value);
                
                if (available === 0) {
                    availabilityNote.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Sold out</span>';
                    quantitySelect.disabled = true;
                } else if (available < 10) {
                    availabilityNote.innerHTML = `<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Only ${available} tickets left!</span>`;
                    quantitySelect.disabled = false;
                    
                    // Limit max quantity to available tickets
                    for (let i = 1; i <= 10; i++) {
                        const option = quantitySelect.querySelector(`option[value="${i}"]`);
                        if (option) {
                            option.disabled = i > available;
                        }
                    }
                } else {
                    availabilityNote.innerHTML = `<span class="text-success"><i class="fas fa-check-circle"></i> ${available} tickets available</span>`;
                    quantitySelect.disabled = false;
                    
                    // Enable all options
                    for (let i = 1; i <= 10; i++) {
                        const option = quantitySelect.querySelector(`option[value="${i}"]`);
                        if (option) {
                            option.disabled = false;
                        }
                    }
                }
            }
            
            // Update preview
            function updatePreview() {
                const selectedMatch = matchSelect.options[matchSelect.selectedIndex];
                const matchText = selectedMatch ? selectedMatch.text.split('(')[0] : 'Select a match';
                const category = seatCategoryInput.value;
                const quantity = parseInt(quantitySelect.value);
                
                // Calculate prices
                const multipliers = {
                    'standard': 1.0,
                    'premium': 1.5,
                    'vip': 2.0,
                    'corporate': 3.0
                };
                
                const multiplier = multipliers[category] || 1.0;
                const total = (currentBasePrice * multiplier * quantity).toFixed(2);
                
                // Update preview
                previewMatch.textContent = matchText.substring(0, 30) + (matchText.length > 30 ? '...' : '');
                previewCategory.textContent = category.charAt(0).toUpperCase() + category.slice(1);
                previewQuantity.textContent = quantity;
                previewTotal.textContent = '$' + total;
            }
            
            // Match change handler
            if (matchSelect) {
                matchSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value) {
                        const basePrice = parseFloat(selectedOption.dataset.basePrice);
                        const available = parseInt(selectedOption.dataset.available);
                        
                        updatePrices(basePrice);
                        updateAvailability(available);
                        updatePreview();
                    } else {
                        previewMatch.textContent = 'Select a match';
                        previewTotal.textContent = '$0.00';
                    }
                });
                
                // Auto-select match if coming from tickets page
                <?php if($selected_match_id > 0): ?>
                // Find and select the match from URL parameter
                for (let i = 0; i < matchSelect.options.length; i++) {
                    if (matchSelect.options[i].value == '<?php echo $selected_match_id; ?>') {
                        matchSelect.selectedIndex = i;
                        matchSelect.dispatchEvent(new Event('change'));
                        break;
                    }
                }
                <?php endif; ?>
            }
            
            // Seat category click handler
            seatOptions.forEach(option => {
                option.addEventListener('click', function() {
                    seatOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    seatCategoryInput.value = this.dataset.category;
                    updatePreview();
                });
            });
            
            // Payment method click handler
            paymentOptions.forEach(option => {
                option.addEventListener('click', function() {
                    paymentOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    paymentMethodInput.value = this.dataset.method;
                    
                    // Show/hide card details
                    if (this.dataset.method === 'card') {
                        cardDetails.style.display = 'block';
                    } else {
                        cardDetails.style.display = 'none';
                    }
                });
            });
            
            // Quantity change handler
            quantitySelect.addEventListener('change', function() {
                updatePreview();
                
                // Check if quantity exceeds available
                if (currentAvailable > 0 && parseInt(this.value) > currentAvailable) {
                    alert(`Only ${currentAvailable} tickets available. Please select a lower quantity.`);
                    this.value = Math.min(this.value, currentAvailable);
                    updatePreview();
                }
            });
            
            // Initialize
            if (matchSelect.value) {
                matchSelect.dispatchEvent(new Event('change'));
            }
            
            // Initialize selections
            if (seatOptions.length > 0) {
                seatOptions[0].click();
            }
            if (paymentOptions.length > 0) {
                paymentOptions[0].click();
            }
            
            // Format card number input
            const cardNumberInput = document.querySelector('input[placeholder="1234 5678 9012 3456"]');
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
                    e.target.value = value.trim();
                });
            }
            
            // Form validation
            document.getElementById('bookingForm').addEventListener('submit', function(e) {
                if (!document.getElementById('terms').checked) {
                    e.preventDefault();
                    alert('Please agree to the terms and conditions.');
                    return false;
                }
                
                const selectedMatch = matchSelect.value;
                if (!selectedMatch) {
                    e.preventDefault();
                    alert('Please select a match.');
                    return false;
                }
                
                const quantity = parseInt(quantitySelect.value);
                if (quantity > currentAvailable) {
                    e.preventDefault();
                    alert(`Only ${currentAvailable} tickets available. Please select a lower quantity.`);
                    return false;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                return true;
            });
        });
    </script>
</body>
</html>