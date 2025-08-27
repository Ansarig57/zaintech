// ===== ADMIN PANEL JAVASCRIPT =====

class AdminPanel {
    constructor() {
        this.currentSection = 'dashboard';
        this.currentUsersPage = 1;
        this.currentWithdrawalsFilter = 'pending';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupMobileNavigation();
        this.loadDashboardCharts();
        this.loadUsers();
        this.loadWithdrawals();
        this.loadSettings();
    }

    setupEventListeners() {
        // Navigation links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const section = link.dataset.section;
                if (section) {
                    this.switchSection(section);
                }
            });
        });

        // Settings forms
        document.getElementById('general-settings-form')?.addEventListener('submit', (e) => {
            this.saveGeneralSettings(e);
        });

        document.getElementById('rewards-settings-form')?.addEventListener('submit', (e) => {
            this.saveRewardsSettings(e);
        });

        // User search and filters
        document.getElementById('user-search')?.addEventListener('input', (e) => {
            this.searchUsers(e.target.value);
        });

        document.getElementById('user-filter')?.addEventListener('change', (e) => {
            this.filterUsers(e.target.value);
        });

        document.getElementById('user-sort')?.addEventListener('change', (e) => {
            this.sortUsers(e.target.value);
        });
    }

    setupMobileNavigation() {
        const mobileToggle = document.getElementById('mobile-toggle');
        const navMenu = document.querySelector('.nav-menu');

        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                mobileToggle.classList.toggle('active');
                navMenu.classList.toggle('active');
            });
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.admin-nav') && navMenu.classList.contains('active')) {
                mobileToggle.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });
    }

    switchSection(sectionName) {
        // Hide all sections
        document.querySelectorAll('.admin-section').forEach(section => {
            section.classList.remove('active');
        });

        // Show target section
        const targetSection = document.getElementById(`${sectionName}-section`);
        if (targetSection) {
            targetSection.classList.add('active');
        }

        // Update navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });

        const activeLink = document.querySelector(`.nav-link[data-section="${sectionName}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }

        this.currentSection = sectionName;

        // Close mobile menu
        const mobileToggle = document.getElementById('mobile-toggle');
        const navMenu = document.querySelector('.nav-menu');
        if (mobileToggle && navMenu) {
            mobileToggle.classList.remove('active');
            navMenu.classList.remove('active');
        }

        // Section-specific initialization
        this.initializeSection(sectionName);
    }

    initializeSection(sectionName) {
        switch (sectionName) {
            case 'users':
                this.loadUsers();
                break;
            case 'withdrawals':
                this.loadWithdrawals();
                break;
            case 'analytics':
                this.loadAnalytics();
                break;
        }
    }

    loadDashboardCharts() {
        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart');
        if (userGrowthCtx) {
            new Chart(userGrowthCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'New Users',
                        data: [120, 190, 300, 500, 820, 1200],
                        borderColor: '#00d4ff',
                        backgroundColor: 'rgba(0, 212, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#ffffff'
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                color: '#b8b8b8'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#b8b8b8'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    }
                }
            });
        }

        // Activity Chart
        const activityCtx = document.getElementById('activityChart');
        if (activityCtx) {
            new Chart(activityCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Spins', 'Watch Ads', 'Daily Bonus', 'Referrals'],
                    datasets: [{
                        data: [45, 30, 15, 10],
                        backgroundColor: [
                            '#00d4ff',
                            '#ff6b6b',
                            '#4ecdc4',
                            '#ffd93d'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#ffffff',
                                padding: 20
                            }
                        }
                    }
                }
            });
        }
    }

    async loadUsers() {
        try {
            const response = await fetch('../api/admin/users.php');
            const result = await response.json();

            if (result.success) {
                this.displayUsers(result.users);
                this.displayUsersPagination(result.pagination);
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    displayUsers(users) {
        const tbody = document.getElementById('users-table-body');
        if (!tbody) return;

        if (users.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        No users found
                    </td>
                </tr>
            `;
            return;
        }

        const usersHTML = users.map(user => `
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <img src="${user.avatar || '../assets/images/default-avatar.png'}" 
                             alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%;">
                        <div>
                            <div style="color: var(--text-primary); font-weight: 600;">${this.escapeHtml(user.name)}</div>
                            <div style="color: var(--text-muted); font-size: 0.8rem;">ID: ${user.id}</div>
                        </div>
                    </div>
                </td>
                <td>${this.escapeHtml(user.email)}</td>
                <td>
                    <span style="color: var(--primary-color); font-weight: 600;">
                        ${this.formatNumber(user.points)}
                    </span>
                </td>
                <td>₹${this.formatNumber(user.total_earned, 2)}</td>
                <td>
                    <span class="status-badge ${user.status}">${this.capitalize(user.status)}</span>
                </td>
                <td>${this.formatDate(user.created_at)}</td>
                <td>
                    <div class="table-actions">
                        <button class="btn-table edit" onclick="editUser(${user.id})" title="Edit User">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-table delete" onclick="deleteUser(${user.id})" title="Delete User">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        tbody.innerHTML = usersHTML;
    }

    async loadWithdrawals() {
        try {
            const response = await fetch(`../api/admin/withdrawals.php?status=${this.currentWithdrawalsFilter}`);
            const result = await response.json();

            if (result.success) {
                this.displayWithdrawals(result.withdrawals);
            }
        } catch (error) {
            console.error('Error loading withdrawals:', error);
        }
    }

    displayWithdrawals(withdrawals) {
        const tbody = document.getElementById('withdrawals-table-body');
        if (!tbody) return;

        if (withdrawals.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        No withdrawals found
                    </td>
                </tr>
            `;
            return;
        }

        const withdrawalsHTML = withdrawals.map(withdrawal => `
            <tr>
                <td>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600;">${this.escapeHtml(withdrawal.user_name)}</div>
                        <div style="color: var(--text-muted); font-size: 0.8rem;">${this.escapeHtml(withdrawal.user_email)}</div>
                    </div>
                </td>
                <td>
                    <div>
                        <div style="color: var(--text-primary); font-weight: 600;">₹${this.formatNumber(withdrawal.amount_pkr, 2)}</div>
                        <div style="color: var(--text-muted); font-size: 0.8rem;">${this.formatNumber(withdrawal.amount_points)} points</div>
                    </div>
                </td>
                <td>${this.capitalize(withdrawal.payment_method)}</td>
                <td>
                    <span class="status-badge ${withdrawal.status}">${this.capitalize(withdrawal.status)}</span>
                </td>
                <td>${this.formatDate(withdrawal.created_at)}</td>
                <td>
                    <div class="table-actions">
                        ${withdrawal.status === 'pending' ? `
                            <button class="btn-table approve" onclick="approveWithdrawal(${withdrawal.id})" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn-table reject" onclick="rejectWithdrawal(${withdrawal.id})" title="Reject">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : `
                            <button class="btn-table edit" onclick="viewWithdrawal(${withdrawal.id})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        `}
                    </div>
                </td>
            </tr>
        `).join('');

        tbody.innerHTML = withdrawalsHTML;
    }

    async loadSettings() {
        try {
            const response = await fetch('../api/admin/settings.php');
            const result = await response.json();

            if (result.success) {
                this.populateSettings(result.settings);
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }

    populateSettings(settings) {
        // Populate form fields with current settings
        Object.keys(settings).forEach(key => {
            const input = document.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = settings[key];
            }
        });
    }

    async saveGeneralSettings(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const settings = Object.fromEntries(formData.entries());

        try {
            this.showLoading(e.target);

            const response = await fetch('../api/admin/settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ type: 'general', settings })
            });

            const result = await response.json();

            if (result.success) {
                app.showSuccess('General settings saved successfully!');
            } else {
                app.showError(result.message || 'Failed to save settings');
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            app.showError('Failed to save settings. Please try again.');
        } finally {
            this.hideLoading(e.target);
        }
    }

    async saveRewardsSettings(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const settings = Object.fromEntries(formData.entries());

        try {
            this.showLoading(e.target);

            const response = await fetch('../api/admin/settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ type: 'rewards', settings })
            });

            const result = await response.json();

            if (result.success) {
                app.showSuccess('Reward settings saved successfully!');
            } else {
                app.showError(result.message || 'Failed to save settings');
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            app.showError('Failed to save settings. Please try again.');
        } finally {
            this.hideLoading(e.target);
        }
    }

    filterWithdrawals(status) {
        this.currentWithdrawalsFilter = status;
        
        // Update active tab
        document.querySelectorAll('.status-tabs .tab-button').forEach(btn => {
            btn.classList.remove('active');
        });
        
        event.target.classList.add('active');
        
        this.loadWithdrawals();
    }

    switchSettingsTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.settings-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show target tab
        const targetTab = document.getElementById(`${tabName}-tab`);
        if (targetTab) {
            targetTab.classList.add('active');
        }

        // Update tab buttons
        document.querySelectorAll('.settings-tabs .tab-button').forEach(btn => {
            btn.classList.remove('active');
        });

        event.target.classList.add('active');
    }

    // Utility functions
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatNumber(num, decimals = 0) {
        return new Intl.NumberFormat('en-IN', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(num);
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    showLoading(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        }
    }

    hideLoading(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            const icon = submitBtn.querySelector('i').className.replace('fa-spinner fa-spin', 'fa-save');
            submitBtn.innerHTML = `<i class="${icon}"></i> ${submitBtn.textContent.includes('General') ? 'Save General Settings' : 'Save Reward Settings'}`;
        }
    }
}

// Global functions
function switchAdminSection(sectionName) {
    adminPanel.switchSection(sectionName);
}

function switchSettingsTab(tabName) {
    adminPanel.switchSettingsTab(tabName);
}

function filterWithdrawals(status) {
    adminPanel.filterWithdrawals(status);
}

async function adminLogout() {
    try {
        const response = await fetch('../api/auth/logout.php', {
            method: 'POST'
        });
        const result = await response.json();

        if (result.success) {
            window.location.href = 'login.php';
        } else {
            app.showError('Logout failed. Please try again.');
        }
    } catch (error) {
        console.error('Admin logout error:', error);
        // Force logout anyway
        window.location.href = 'login.php';
    }
}

async function editUser(userId) {
    // Implementation for editing user
    app.showSuccess('Edit user functionality coming soon!');
}

async function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        // Implementation for deleting user
        app.showSuccess('Delete user functionality coming soon!');
    }
}

async function approveWithdrawal(withdrawalId) {
    if (confirm('Are you sure you want to approve this withdrawal?')) {
        // Implementation for approving withdrawal
        app.showSuccess('Approve withdrawal functionality coming soon!');
    }
}

async function rejectWithdrawal(withdrawalId) {
    const reason = prompt('Please enter a reason for rejection:');
    if (reason) {
        // Implementation for rejecting withdrawal
        app.showSuccess('Reject withdrawal functionality coming soon!');
    }
}

async function viewWithdrawal(withdrawalId) {
    // Implementation for viewing withdrawal details
    app.showSuccess('View withdrawal functionality coming soon!');
}

async function exportUsers() {
    // Implementation for exporting users
    app.showSuccess('Export users functionality coming soon!');
}

// Initialize admin panel
const adminPanel = new AdminPanel();

// Export for global access
window.adminPanel = adminPanel;
window.switchAdminSection = switchAdminSection;
window.switchSettingsTab = switchSettingsTab;
window.filterWithdrawals = filterWithdrawals;
window.adminLogout = adminLogout;