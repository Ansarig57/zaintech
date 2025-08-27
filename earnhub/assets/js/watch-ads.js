// ===== WATCH ADS JAVASCRIPT =====

class WatchAds {
    constructor() {
        this.isWatching = false;
        this.timer = null;
        this.timeRemaining = 0;
        this.adDuration = 30; // Default duration
        this.currentAdType = 'general';
        this.init();
    }

    init() {
        this.adDuration = window.userData?.adDuration || 30;
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Event listeners will be set up when elements are available
    }

    async startWatching() {
        if (this.isWatching) return;

        const todayWatched = window.userData?.todayWatched || 0;
        const watchLimit = window.userData?.watchLimit || 20;

        if (todayWatched >= watchLimit) {
            app.showError('Daily ad watching limit reached. Come back tomorrow!');
            return;
        }

        try {
            // Check if user can watch ad
            const response = await fetch('api/user/watch-check.php');
            const result = await response.json();

            if (!result.success) {
                app.showError(result.message);
                return;
            }

            this.isWatching = true;
            this.timeRemaining = this.adDuration;
            this.showAdPlayer();
            this.startTimer();

        } catch (error) {
            console.error('Watch ad error:', error);
            app.showError('Failed to start ad. Please try again.');
        }
    }

    showAdPlayer() {
        const adPlaceholder = document.getElementById('ad-placeholder');
        const adTimer = document.getElementById('ad-timer');

        if (adPlaceholder) {
            adPlaceholder.style.display = 'none';
        }

        if (adTimer) {
            adTimer.style.display = 'block';
            this.updateTimerDisplay();
        }

        // Show different ad content based on type
        this.displayAdContent();
    }

    displayAdContent() {
        const adTimer = document.getElementById('ad-timer');
        if (!adTimer) return;

        // Add ad content based on type
        const adTypes = {
            facebook: {
                title: 'Facebook Promotion',
                content: 'Discover amazing products and services from Facebook advertisers',
                icon: 'fab fa-facebook'
            },
            tiktok: {
                title: 'TikTok Trending',
                content: 'Watch trending TikTok content and discover new creators',
                icon: 'fab fa-tiktok'
            },
            youtube: {
                title: 'YouTube Video',
                content: 'Enjoy engaging YouTube content from popular creators',
                icon: 'fab fa-youtube'
            },
            general: {
                title: 'Sponsored Content',
                content: 'Watch this sponsored content to earn points',
                icon: 'fas fa-bullhorn'
            }
        };

        const adType = adTypes[this.currentAdType] || adTypes.general;

        // Add ad content above timer
        const existingContent = adTimer.querySelector('.ad-content-display');
        if (!existingContent) {
            const adContentDisplay = document.createElement('div');
            adContentDisplay.className = 'ad-content-display';
            adContentDisplay.innerHTML = `
                <div class="ad-header">
                    <i class="${adType.icon}"></i>
                    <h3>${adType.title}</h3>
                </div>
                <p>${adType.content}</p>
                <div class="ad-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill"></div>
                    </div>
                    <small>Please wait while the ad loads...</small>
                </div>
            `;
            adTimer.insertBefore(adContentDisplay, adTimer.firstChild);
        }

        // Add styles for ad content
        this.addAdContentStyles();
    }

    addAdContentStyles() {
        if (document.querySelector('#ad-content-styles')) return;

        const style = document.createElement('style');
        style.id = 'ad-content-styles';
        style.textContent = `
            .ad-content-display {
                text-align: center;
                margin-bottom: 2rem;
                padding: 1.5rem;
                background: var(--bg-card);
                border-radius: var(--radius-md);
                border: 1px solid var(--border-color);
            }

            .ad-header {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.75rem;
                margin-bottom: 1rem;
            }

            .ad-header i {
                font-size: 1.5rem;
                color: var(--primary-color);
            }

            .ad-header h3 {
                color: var(--text-primary);
                margin: 0;
            }

            .ad-content-display p {
                color: var(--text-secondary);
                margin-bottom: 1.5rem;
            }

            .ad-progress {
                margin-bottom: 1rem;
            }

            .progress-bar {
                width: 100%;
                height: 8px;
                background: var(--bg-primary);
                border-radius: 4px;
                overflow: hidden;
                margin-bottom: 0.5rem;
            }

            .progress-fill {
                height: 100%;
                background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
                width: 0%;
                transition: width 0.3s ease;
                border-radius: 4px;
            }

            .ad-progress small {
                color: var(--text-muted);
                font-size: 0.8rem;
            }
        `;
        document.head.appendChild(style);
    }

