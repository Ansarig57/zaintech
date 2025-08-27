// ===== SPIN WHEEL JAVASCRIPT =====

class SpinWheel {
    constructor() {
        this.canvas = null;
        this.ctx = null;
        this.isSpinning = false;
        this.currentRotation = 0;
        this.spinSound = null;
        this.winSound = null;
        this.prizes = [];
        this.colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', 
            '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F',
            '#BB8FCE', '#85C1E9', '#F8C471', '#82E0AA'
        ];
        this.init();
    }

    init() {
        this.canvas = document.getElementById('wheel-canvas');
        if (!this.canvas) return;

        this.ctx = this.canvas.getContext('2d');
        this.setupPrizes();
        this.setupSounds();
        this.drawWheel();
        this.updateSpinButton();
    }

    setupPrizes() {
        // Generate random prizes based on min/max settings
        const minReward = 10; // From PHP: getAdminSetting('spin_min_reward', 10)
        const maxReward = 500; // From PHP: getAdminSetting('spin_max_reward', 500)
        
        this.prizes = [];
        for (let i = 0; i < 8; i++) {
            const reward = Math.floor(Math.random() * (maxReward - minReward + 1)) + minReward;
            this.prizes.push({
                text: `${reward}`,
                points: reward,
                color: this.colors[i]
            });
        }
    }

    setupSounds() {
        try {
            // Create audio context for better browser support
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            
            // Create spin sound (synthesized)
            this.createSpinSound();
            this.createWinSound();
        } catch (error) {
            console.log('Audio not supported:', error);
        }
    }

    createSpinSound() {
        // Create a simple spinning sound effect
        this.spinSound = {
            play: () => {
                if (!this.audioContext) return;
                
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);
                
                oscillator.frequency.setValueAtTime(200, this.audioContext.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(100, this.audioContext.currentTime + 2);
                
                gainNode.gain.setValueAtTime(0.1, this.audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 2);
                
                oscillator.start(this.audioContext.currentTime);
                oscillator.stop(this.audioContext.currentTime + 2);
            }
        };
    }

    createWinSound() {
        // Create a winning sound effect
        this.winSound = {
            play: () => {
                if (!this.audioContext) return;
                
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);
                
                oscillator.frequency.setValueAtTime(523.25, this.audioContext.currentTime); // C5
                oscillator.frequency.setValueAtTime(659.25, this.audioContext.currentTime + 0.1); // E5
                oscillator.frequency.setValueAtTime(783.99, this.audioContext.currentTime + 0.2); // G5
                
                gainNode.gain.setValueAtTime(0.2, this.audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.5);
                
                oscillator.start(this.audioContext.currentTime);
                oscillator.stop(this.audioContext.currentTime + 0.5);
            }
        };
    }

    drawWheel() {
        if (!this.ctx) return;

        const centerX = this.canvas.width / 2;
        const centerY = this.canvas.height / 2;
        const radius = 180;
        const sliceAngle = (2 * Math.PI) / this.prizes.length;

        // Clear canvas
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // Draw wheel sections
        this.prizes.forEach((prize, index) => {
            const startAngle = index * sliceAngle;
            const endAngle = startAngle + sliceAngle;

            // Draw slice
            this.ctx.beginPath();
            this.ctx.moveTo(centerX, centerY);
            this.ctx.arc(centerX, centerY, radius, startAngle, endAngle);
            this.ctx.closePath();
            
            // Create gradient for each slice
            const gradient = this.ctx.createRadialGradient(centerX, centerY, 0, centerX, centerY, radius);
            gradient.addColorStop(0, this.lightenColor(prize.color, 20));
            gradient.addColorStop(1, prize.color);
            
            this.ctx.fillStyle = gradient;
            this.ctx.fill();
            
            // Add border
            this.ctx.strokeStyle = '#ffffff';
            this.ctx.lineWidth = 2;
            this.ctx.stroke();

            // Draw text
            const textAngle = startAngle + sliceAngle / 2;
            const textX = centerX + Math.cos(textAngle) * (radius * 0.7);
            const textY = centerY + Math.sin(textAngle) * (radius * 0.7);

            this.ctx.save();
            this.ctx.translate(textX, textY);
            this.ctx.rotate(textAngle + Math.PI / 2);
            
            this.ctx.fillStyle = '#ffffff';
            this.ctx.font = 'bold 18px Poppins, Arial, sans-serif';
            this.ctx.textAlign = 'center';
            this.ctx.textBaseline = 'middle';
            
            // Add text shadow for better visibility
            this.ctx.shadowColor = 'rgba(0, 0, 0, 0.5)';
            this.ctx.shadowBlur = 3;
            this.ctx.shadowOffsetX = 1;
            this.ctx.shadowOffsetY = 1;
            
            this.ctx.fillText(prize.text, 0, -5);
            this.ctx.fillText('Points', 0, 15);
            
            this.ctx.restore();
        });

        // Draw center circle
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 30, 0, 2 * Math.PI);
        this.ctx.fillStyle = '#1a1a2e';
        this.ctx.fill();
        this.ctx.strokeStyle = '#00d4ff';
        this.ctx.lineWidth = 3;
        this.ctx.stroke();

        // Add shine effect
        const shineGradient = this.ctx.createRadialGradient(centerX - 50, centerY - 50, 0, centerX, centerY, radius);
        shineGradient.addColorStop(0, 'rgba(255, 255, 255, 0.3)');
        shineGradient.addColorStop(0.5, 'rgba(255, 255, 255, 0.1)');
        shineGradient.addColorStop(1, 'rgba(255, 255, 255, 0)');
        
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        this.ctx.fillStyle = shineGradient;
        this.ctx.fill();
    }

    lightenColor(color, percent) {
        const num = parseInt(color.replace("#", ""), 16);
        const amt = Math.round(2.55 * percent);
        const R = (num >> 16) + amt;
        const G = (num >> 8 & 0x00FF) + amt;
        const B = (num & 0x0000FF) + amt;
        return "#" + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
            (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
            (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1);
    }

    updateSpinButton() {
        const spinButton = document.getElementById('spin-button');
        if (!spinButton) return;

        const todaySpins = window.userData?.todaySpins || 0;
        const spinLimit = window.userData?.spinLimit || 5;

        if (todaySpins >= spinLimit) {
            spinButton.disabled = true;
            spinButton.innerHTML = `
                <i class="fas fa-clock"></i>
                <span>Daily Limit Reached</span>
            `;
        } else {
            spinButton.disabled = false;
            spinButton.innerHTML = `
                <i class="fas fa-play"></i>
                <span>SPIN</span>
            `;
        }
    }

    async spin() {
        if (this.isSpinning) return;
        
        const todaySpins = window.userData?.todaySpins || 0;
        const spinLimit = window.userData?.spinLimit || 5;

        if (todaySpins >= spinLimit) {
            app.showError('Daily spin limit reached. Come back tomorrow!');
            return;
        }

        try {
            // Request spin from server
            const response = await fetch('api/user/spin.php', {
                method: 'POST'
            });
            const result = await response.json();

            if (!result.success) {
                app.showError(result.message);
                return;
            }

            this.isSpinning = true;
            const spinButton = document.getElementById('spin-button');
            if (spinButton) {
                spinButton.disabled = true;
                spinButton.innerHTML = `
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Spinning...</span>
                `;
            }

            // Play spin sound
            if (this.spinSound) {
                this.spinSound.play();
            }

            // Calculate winning position
            const winningPrize = result.reward;
            const winningIndex = this.prizes.findIndex(prize => prize.points === winningPrize);
            const sliceAngle = (2 * Math.PI) / this.prizes.length;
            const winningAngle = winningIndex * sliceAngle + sliceAngle / 2;

            // Calculate final rotation (multiple full rotations + winning position)
            const spins = 5 + Math.random() * 5; // 5-10 full rotations
            const finalRotation = this.currentRotation + (spins * 2 * Math.PI) + (2 * Math.PI - winningAngle);

            // Animate the wheel
            this.animateWheel(finalRotation, () => {
                this.onSpinComplete(result);
            });

        } catch (error) {
            console.error('Spin error:', error);
            app.showError('Spin failed. Please try again.');
            this.isSpinning = false;
            this.updateSpinButton();
        }
    }

    animateWheel(finalRotation, callback) {
        const startRotation = this.currentRotation;
        const rotationDiff = finalRotation - startRotation;
        const duration = 4000; // 4 seconds
        const startTime = Date.now();

        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function for natural spin feel
            const easeOut = 1 - Math.pow(1 - progress, 3);
            
            this.currentRotation = startRotation + (rotationDiff * easeOut);

            // Apply rotation to canvas
            this.ctx.save();
            this.ctx.translate(this.canvas.width / 2, this.canvas.height / 2);
            this.ctx.rotate(this.currentRotation);
            this.ctx.translate(-this.canvas.width / 2, -this.canvas.height / 2);
            
            this.drawWheel();
            this.ctx.restore();

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                callback();
            }
        };

        animate();
    }

    onSpinComplete(result) {
        this.isSpinning = false;

        // Play win sound
        if (this.winSound) {
            setTimeout(() => {
                this.winSound.play();
            }, 200);
        }

        // Show win animation
        this.showWinAnimation(result.reward);

        // Update user data
        window.userData.points = result.newPoints;
        window.userData.todaySpins = result.todaySpins;

        // Update UI
        if (window.dashboard) {
            window.dashboard.updatePointsDisplay(result.newPoints);
        }

        // Update spin button
        setTimeout(() => {
            this.updateSpinButton();
        }, 2000);

        // Show success message
        app.showSuccess(`🎉 Congratulations! You won ${result.reward} points!`);
    }

    showWinAnimation(reward) {
        // Create floating points animation
        const wheelContainer = document.querySelector('.spin-wheel-wrapper');
        if (!wheelContainer) return;

        for (let i = 0; i < 10; i++) {
            const pointElement = document.createElement('div');
            pointElement.textContent = `+${reward}`;
            pointElement.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                color: var(--success-color);
                font-weight: bold;
                font-size: 1.5rem;
                pointer-events: none;
                z-index: 1000;
                animation: floatUp 2s ease-out forwards;
                transform: translate(-50%, -50%);
            `;

            // Random offset for each element
            const offsetX = (Math.random() - 0.5) * 100;
            const offsetY = (Math.random() - 0.5) * 100;
            pointElement.style.setProperty('--offset-x', offsetX + 'px');
            pointElement.style.setProperty('--offset-y', offsetY + 'px');

            wheelContainer.appendChild(pointElement);

            // Remove element after animation
            setTimeout(() => {
                pointElement.remove();
            }, 2000);
        }

        // Add CSS animation if not present
        if (!document.querySelector('#spin-animations')) {
            const style = document.createElement('style');
            style.id = 'spin-animations';
            style.textContent = `
                @keyframes floatUp {
                    0% {
                        opacity: 1;
                        transform: translate(-50%, -50%) scale(1);
                    }
                    100% {
                        opacity: 0;
                        transform: translate(calc(-50% + var(--offset-x, 0px)), calc(-50% + var(--offset-y, -100px))) scale(1.5);
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
}

// Global function for spinning
async function spinWheel() {
    if (window.spinWheelInstance) {
        await window.spinWheelInstance.spin();
    }
}

// Initialize spin wheel when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('wheel-canvas')) {
        window.spinWheelInstance = new SpinWheel();
        window.spinWheel = window.spinWheelInstance;
    }
});

// Export for global access
window.SpinWheel = SpinWheel;
window.spinWheel = spinWheel;