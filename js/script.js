// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get all accordion headers
    const accordionHeaders = document.querySelectorAll('.accordion-header');

    // Add click event listener to each header
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            // Get the parent accordion item
            const accordionItem = this.parentElement;
            
            // Check if this item is already active
            const isActive = accordionItem.classList.contains('active');

            // Close all other accordion items (only one open at a time)
            accordionHeaders.forEach(otherHeader => {
                otherHeader.parentElement.classList.remove('active');
            });

            // Toggle the active class on the clicked item
            if (!isActive) {
                accordionItem.classList.add('active');
            }
        });
    });

    // Optional: Add keyboard navigation
    accordionHeaders.forEach((header, index) => {
        header.addEventListener('keydown', function(e) {
            // Enter or Space key
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
            
            // Arrow key navigation
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const nextHeader = accordionHeaders[index + 1];
                if (nextHeader) nextHeader.focus();
            }
            
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prevHeader = accordionHeaders[index - 1];
                if (prevHeader) prevHeader.focus();
            }

            // Home key - go to first item
            if (e.key === 'Home') {
                e.preventDefault();
                accordionHeaders[0].focus();
            }

            // End key - go to last item
            if (e.key === 'End') {
                e.preventDefault();
                accordionHeaders[accordionHeaders.length - 1].focus();
            }
        });
    });

    // Smooth scroll behavior for any internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Registration Form Handling
    const registrationForm = document.getElementById('registrationForm');
    
    if (registrationForm) {
        // Phone number formatting
        const phoneInput = document.getElementById('cellPhone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length > 0) {
                    if (value.length <= 3) {
                        value = `(${value}`;
                    } else if (value.length <= 6) {
                        value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
                    } else {
                        value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
                    }
                }
                
                e.target.value = value;
            });
        }

        // Real-time validation for all form fields
        const formFields = {
            firstName: {
                element: document.getElementById('firstName'),
                error: document.getElementById('firstNameError'),
                validate: (value) => {
                    if (!value || value.trim().length === 0) {
                        return 'First name is required';
                    }
                    if (value.trim().length < 2) {
                        return 'First name must be at least 2 characters';
                    }
                    if (!/^[a-zA-Z\s\-']+$/.test(value)) {
                        return 'First name can only contain letters, spaces, hyphens, and apostrophes';
                    }
                    return '';
                }
            },
            lastName: {
                element: document.getElementById('lastName'),
                error: document.getElementById('lastNameError'),
                validate: (value) => {
                    if (!value || value.trim().length === 0) {
                        return 'Last name is required';
                    }
                    if (value.trim().length < 2) {
                        return 'Last name must be at least 2 characters';
                    }
                    if (!/^[a-zA-Z\s\-']+$/.test(value)) {
                        return 'Last name can only contain letters, spaces, hyphens, and apostrophes';
                    }
                    return '';
                }
            },
            email: {
                element: document.getElementById('email'),
                error: document.getElementById('emailError'),
                validate: (value) => {
                    if (!value || value.trim().length === 0) {
                        return 'Email address is required';
                    }
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        return 'Please enter a valid email address';
                    }
                    return '';
                }
            },
            cellPhone: {
                element: document.getElementById('cellPhone'),
                error: document.getElementById('cellPhoneError'),
                validate: (value) => {
                    if (!value || value.trim().length === 0) {
                        return 'Cell phone number is required';
                    }
                    const digitsOnly = value.replace(/\D/g, '');
                    if (digitsOnly.length !== 10) {
                        return 'Please enter a valid 10-digit phone number';
                    }
                    return '';
                }
            },
            banquetAttendees: {
                element: document.getElementById('banquetAttendees'),
                error: document.getElementById('banquetError'),
                validate: (value) => {
                    const num = parseInt(value);
                    if (isNaN(num) || num < 1) {
                        return 'Please enter at least 1 attendee';
                    }
                    if (num > 20) {
                        return 'Maximum 20 attendees per registration';
                    }
                    return '';
                }
            }
        };

        // Add blur event listeners for real-time validation
        Object.keys(formFields).forEach(fieldName => {
            const field = formFields[fieldName];
            if (field.element) {
                field.element.addEventListener('blur', function() {
                    const errorMsg = field.validate(this.value);
                    if (field.error) {
                        field.error.textContent = errorMsg;
                    }
                });

                // Clear error on input
                field.element.addEventListener('input', function() {
                    if (field.error && field.error.textContent) {
                        const errorMsg = field.validate(this.value);
                        if (!errorMsg) {
                            field.error.textContent = '';
                        }
                    }
                });
            }
        });

        // Calculate total cost dynamically
        const attendeesInput = document.getElementById('banquetAttendees');
        const totalCostDisplay = document.getElementById('totalCost');
        const pricePerPerson = 55.00;

        if (attendeesInput && totalCostDisplay) {
            attendeesInput.addEventListener('input', function() {
                const attendees = parseInt(this.value) || 1;
                const total = (attendees * pricePerPerson).toFixed(2);
                totalCostDisplay.textContent = total;
            });
        }

        // Form submission
        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate all fields
            let isValid = true;
            Object.keys(formFields).forEach(fieldName => {
                const field = formFields[fieldName];
                if (field.element) {
                    const errorMsg = field.validate(field.element.value);
                    if (field.error) {
                        field.error.textContent = errorMsg;
                    }
                    if (errorMsg) {
                        isValid = false;
                    }
                }
            });

            // Validate reCAPTCHA
            const recaptchaResponse = grecaptcha.getResponse();
            const recaptchaError = document.getElementById('recaptchaError');
            
            if (!recaptchaResponse || recaptchaResponse.length === 0) {
                if (recaptchaError) {
                    recaptchaError.textContent = 'Please complete the reCAPTCHA verification';
                }
                isValid = false;
            } else {
                if (recaptchaError) {
                    recaptchaError.textContent = '';
                }
            }

            // If validation passes, collect form data
            if (isValid) {
                const formData = {
                    firstName: document.getElementById('firstName').value.trim(),
                    lastName: document.getElementById('lastName').value.trim(),
                    email: document.getElementById('email').value.trim(),
                    cellPhone: document.getElementById('cellPhone').value.trim(),
                    banquetAttendees: parseInt(document.getElementById('banquetAttendees').value),
                    recaptchaToken: recaptchaResponse,
                    submittedAt: new Date().toISOString()
                };

                console.log('Form Data:', formData);

                // Show success message
                registrationForm.style.display = 'none';
                const successMessage = document.getElementById('successMessage');
                if (successMessage) {
                    successMessage.classList.remove('hidden');
                }

                // Here you would typically send the data to your server
                // Example:
                // fetch('/api/register', {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json',
                //     },
                //     body: JSON.stringify(formData)
                // })
                // .then(response => response.json())
                // .then(data => {
                //     console.log('Success:', data);
                //     // Show success message
                // })
                // .catch((error) => {
                //     console.error('Error:', error);
                //     // Show error message
                // });
            } else {
                // Scroll to first error
                const firstError = document.querySelector('.error-message:not(:empty)');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        // Reset form handler
        registrationForm.addEventListener('reset', function() {
            // Clear all error messages
            Object.keys(formFields).forEach(fieldName => {
                const field = formFields[fieldName];
                if (field.error) {
                    field.error.textContent = '';
                }
            });
            
            // Reset reCAPTCHA
            if (typeof grecaptcha !== 'undefined') {
                grecaptcha.reset();
            }
            
            const recaptchaError = document.getElementById('recaptchaError');
            if (recaptchaError) {
                recaptchaError.textContent = '';
            }
        });
    }
});