    startTimer() {
        this.updateTimerDisplay();

        this.timer = setInterval(() => {
            this.timeRemaining--;
            this.updateTimerDisplay();
            this.updateProgress();

            if (this.timeRemaining <= 0) {
                this.completeAd();
            }
        }, 1000);
    }

    updateTimerDisplay() {
        const timerSeconds = document.getElementById('timer-seconds');
        if (timerSeconds) {
            timerSeconds.textContent = this.timeRemaining;
        }

        // Update timer circle progress
        const timerCircle = document.querySelector('.timer-circle');
        if (timerCircle) {
            const progress = ((this.adDuration - this.timeRemaining) / this.adDuration) * 100;
            timerCircle.style.setProperty('--progress', progress + '%');
        }
    }

    updateProgress() {
        const progressFill = document.getElementById('progress-fill');
        if (progressFill) {
            const progress = ((this.adDuration - this.timeRemaining) / this.adDuration) * 100;
            progressFill.style.width = progress + '%';
        }

        // Update progress text
        const progressText = document.querySelector('.ad-progress small');
        if (progressText) {
            if (this.timeRemaining > 0) {
                progressText.textContent = `Ad playing... ${this.timeRemaining}s remaining`;
            } else {
                progressText.textContent = 'Ad completed! Click claim to get your reward.';
            }
        }
    }

