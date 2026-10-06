// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenu = document.querySelector('.mobile-menu');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenu) {
        mobileMenu.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });
    }

    // Load featured matches
    loadFeaturedMatches();
    
    // Initialize payment selection
    initPaymentSelection();
    
    // Initialize form validation
    initFormValidation();
});

// Featured Matches Data
const featuredMatches = [
    {
        id: 1,
        name: "Champions League Final",
        date: "2024-06-01",
        venue: "Wembley Stadium, London",
        category: "Premium",
        price: 299.99,
        image: "https://images.unsplash.com/photo-1575361204480-aadea25e6e68?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 2,
        name: "Premier League Derby",
        date: "2024-05-15",
        venue: "Old Trafford, Manchester",
        category: "Standard",
        price: 149.99,
        image: "https://images.unsplash.com/photo-1599058917212-d750089bc07e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    },
    {
        id: 3,
        name: "International Friendly",
        date: "2024-06-10",
        venue: "Camp Nou, Barcelona",
        category: "VIP",
        price: 399.99,
        image: "https://images.unsplash.com/photo-1522778119026-d647f0596c20?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
    }
];

function loadFeaturedMatches() {
    const matchesGrid = document.querySelector('.matches-grid');
    if (!matchesGrid) return;

    matchesGrid.innerHTML = '';
    
    featuredMatches.forEach(match => {
        const matchCard = document.createElement('div');
        matchCard.className = 'match-card';
        matchCard.innerHTML = `
            <img src="${match.image}" alt="${match.name}">
            <div class="match-info">
                <h3>${match.name}</h3>
                <p class="match-date">${match.date} | ${match.venue}</p>
                <p>Category: ${match.category}</p>
                <p class="match-price">$${match.price.toFixed(2)}</p>
                <button class="btn-primary book-ticket" data-id="${match.id}">Book Now</button>
            </div>
        `;
        matchesGrid.appendChild(matchCard);
    });

    // Add event listeners to book buttons
    document.querySelectorAll('.book-ticket').forEach(button => {
        button.addEventListener('click', function() {
            const matchId = this.getAttribute('data-id');
            const match = featuredMatches.find(m => m.id == matchId);
            if (match) {
                localStorage.setItem('selectedMatch', JSON.stringify(match));
                window.location.href = 'booking-ticket.php';
            }
        });
    });
}

function initPaymentSelection() {
    const paymentOptions = document.querySelectorAll('.payment-option');
    if (!paymentOptions.length) return;

    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            paymentOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            const paymentMethod = this.getAttribute('data-method');
            document.querySelector('input[name="payment_method"]').value = paymentMethod;
        });
    });
}

function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validation="true"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            const inputs = this.querySelectorAll('input[required], select[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('error');
                } else {
                    input.classList.remove('error');
                }
                
                // Email validation
                if (input.type === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value)) {
                        isValid = false;
                        input.classList.add('error');
                    }
                }
                
                // Password validation
                if (input.name === 'password' && input.value.length < 6) {
                    isValid = false;
                    input.classList.add('error');
                    alert('Password must be at least 6 characters long');
                }
                
                // Confirm password validation
                if (input.name === 'confirm_password') {
                    const password = this.querySelector('input[name="password"]').value;
                    if (input.value !== password) {
                        isValid = false;
                        input.classList.add('error');
                        alert('Passwords do not match');
                    }
                }
            });
            
            if (isValid) {
                this.submit();
            }
        });
    });
}

// Dynamic price calculation for booking forms
function calculateTotal() {
    const quantity = document.getElementById('quantity') ? 
        parseInt(document.getElementById('quantity').value) || 1 : 1;
    const price = document.getElementById('price') ? 
        parseFloat(document.getElementById('price').value) || 0 : 0;
    const nights = document.getElementById('nights') ? 
        parseInt(document.getElementById('nights').value) || 1 : 1;
    const roomPrice = document.getElementById('room_price') ? 
        parseFloat(document.getElementById('room_price').value) || 0 : 0;
    
    let total = 0;
    
    if (price > 0) {
        total = quantity * price;
    } else if (roomPrice > 0) {
        total = nights * roomPrice;
    }
    
    const totalElement = document.getElementById('total_amount');
    if (totalElement) {
        totalElement.textContent = '$' + total.toFixed(2);
    }
    
    // Update hidden total input if it exists
    const totalInput = document.querySelector('input[name="total_amount"]');
    if (totalInput) {
        totalInput.value = total.toFixed(2);
    }
    
    return total;
}

// Initialize date pickers with min date = today
function initDatePickers() {
    const today = new Date().toISOString().split('T')[0];
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        input.min = today;
        
        // For check-out date, set min date based on check-in
        if (input.name === 'check_out') {
            const checkInInput = document.querySelector('input[name="check_in"]');
            if (checkInInput) {
                checkInInput.addEventListener('change', function() {
                    input.min = this.value;
                });
            }
        }
    });
}

// Call init functions when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initDatePickers();
    
    // Add change listeners for quantity inputs
    const quantityInputs = document.querySelectorAll('input[name="quantity"], input[name="nights"]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', calculateTotal);
        input.addEventListener('input', calculateTotal);
    });
    
    // Initialize price calculation on page load
    calculateTotal();
});