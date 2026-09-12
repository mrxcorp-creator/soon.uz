/**
 * WebHub.uz - Main JavaScript
 * Theme handling, animations, and utilities
 */

(function() {
    'use strict';

    // Theme Management
    const ThemeManager = {
        init: function() {
            this.applySavedTheme();
            this.setupThemeToggle();
        },

        applySavedTheme: function() {
            const savedTheme = localStorage.getItem('webhub-theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            } else {
                // Check system preference
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            }
        },

        setupThemeToggle: function() {
            const toggle = document.getElementById('theme-toggle');
            if (!toggle) return;

            toggle.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('webhub-theme', newTheme);
            });
        },

        getTheme: function() {
            return document.documentElement.getAttribute('data-theme') || 'light';
        }
    };

    // Scroll Animations
    const ScrollAnimations = {
        observer: null,

        init: function() {
            if ('IntersectionObserver' in window) {
                this.observer = new IntersectionObserver(this.handleIntersect.bind(this), {
                    root: null,
                    rootMargin: '0px 0px -100px 0px',
                    threshold: 0.1
                });

                document.querySelectorAll('.scroll-animate').forEach(el => {
                    this.observer.observe(el);
                });
            }
        },

        handleIntersect: function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    if (this.observer) {
                        this.observer.unobserve(entry.target);
                    }
                }
            });
        }
    };

    // Smooth scroll for anchor links
    const SmoothScroll = {
        init: function() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href !== '#') {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                });
            });
        }
    };

    // Form handling with CSRF
    const FormHandler = {
        init: function() {
            document.querySelectorAll('form[data-ajax]').forEach(form => {
                form.addEventListener('submit', this.handleSubmit.bind(this));
            });
        },

        handleSubmit: async function(e) {
            e.preventDefault();
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Yuborilmoqda...';
            }

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    form.reset();
                    if (result.message) {
                        this.showMessage(result.message, 'success');
                    }
                } else {
                    this.showMessage(result.error || 'Xatolik yuz berdi', 'error');
                }
            } catch (error) {
                this.showMessage('Ulanishda xatolik yuz berdi', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            }
        },

        showMessage: function(message, type) {
            // Remove existing messages
            const existingMsg = document.querySelector('.form-message');
            if (existingMsg) {
                existingMsg.remove();
            }

            const msgDiv = document.createElement('div');
            msgDiv.className = `form-message ${type}`;
            msgDiv.style.cssText = `
                padding: 12px 16px;
                border-radius: 8px;
                margin-bottom: 16px;
                background: ${type === 'success' ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)'};
                border: 1px solid ${type === 'success' ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)'};
                color: ${type === 'success' ? '#10b981' : '#ef4444'};
            `;
            msgDiv.textContent = message;

            const form = document.querySelector('form[data-ajax]');
            if (form) {
                form.insertBefore(msgDiv, form.firstChild);
                setTimeout(() => msgDiv.remove(), 5000);
            }
        }
    };

    // Mobile Navigation
    const MobileNav = {
        init: function() {
            const menuToggle = document.getElementById('menu-toggle');
            const navMenu = document.getElementById('nav-menu');

            if (menuToggle && navMenu) {
                menuToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                    this.setAttribute('aria-expanded', 
                        navMenu.classList.contains('active'));
                });

                // Close menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                        navMenu.classList.remove('active');
                    }
                });
            }
        }
    };

    // Counter Animation
    const CounterAnimation = {
        observer: null,

        init: function() {
            if ('IntersectionObserver' in window) {
                this.observer = new IntersectionObserver(this.handleIntersect.bind(this), {
                    threshold: 0.5
                });

                document.querySelectorAll('[data-counter]').forEach(el => {
                    this.observer.observe(el);
                });
            }
        },

        handleIntersect: function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    this.observer.unobserve(entry.target);
                }
            });
        },

        animateCounter: function(element) {
            const target = parseInt(element.dataset.counter, 10);
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    element.textContent = target + '+';
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current) + '+';
                }
            }, 16);
        }
    };

    // Initialize everything when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        ThemeManager.init();
        ScrollAnimations.init();
        SmoothScroll.init();
        FormHandler.init();
        MobileNav.init();
        CounterAnimation.init();
    });

    // Export for external use
    window.WebHub = {
        ThemeManager: ThemeManager,
        showMessage: FormHandler.showMessage
    };

})();