    completeAd() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }

        // Show claim button
        const claimButton = document.getElementById('claim-button');
        if (claimButton) {
            claimButton.style.display = 'inline-flex';
            
            // Add glow animation to claim button
            claimButton.style.animation = 'pulse 1s infinite';
        }

        // Hide timer display
        const timerCircle = document.querySelector('.timer-circle');
        if (timerCircle) {
            timerCircle.style.display = 'none';
        }

        // Update progress text
        const progressText = document.querySelector('.ad-progress small');
        if (progressText) {
            progressText.textContent = 'Ad completed! Click the button below to claim your reward.';
            progressText.style.color = 'var(--success-color)';
            progressText.style.fontWeight = 'bold';
        }

        // Play completion sound
        this.playCompletionSound();
    }

    async claimReward() {
        if (!this.isWatching) return;

        try {
            const response = await fetch('api/user/watch-claim.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ad_type: this.currentAdType,
                    duration: this.adDuration
                })
            });

            const result = await response.json();

            if (result.success) {
                // Update user data
                window.userData.points = result.newPoints;
                window.userData.todayWatched = result.todayWatched;

                // Update UI
                if (window.dashboard) {
                    window.dashboard.updatePointsDisplay(result.newPoints);
                }

                // Show success message
                app.showSuccess(`🎉 Great! You earned ${result.reward} points!`);

                // Reset ad player
                this.resetAdPlayer();

                // Show celebration animation
                this.showCelebration(result.reward);

            } else {
                app.showError(result.message);
            }

        } catch (error) {
            console.error('Claim reward error:', error);
            app.showError('Failed to claim reward. Please try again.');
        }
    }

    resetAdPlayer() {
        this.isWatching = false;
        this.timeRemaining = 0;

        const adPlaceholder = document.getElementById('ad-placeholder');
        const adTimer = document.getElementById('ad-timer');
        const claimButton = document.getElementById('claim-button');

        if (adPlaceholder) {
            adPlaceholder.style.display = 'block';
        }

        if (adTimer) {
            adTimer.style.display = 'none';
            
            // Remove ad content display
            const adContentDisplay = adTimer.querySelector('.ad-content-display');
            if (adContentDisplay) {
                adContentDisplay.remove();
            }
        }

        if (claimButton) {
            claimButton.style.display = 'none';
            claimButton.style.animation = '';
        }

        // Reset timer circle
        const timerCircle = document.querySelector('.timer-circle');
        if (timerCircle) {
            timerCircle.style.display = 'flex';
            timerCircle.style.setProperty('--progress', '0%');
        }

        // Update timer seconds display
        const timerSeconds = document.getElementById('timer-seconds');
        if (timerSeconds) {
            timerSeconds.textContent = this.adDuration;
        }

        // Check if user can watch more ads
        this.updateWatchButton();
    }

    updateWatchButton() {
        const todayWatched = window.userData?.todayWatched || 0;
        const watchLimit = window.userData?.watchLimit || 20;

        const startButton = document.querySelector('button[onclick="startWatchingAd()"]');
        if (!startButton) return;

        if (todayWatched >= watchLimit) {
            startButton.disabled = true;
            startButton.innerHTML = `
                <i class="fas fa-clock"></i> Daily Limit Reached
            `;
        } else {
            startButton.disabled = false;
            startButton.innerHTML = `
                <i class="fas fa-play"></i> Start Watching
            `;
        }
    }

    showCelebration(reward) {
        const watchPlayer = document.querySelector('.watch-player');
        if (!watchPlayer) return;

        // Create celebration elements
        for (let i = 0; i < 8; i++) {
            const celebration = document.createElement('div');
            celebration.textContent = `+${reward}`;
            celebration.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                color: var(--success-color);
                font-weight: bold;
                font-size: 1.2rem;
                pointer-events: none;
                z-index: 1000;
                animation: celebrationFloat 2s ease-out forwards;
                transform: translate(-50%, -50%);
            `;

            // Random positioning
            const offsetX = (Math.random() - 0.5) * 200;
            const offsetY = (Math.random() - 0.5) * 200;
            celebration.style.setProperty('--offset-x', offsetX + 'px');
            celebration.style.setProperty('--offset-y', offsetY + 'px');

            watchPlayer.appendChild(celebration);

            setTimeout(() => {
                celebration.remove();
            }, 2000);
        }

        // Add animation styles
        if (!document.querySelector('#celebration-styles')) {
            const style = document.createElement('style');
            style.id = 'celebration-styles';
            style.textContent = `
                @keyframes celebrationFloat {
                    0% {
                        opacity: 1;
                        transform: translate(-50%, -50%) scale(1) rotate(0deg);
                    }
                    100% {
                        opacity: 0;
                        transform: translate(calc(-50% + var(--offset-x, 0px)), calc(-50% + var(--offset-y, -150px))) scale(1.5) rotate(360deg);
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    playCompletionSound() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            // Success sound sequence
            oscillator.frequency.setValueAtTime(523.25, audioContext.currentTime); // C5
            oscillator.frequency.setValueAtTime(659.25, audioContext.currentTime + 0.1); // E5
            oscillator.frequency.setValueAtTime(783.99, audioContext.currentTime + 0.2); // G5

            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.4);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.4);
        } catch (error) {
            console.log('Audio not supported:', error);
        }
    }

    // Random ad type selection
    selectRandomAdType() {
        const adTypes = ['facebook', 'tiktok', 'youtube', 'general'];
        this.currentAdType = adTypes[Math.floor(Math.random() * adTypes.length)];
    }

    destroy() {
        if (this.timer) {
            clearInterval(this.timer);
        }
    }
}

// Global functions
async function startWatchingAd() {
    if (window.watchAdsInstance) {
        window.watchAdsInstance.selectRandomAdType();
        await window.watchAdsInstance.startWatching();
    }
}

async function claimAdReward() {
    if (window.watchAdsInstance) {
        await window.watchAdsInstance.claimReward();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('ad-placeholder')) {
        window.watchAdsInstance = new WatchAds();
        window.watchAds = window.watchAdsInstance;
    }
});

// Export for global access
window.WatchAds = WatchAds;
window.startWatchingAd = startWatchingAd;
window.claimAdReward = claimAdReward;