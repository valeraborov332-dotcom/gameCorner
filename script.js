const errorBlock = document.getElementById("error");

// Show validation errors
function showError(message) {
    if (errorBlock) {
        errorBlock.textContent = message;
        errorBlock.style.display = "block";
        errorBlock.classList.remove("visible");
        void errorBlock.offsetWidth; // Trigger reflow
        errorBlock.classList.add("visible");
    } else {
        console.error("Block id='error' not found in HTML!");
        alert(message);
    }
}

// Clear errors
function clearError() {
    if (errorBlock) {
        errorBlock.textContent = "";
        errorBlock.style.display = "none";
    }
}

// === REGISTER FORM VALIDATION ===
const registerForm = document.getElementById("register-form");

if (registerForm) {
    const regUser = document.getElementById("username-reg");
    const regEmail = document.getElementById("email");
    const regPass1 = document.getElementById("password-register");
    const regPass2 = document.getElementById("confirm-password");

    registerForm.addEventListener("submit", function(event) {
        let errors = [];
        let username = regUser.value.trim();
        let email = regEmail.value.trim();
        let pass1 = regPass1.value;
        let pass2 = regPass2.value;

        if (!username) errors.push("Username is required");
        if (!email) errors.push("Email is required");
        if (!pass1) errors.push("Password is required");

        if (email && (!email.includes('@') || !email.includes('.'))) {
            errors.push("Invalid email format");
        }

        if (pass1 !== pass2) {
            errors.push("Passwords do not match");
        }

        if (errors.length > 0) {
            event.preventDefault();
            showError(errors.join(" | "));
        }
    });

    regUser.addEventListener("input", clearError);
    regEmail.addEventListener("input", clearError);
    regPass1.addEventListener("input", clearError);
    regPass2.addEventListener("input", clearError);
}

// === LOGIN FORM VALIDATION ===
const loginForm = document.getElementById("login-form");

if (loginForm) {
    const loginIdent = document.getElementById("login-identifier");
    const loginPass = document.getElementById("password-login");

    loginForm.addEventListener("submit", function(event) {
        let errors = [];
        let ident = loginIdent.value.trim();
        let pass = loginPass.value;

        if (!ident) errors.push("Username or Email is required");
        if (!pass) errors.push("Password is required");

        if (errors.length > 0) {
            event.preventDefault();
            showError(errors.join(" | "));
        }
    });

    loginIdent.addEventListener("input", clearError);
    loginPass.addEventListener("input", clearError);
}

/* ==========================================
   LIVE SEARCH
   ========================================== */
document.addEventListener('DOMContentLoaded', function() {
    
    const searchInput = document.getElementById('search-input');
    const resultsContainer = document.getElementById('search-results');

    if (!searchInput || !resultsContainer) return;

    // Move dropdown to body to avoid z-index/overflow issues
    document.body.appendChild(resultsContainer);

    // Position the dropdown exactly below the input
    function positionDropdown() {
        const rect = searchInput.getBoundingClientRect();
        
        resultsContainer.style.position = 'absolute';
        resultsContainer.style.left = (rect.left + window.scrollX) + 'px';
        resultsContainer.style.top = (rect.bottom + window.scrollY + 5) + 'px';
        resultsContainer.style.width = rect.width + 'px';
    }

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        if (query.length < 2) {
            resultsContainer.style.display = 'none';
            return;
        }
        positionDropdown();

        fetch(`search-ajax.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(games => {
                resultsContainer.innerHTML = '';

                if (games.length > 0) {
                    resultsContainer.style.display = 'block';
                    positionDropdown();
                    
                    games.forEach(game => {
                        const link = document.createElement('a');
                        link.href = `game.php?id=${game.id}`;
                        link.classList.add('search-item');
                        
                        // User rating display logic
                        let userRatingHtml = '';
                        if (game.user_rating) {
                            let score = parseFloat(game.user_rating).toFixed(1);
                            userRatingHtml = `<span class="separator">|</span> <span class="user-rate-search" title="User Score">👤 ${score}</span>`;
                        }

                        link.innerHTML = `
                            <img src="${game.image_url}" alt="${game.title}">
                            <div class="search-info">
                                <span class="title">${game.title}</span>
                                <div class="search-ratings">
                                    <span class="critic-rate-search" title="Critic Score">⭐ ${game.rating}</span>
                                    ${userRatingHtml}
                                </div>
                            </div>
                        `;
                        resultsContainer.appendChild(link);
                    });
                } else {
                    resultsContainer.style.display = 'none';
                }
            })
            .catch(err => console.error('Error:', err));
    });

    // Adjust position on resize
    window.addEventListener('resize', () => {
        if (resultsContainer.style.display === 'block') positionDropdown();
    });

    // Hide when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target !== searchInput && !resultsContainer.contains(e.target)) {
            resultsContainer.style.display = 'none';
        }
    });
});

/* * Script for Visual Feedback when deleting images
 * Adds/Removes 'marked-for-delete' class to the parent container
 */
document.addEventListener('DOMContentLoaded', function() {
    // Select all delete checkboxes
    const checkboxes = document.querySelectorAll('input[name="delete_images[]"]');
    
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            // Find parent container (.edit-image-item)
            const parentBlock = this.closest('.edit-image-item');
            
            // Toggle class based on checkbox state
            if (this.checked) {
                parentBlock.classList.add('marked-for-delete');
            } else {
                parentBlock.classList.remove('marked-for-delete');
            }
        });
    });
});