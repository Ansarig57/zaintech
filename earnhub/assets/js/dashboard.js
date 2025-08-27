// ===== DASHBOARD JAVASCRIPT =====

class Dashboard {
    constructor() {
        this.currentSection = 'dashboard';
        this.activityUpdateInterval = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadRecentActivity();
        this.startActivityUpdates();
        this.setupMobileNavigation();
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

        // Window events
        window.addEventListener('resize', () => {
            this.handleResize();
        });

        // Update points display when points change
        document.addEventListener('pointsUpdated', (e) => {
            this.updatePointsDisplay(e.detail.points);
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
            if (!e.target.closest('.dashboard-nav') && navMenu.classList.contains('active')) {
                mobileToggle.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });
    }

    switchSection(sectionName) {
        // Hide all sections
        document.querySelectorAll('.dashboard-section').forEach(section => {
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
            case 'spin':
                if (window.spinWheel) {
                    window.spinWheel.init();
                }
                break;
            case 'watch':
                if (window.watchAds) {
                    window.watchAds.init();
                }
                break;
            case 'wallet':
                this.loadWalletData();
                break;
            case 'leaderboard':
                this.loadLeaderboard();
                break;
            case 'profile':
                this.loadProfile();
                break;
        }
    }

    async loadRecentActivity() {
        try {
            const response = await fetch('api/user/activity.php');
            const result = await response.json();

            if (result.success) {
                this.displayActivity(result.activities);
            }
        } catch (error) {
            console.error('Error loading activity:', error);
        }
    }

    displayActivity(activities) {
        const activityList = document.getElementById('activity-list');
        if (!activityList) return;

        if (activities.length === 0) {
            activityList.innerHTML = `
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="activity-info">
                        <div class="activity-title">No recent activity</div>
                        <div class="activity-description">Start earning points to see your activity here</div>
                    </div>
                </div>
            `;
            return;
        }

        const activityHTML = activities.map(activity => {
            const iconClass = this.getActivityIcon(activity.type);
            const timeAgo = this.timeAgo(activity.created_at);
            
            return `
                <div class="activity-item">
                    <div class="activity-icon ${activity.type}">
                        <i class="fas fa-${iconClass}"></i>
                    </div>
                    <div class="activity-info">
                        <div class="activity-title">${activity.description}</div>
                        <div class="activity-time">${timeAgo}</div>
                    </div>
                    <div class="activity-points">
                        ${activity.points > 0 ? '+' : ''}${activity.points}
                    </div>
                </div>
            `;
        }).join('');

        activityList.innerHTML = activityHTML;
    }

    getActivityIcon(type) {
        const icons = {
            'spin': 'dharmachakra',
            'watch_ad': 'video',
            'daily_bonus': 'gift',
            'referral': 'users',
            'withdrawal': 'money-bill-wave',
            'jackpot': 'trophy',
            'admin_adjust': 'cog'
        };
        return icons[type] || 'coins';
    }

    timeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) {
            return 'Just now';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes} minute${minutes !== 1 ? 's' : ''} ago`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours} hour${hours !== 1 ? 's' : ''} ago`;
        } else {
            const days = Math.floor(diffInSeconds / 86400);
            return `${days} day${days !== 1 ? 's' : ''} ago`;
        }
    }

    startActivityUpdates() {
        // Update activity every 30 seconds
        this.activityUpdateInterval = setInterval(() => {
            this.loadRecentActivity();
        }, 30000);
    }

    updatePointsDisplay(newPoints) {
        const pointsElement = document.getElementById('user-points');
        if (pointsElement) {
            // Animate the change
            pointsElement.style.transform = 'scale(1.2)';
            pointsElement.style.color = 'var(--success-color)';
            
            setTimeout(() => {
                pointsElement.textContent = newPoints.toLocaleString();
                pointsElement.style.transform = 'scale(1)';
                pointsElement.style.color = '';
            }, 200);
        }

        // Update user data
        window.userData.points = newPoints;
    }

    async claimDailyBonus() {
        try {
            const response = await fetch('api/user/daily-bonus.php', {
                method: 'POST'
            });
            const result = await response.json();

            if (result.success) {
                app.showSuccess(result.message);
                this.updatePointsDisplay(result.newPoints);
                
                // Hide the bonus button
                const bonusButton = document.querySelector('button[onclick="claimDailyBonus()"]');
                if (bonusButton) {
                    bonusButton.style.display = 'none';
                }

                // Reload activity
                this.loadRecentActivity();
            } else {
                app.showError(result.message);
            }
        } catch (error) {
            console.error('Error claiming daily bonus:', error);
            app.showError('Failed to claim daily bonus. Please try again.');
        }
    }

    async loadWalletData() {
        try {
            const response = await fetch('api/user/wallet.php');
            const result = await response.json();

            if (result.success) {
                this.displayWalletData(result.data);
            }
        } catch (error) {
            console.error('Error loading wallet data:', error);
        }
    }

    async loadLeaderboard() {
        try {
            const response = await fetch('api/user/leaderboard.php');
            const result = await response.json();

            if (result.success) {
                this.displayLeaderboard(result.data);
            }
        } catch (error) {
            console.error('Error loading leaderboard:', error);
        }
    }

    async loadProfile() {
        try {
            const response = await fetch('api/user/profile.php');
            const result = await response.json();

            if (result.success) {
                this.displayProfile(result.data);
            }
        } catch (error) {
            console.error('Error loading profile:', error);
        }
    }

    handleResize() {
        // Handle responsive changes
        if (window.innerWidth <= 768) {
            // Mobile adjustments
        } else {
            // Desktop adjustments
            const mobileToggle = document.getElementById('mobile-toggle');
            const navMenu = document.querySelector('.nav-menu');
            
            if (mobileToggle && navMenu) {
                mobileToggle.classList.remove('active');
                navMenu.classList.remove('active');
            }
        }
    }

    destroy() {
        if (this.activityUpdateInterval) {
            clearInterval(this.activityUpdateInterval);
        }
    }
}

// Global functions
async function claimDailyBonus() {
    await dashboard.claimDailyBonus();
}

function switchSection(sectionName) {
    dashboard.switchSection(sectionName);
}

async function logout() {
    try {
        const response = await fetch('api/auth/logout.php', {
            method: 'POST'
        });
        const result = await response.json();

        if (result.success) {
            window.location.href = 'index.html';
        } else {
            app.showError('Logout failed. Please try again.');
        }
    } catch (error) {
        console.error('Logout error:', error);
        // Force logout anyway
        window.location.href = 'index.html';
    }
}

// Initialize dashboard
const dashboard = new Dashboard();

// Export for global access
window.dashboard = dashboard;
window.switchSection = switchSection;
window.claimDailyBonus = claimDailyBonus;
window.logout = logout;