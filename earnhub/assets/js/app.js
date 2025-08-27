// ===== MAIN APPLICATION JAVASCRIPT =====

class EarnhubApp {
    constructor() {
        this.currentUser = null;
        this.currentTheme = 'dark-theme';
        this.isLoggedIn = false;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeTheme();
        this.startLoadingSequence();
        this.initializeCounters();
        this.setupNavigation();
        this.initializeThemeSelector();
    }

    setupEventListeners() {
        // Window events
        window.addEventListener('load', () => {
            this.hideLoadingScreen();
        });

        window.addEventListener('scroll', () => {
            this.handleScroll();
        });

        // Form submissions
        document.getElementById('loginForm')?.addEventListener('submit', (e) => {
            this.handleLogin(e);
        });

        document.getElementById('registerForm')?.addEventListener('submit', (e) => {
            this.handleRegister(e);
        });

        document.getElementById('forgotPasswordForm')?.addEventListener('submit', (e) => {
            this.handleForgotPassword(e);
        });

        // Modal events
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal(e.target.id);
            }
        });

        // Navigation toggle
        document.getElementById('nav-toggle')?.addEventListener('click', () => {
            this.toggleMobileNav();
        });
    }

    startLoadingSequence() {
        // Simulate loading time
        setTimeout(() => {
            this.hideLoadingScreen();
        }, 2000);
    }

    hideLoadingScreen() {
        const loadingScreen = document.getElementById('loading-screen');
        if (loadingScreen) {
            loadingScreen.classList.add('hidden');
            setTimeout(() => {
                loadingScreen.style.display = 'none';
            }, 500);
        }
    }

    handleScroll() {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 100) {
            navbar?.classList.add('scrolled');
        } else {
            navbar?.classList.remove('scrolled');
        }

        // Update active nav link
        this.updateActiveNavLink();
    }

    updateActiveNavLink() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');
        
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.scrollY >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    }

    setupNavigation() {
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);
                this.scrollToSection(targetId);
                this.closeMobileNav();
            });
        });
    }

    toggleMobileNav() {
        const navMenu = document.getElementById('nav-menu');
        const navToggle = document.getElementById('nav-toggle');
        
        navMenu?.classList.toggle('active');
        navToggle?.classList.toggle('active');
    }

    closeMobileNav() {
        const navMenu = document.getElementById('nav-menu');
        const navToggle = document.getElementById('nav-toggle');
        
        navMenu?.classList.remove('active');
        navToggle?.classList.remove('active');
    }

    scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            const offsetTop = section.offsetTop - 80;
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
    }

    initializeCounters() {
        // Animate counters when they come into view
        const counters = document.querySelectorAll('.stat-number');
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        counters.forEach(counter => observer.observe(counter));

        // Start live ticker updates
        this.startLiveTicker();
    }

    animateCounter(element) {
        const target = parseInt(element.textContent.replace(/[^\d]/g, ''));
        const prefix = element.textContent.replace(/[\d,]/g, '');
        let current = 0;
        const increment = target / 100;
        const duration = 2000;
        const stepTime = duration / 100;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = prefix + current.toLocaleString('en-IN');
        }, stepTime);
    }

    startLiveTicker() {
        const names = ['John', 'Sarah', 'Mike', 'Lisa', 'Alex', 'Emma', 'David', 'Anna', 'Chris', 'Maya'];
        const actions = ['earned', 'won', 'claimed', 'received'];
        const ticker = document.querySelector('.ticker-content span');
        
        if (!ticker) return;

        setInterval(() => {
            const name = names[Math.floor(Math.random() * names.length)];
            const action = actions[Math.floor(Math.random() * actions.length)];
            const amount = Math.floor(Math.random() * 400) + 50;
            
            const currentContent = ticker.textContent;
            const newEntry = `🎉 ${name} ${action} ₹${amount} • `;
            ticker.textContent = newEntry + currentContent;
        }, 5000);
    }

    // Modal Management
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Focus first input
            const firstInput = modal.querySelector('input');
            setTimeout(() => firstInput?.focus(), 300);
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    switchModal(currentModalId, newModalId) {
        this.closeModal(currentModalId);
        setTimeout(() => {
            this.openModal(newModalId);
        }, 300);
    }

    // Authentication
    async handleLogin(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        const loginData = {
            email: formData.get('email'),
            password: formData.get('password'),
            remember: formData.get('remember') === 'on'
        };

        try {
            this.showLoading(form);
            
            const response = await fetch('api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(loginData)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('Login successful! Redirecting...');
                this.currentUser = result.user;
                this.isLoggedIn = true;
                
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1500);
            } else {
                this.showError(result.message || 'Login failed. Please try again.');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showError('Connection error. Please try again.');
        } finally {
            this.hideLoading(form);
        }
    }

    async handleRegister(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        const registerData = {
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            password: formData.get('password'),
            referral_code: formData.get('referral_code')
        };

        // Client-side validation
        if (!this.validateRegisterData(registerData)) {
            return;
        }

        try {
            this.showLoading(form);
            
            const response = await fetch('api/auth/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(registerData)
            });

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('Registration successful! Please check your email for verification.');
                form.reset();
                setTimeout(() => {
                    this.switchModal('registerModal', 'loginModal');
                }, 2000);
            } else {
                this.showError(result.message || 'Registration failed. Please try again.');
            }
        } catch (error) {
            console.error('Registration error:', error);
            this.showError('Connection error. Please try again.');
        } finally {
            this.hideLoading(form);
        }
    }

    async handleForgotPassword(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        const email = formData.get('email');

        try {
            this.showLoading(form);
            
            const response = await fetch('api/auth/forgot-password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email })
            });

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('Reset link sent to your email!');
                form.reset();
                setTimeout(() => {
                    this.switchModal('forgotPasswordModal', 'loginModal');
                }, 2000);
            } else {
                this.showError(result.message || 'Failed to send reset link.');
            }
        } catch (error) {
            console.error('Forgot password error:', error);
            this.showError('Connection error. Please try again.');
        } finally {
            this.hideLoading(form);
        }
    }

    validateRegisterData(data) {
        if (data.name.length < 2) {
            this.showError('Name must be at least 2 characters long.');
            return false;
        }

        if (!this.isValidEmail(data.email)) {
            this.showError('Please enter a valid email address.');
            return false;
        }

        if (!this.isValidPhone(data.phone)) {
            this.showError('Please enter a valid phone number.');
            return false;
        }

        if (data.password.length < 6) {
            this.showError('Password must be at least 6 characters long.');
            return false;
        }

        return true;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPhone(phone) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
    }

    // UI Feedback
    showLoading(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        }
    }

    hideLoading(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            // Restore original button text based on form
            if (form.id === 'loginForm') {
                submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login';
            } else if (form.id === 'registerForm') {
                submitBtn.innerHTML = '<i class="fas fa-user-plus"></i> Create Account';
            } else if (form.id === 'forgotPasswordForm') {
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Reset Link';
            }
        }
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);

        // Add notification styles if not present
        if (!document.querySelector('#notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                .notification {
                    position: fixed;
                    top: 100px;
                    right: 20px;
                    z-index: 10001;
                    max-width: 400px;
                    animation: slideInRight 0.3s ease;
                }
                
                .notification-content {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    padding: 1rem 1.5rem;
                    border-radius: var(--radius-md);
                    color: white;
                    font-weight: 500;
                    box-shadow: var(--shadow-lg);
                }
                
                .notification-success .notification-content {
                    background: var(--success-color);
                }
                
                .notification-error .notification-content {
                    background: var(--danger-color);
                }
                
                .notification-info .notification-content {
                    background: var(--primary-color);
                }
                
                .notification-close {
                    background: none;
                    border: none;
                    color: white;
                    cursor: pointer;
                    font-size: 1.2rem;
                    margin-left: auto;
                }
                
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Theme Management
    initializeTheme() {
        const savedTheme = localStorage.getItem('earnhub-theme') || 'dark-theme';
        this.setTheme(savedTheme);
    }

    setTheme(themeName) {
        const body = document.getElementById('app-body');
        if (body) {
            // Remove all theme classes
            const themeClasses = [
                'dark-theme', 'neon-theme', 'golden-theme', 'blue-theme',
                'red-theme', 'green-theme', 'purple-theme', 'gradient-theme',
                'pastel-theme', 'classic-theme'
            ];
            
            body.classList.remove(...themeClasses);
            body.classList.add('theme-transition');
            body.classList.add(themeName);
            
            this.currentTheme = themeName;
            localStorage.setItem('earnhub-theme', themeName);
            
            // Update theme selector active state
            this.updateThemeSelector();
            
            // Remove transition class after animation
            setTimeout(() => {
                body.classList.remove('theme-transition');
            }, 500);
        }
    }

    initializeThemeSelector() {
        // Create theme selector if it doesn't exist
        if (!document.querySelector('.theme-selector')) {
            this.createThemeSelector();
        }
    }

    createThemeSelector() {
        const themeSelector = document.createElement('div');
        themeSelector.className = 'theme-selector';
        themeSelector.innerHTML = `
            <button class="theme-selector-toggle" onclick="app.toggleThemeSelector()">
                <i class="fas fa-palette"></i>
            </button>
            <h3>Choose Theme</h3>
            <div class="theme-grid">
                ${this.getThemeOptions()}
            </div>
        `;
        
        document.body.appendChild(themeSelector);
        this.updateThemeSelector();
    }

    getThemeOptions() {
        const themes = [
            { name: 'dark-theme', label: 'Dark' },
            { name: 'neon-theme', label: 'Neon' },
            { name: 'golden-theme', label: 'Golden' },
            { name: 'blue-theme', label: 'Blue' },
            { name: 'red-theme', label: 'Red' },
            { name: 'green-theme', label: 'Green' },
            { name: 'purple-theme', label: 'Purple' },
            { name: 'gradient-theme', label: 'Gradient' },
            { name: 'pastel-theme', label: 'Pastel' },
            { name: 'classic-theme', label: 'Classic' }
        ];

        return themes.map(theme => `
            <div class="theme-option" data-theme="${theme.name}" onclick="app.setTheme('${theme.name}')">
                <div class="theme-preview"></div>
                ${theme.label}
            </div>
        `).join('');
    }

    toggleThemeSelector() {
        const themeSelector = document.querySelector('.theme-selector');
        if (themeSelector) {
            themeSelector.classList.toggle('open');
        }
    }

    updateThemeSelector() {
        const themeOptions = document.querySelectorAll('.theme-option');
        themeOptions.forEach(option => {
            option.classList.remove('active');
            if (option.dataset.theme === this.currentTheme) {
                option.classList.add('active');
            }
        });
    }
}

// Utility Functions
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggle = input.nextElementSibling;
    
    if (input.type === 'password') {
        input.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }
}

function openModal(modalId) {
    app.openModal(modalId);
}

function closeModal(modalId) {
    app.closeModal(modalId);
}

function switchModal(currentModalId, newModalId) {
    app.switchModal(currentModalId, newModalId);
}

function scrollToSection(sectionId) {
    app.scrollToSection(sectionId);
}

// Initialize App
const app = new EarnhubApp();

// Export for global access
window.app = app;