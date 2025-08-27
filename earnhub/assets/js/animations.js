// ===== ANIMATIONS & EFFECTS JAVASCRIPT =====

class AnimationSystem {
    constructor() {
        this.particles = [];
        this.canvas = null;
        this.ctx = null;
        this.animationId = null;
        this.init();
    }

    init() {
        this.createParticleCanvas();
        this.setupIntersectionObserver();
        this.setupScrollAnimations();
        this.setupHoverEffects();
        this.startParticleSystem();
    }

    createParticleCanvas() {
        // Create floating particles canvas
        const canvas = document.createElement('canvas');
        canvas.id = 'particles-canvas';
        canvas.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            opacity: 0.6;
        `;
        
        document.body.appendChild(canvas);
        
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        
        this.resizeCanvas();
        window.addEventListener('resize', () => this.resizeCanvas());
    }

    resizeCanvas() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
        
        // Reinitialize particles
        this.initParticles();
    }

    initParticles() {
        this.particles = [];
        const particleCount = Math.min(50, Math.floor(window.innerWidth / 30));
        
        for (let i = 0; i < particleCount; i++) {
            this.particles.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                size: Math.random() * 3 + 1,
                speedX: (Math.random() - 0.5) * 0.5,
                speedY: (Math.random() - 0.5) * 0.5,
                opacity: Math.random() * 0.5 + 0.2,
                color: this.getThemeColor()
            });
        }
    }

    getThemeColor() {
        const colors = ['#00d4ff', '#4ecdc4', '#ff6b6b', '#ffd93d'];
        return colors[Math.floor(Math.random() * colors.length)];
    }

    startParticleSystem() {
        const animate = () => {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            this.particles.forEach(particle => {
                // Update position
                particle.x += particle.speedX;
                particle.y += particle.speedY;
                
                // Wrap around edges
                if (particle.x > this.canvas.width) particle.x = 0;
                if (particle.x < 0) particle.x = this.canvas.width;
                if (particle.y > this.canvas.height) particle.y = 0;
                if (particle.y < 0) particle.y = this.canvas.height;
                
                // Draw particle
                this.ctx.beginPath();
                this.ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
                this.ctx.fillStyle = particle.color;
                this.ctx.globalAlpha = particle.opacity;
                this.ctx.fill();
            });
            
            this.ctx.globalAlpha = 1;
            this.animationId = requestAnimationFrame(animate);
        };
        
        animate();
    }

    setupIntersectionObserver() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                } else {
                    entry.target.classList.remove('animate-in');
                }
            });
        }, observerOptions);

        // Observe elements that should animate on scroll
        document.querySelectorAll('.stat-card, .feature-card, .action-card').forEach(el => {
            observer.observe(el);
        });
    }

    setupScrollAnimations() {
        // Add CSS for scroll animations
        if (!document.querySelector('#scroll-animations')) {
            const style = document.createElement('style');
            style.id = 'scroll-animations';
            style.textContent = `
                .stat-card,
                .feature-card,
                .action-card {
                    opacity: 0;
                    transform: translateY(50px);
                    transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                }

                .stat-card.animate-in,
                .feature-card.animate-in,
                .action-card.animate-in {
                    opacity: 1;
                    transform: translateY(0);
                }

                .stat-card:nth-child(1) { transition-delay: 0.1s; }
                .stat-card:nth-child(2) { transition-delay: 0.2s; }
                .stat-card:nth-child(3) { transition-delay: 0.3s; }
                .stat-card:nth-child(4) { transition-delay: 0.4s; }

                .feature-card:nth-child(1) { transition-delay: 0.1s; }
                .feature-card:nth-child(2) { transition-delay: 0.2s; }
                .feature-card:nth-child(3) { transition-delay: 0.3s; }
                .feature-card:nth-child(4) { transition-delay: 0.4s; }
                .feature-card:nth-child(5) { transition-delay: 0.5s; }
                .feature-card:nth-child(6) { transition-delay: 0.6s; }

                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }

                @keyframes fadeInScale {
                    from {
                        opacity: 0;
                        transform: scale(0.8);
                    }
                    to {
                        opacity: 1;
                        transform: scale(1);
                    }
                }

                @keyframes slideInRight {
                    from {
                        opacity: 0;
                        transform: translateX(50px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }

                @keyframes slideInLeft {
                    from {
                        opacity: 0;
                        transform: translateX(-50px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }

                @keyframes bounce {
                    0%, 20%, 53%, 80%, 100% {
                        transform: translate3d(0,0,0);
                    }
                    40%, 43% {
                        transform: translate3d(0, -30px, 0);
                    }
                    70% {
                        transform: translate3d(0, -15px, 0);
                    }
                    90% {
                        transform: translate3d(0, -4px, 0);
                    }
                }

                @keyframes pulse {
                    0% {
                        transform: scale(1);
                    }
                    50% {
                        transform: scale(1.05);
                    }
                    100% {
                        transform: scale(1);
                    }
                }

                @keyframes glow {
                    0%, 100% {
                        box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
                    }
                    50% {
                        box-shadow: 0 0 30px rgba(0, 212, 255, 0.6);
                    }
                }

                .animate-fadeInUp {
                    animation: fadeInUp 0.8s ease-out;
                }

                .animate-fadeInScale {
                    animation: fadeInScale 0.6s ease-out;
                }

                .animate-slideInRight {
                    animation: slideInRight 0.8s ease-out;
                }

                .animate-slideInLeft {
                    animation: slideInLeft 0.8s ease-out;
                }

                .animate-bounce {
                    animation: bounce 1s ease-in-out;
                }

                .animate-pulse {
                    animation: pulse 2s ease-in-out infinite;
                }

                .animate-glow {
                    animation: glow 2s ease-in-out infinite;
                }
            `;
            document.head.appendChild(style);
        }
    }

    setupHoverEffects() {
        // Enhanced button hover effects
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', (e) => {
                this.createRippleEffect(e.target, e);
            });
        });

        // Card tilt effect
        document.querySelectorAll('.stat-card, .feature-card, .action-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                this.handleCardTilt(e);
            });

            card.addEventListener('mouseleave', (e) => {
                e.target.style.transform = 'rotateX(0deg) rotateY(0deg) scale(1)';
            });
        });
    }

    createRippleEffect(button, event) {
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        `;

        // Add ripple animation if not present
        if (!document.querySelector('#ripple-animation')) {
            const style = document.createElement('style');
            style.id = 'ripple-animation';
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        button.style.position = 'relative';
        button.style.overflow = 'hidden';
        button.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    handleCardTilt(event) {
        const card = event.currentTarget;
        const rect = card.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = (y - centerY) / centerY * -10;
        const rotateY = (x - centerX) / centerX * 10;
        
        card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
        card.style.transition = 'transform 0.1s ease-out';
    }

    // Point celebration effect
    createPointCelebration(element, points) {
        const celebration = document.createElement('div');
        celebration.textContent = `+${points}`;
        celebration.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--success-color);
            font-size: 2rem;
            font-weight: bold;
            pointer-events: none;
            z-index: 1000;
            animation: pointCelebration 2s ease-out forwards;
        `;

        if (!document.querySelector('#point-celebration-animation')) {
            const style = document.createElement('style');
            style.id = 'point-celebration-animation';
            style.textContent = `
                @keyframes pointCelebration {
                    0% {
                        opacity: 1;
                        transform: translate(-50%, -50%) scale(0.5);
                    }
                    50% {
                        opacity: 1;
                        transform: translate(-50%, -100px) scale(1.2);
                    }
                    100% {
                        opacity: 0;
                        transform: translate(-50%, -150px) scale(1);
                    }
                }
            `;
            document.head.appendChild(style);
        }

        element.style.position = 'relative';
        element.appendChild(celebration);

        setTimeout(() => {
            celebration.remove();
        }, 2000);
    }

    // Success confetti effect
    createConfetti(element) {
        const colors = ['#00d4ff', '#4ecdc4', '#ff6b6b', '#ffd93d', '#a8e6cf'];
        const confettiCount = 50;

        for (let i = 0; i < confettiCount; i++) {
            const confetti = document.createElement('div');
            confetti.style.cssText = `
                position: fixed;
                width: 10px;
                height: 10px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                top: ${element.getBoundingClientRect().top + window.scrollY}px;
                left: ${element.getBoundingClientRect().left + Math.random() * element.offsetWidth}px;
                pointer-events: none;
                z-index: 1000;
                animation: confetti 3s ease-out forwards;
                animation-delay: ${Math.random() * 0.5}s;
            `;

            document.body.appendChild(confetti);

            setTimeout(() => {
                confetti.remove();
            }, 3500);
        }

        if (!document.querySelector('#confetti-animation')) {
            const style = document.createElement('style');
            style.id = 'confetti-animation';
            style.textContent = `
                @keyframes confetti {
                    0% {
                        transform: rotateZ(0deg) translateY(0px) rotateX(0deg);
                        opacity: 1;
                    }
                    100% {
                        transform: rotateZ(720deg) translateY(400px) rotateX(180deg);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Loading shimmer effect
    addShimmerEffect(element) {
        element.classList.add('shimmer');
        
        if (!document.querySelector('#shimmer-animation')) {
            const style = document.createElement('style');
            style.id = 'shimmer-animation';
            style.textContent = `
                .shimmer {
                    background: linear-gradient(90deg, 
                        var(--bg-card) 25%, 
                        rgba(255, 255, 255, 0.1) 50%, 
                        var(--bg-card) 75%);
                    background-size: 200% 100%;
                    animation: shimmer 1.5s infinite;
                }

                @keyframes shimmer {
                    0% {
                        background-position: -200% 0;
                    }
                    100% {
                        background-position: 200% 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    removeShimmerEffect(element) {
        element.classList.remove('shimmer');
    }

    // Typing effect
    typeText(element, text, speed = 50) {
        element.textContent = '';
        let i = 0;
        
        const type = () => {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
                setTimeout(type, speed);
            }
        };
        
        type();
    }

    // Number counting animation
    animateNumber(element, start, end, duration = 2000) {
        const range = end - start;
        const startTime = Date.now();
        
        const update = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(start + (range * easeOut));
            
            element.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        };
        
        update();
    }

    destroy() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
        
        if (this.canvas) {
            this.canvas.remove();
        }
    }
}

// Global animation utilities
window.AnimationUtils = {
    celebratePoints: (element, points) => {
        if (window.animationSystem) {
            window.animationSystem.createPointCelebration(element, points);
        }
    },
    
    showConfetti: (element) => {
        if (window.animationSystem) {
            window.animationSystem.createConfetti(element);
        }
    },
    
    addShimmer: (element) => {
        if (window.animationSystem) {
            window.animationSystem.addShimmerEffect(element);
        }
    },
    
    removeShimmer: (element) => {
        if (window.animationSystem) {
            window.animationSystem.removeShimmerEffect(element);
        }
    },
    
    typeText: (element, text, speed) => {
        if (window.animationSystem) {
            window.animationSystem.typeText(element, text, speed);
        }
    },
    
    animateNumber: (element, start, end, duration) => {
        if (window.animationSystem) {
            window.animationSystem.animateNumber(element, start, end, duration);
        }
    }
};

// Initialize animation system when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.animationSystem = new AnimationSystem();
});

// Clean up on page unload
window.addEventListener('beforeunload', () => {
    if (window.animationSystem) {
        window.animationSystem.destroy();
    }
});