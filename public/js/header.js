document.addEventListener('DOMContentLoaded', function() {
    const navbarCollapse = document.getElementById('mainHeaderNav');
    const particlesContainer = document.getElementById('particlesContainer');
    const navLinks = document.querySelectorAll('.header-link');

    navLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            const href = link.getAttribute('href');

            if (!href || href === '#' || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            navLinks.forEach(item => item.classList.remove('loading'));
            link.classList.add('loading');
        });
    });

    if (navbarCollapse && window.bootstrap) {
        const collapseInstance = bootstrap.Collapse.getOrCreateInstance(navbarCollapse, { toggle: false });
        const collapseLinks = navbarCollapse.querySelectorAll('.nav-link');

        collapseLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992 && navbarCollapse.classList.contains('show')) {
                    collapseInstance.hide();
                }
            });
        });
    }

    if (particlesContainer) {
        initHeaderParticles();
    }

    function initHeaderParticles() {
        const particles = [];
        const particleLines = [];
        const particleCount = 20;
        const connectionDistance = 120;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');

            const size = Math.random() * 2 + 2;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.top = `${Math.random() * 100}%`;
            particle.style.opacity = Math.random() * 0.4 + 0.1;

            particlesContainer.appendChild(particle);

            particles.push({
                element: particle,
                x: Math.random() * 100,
                y: Math.random() * 100,
                vx: (Math.random() - 0.5) * 0.15,
                vy: (Math.random() - 0.5) * 0.15
            });
        }

        function animateParticles() {
            particles.forEach(particle => {
                particle.x += particle.vx;
                particle.y += particle.vy;

                if (particle.x <= 0 || particle.x >= 100) particle.vx *= -1;
                if (particle.y <= 0 || particle.y >= 100) particle.vy *= -1;

                particle.element.style.left = `${particle.x}%`;
                particle.element.style.top = `${particle.y}%`;
            });

            particleLines.forEach(line => {
                if (line.parentNode) {
                    line.parentNode.removeChild(line);
                }
            });
            particleLines.length = 0;

            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const p1 = particles[i];
                    const p2 = particles[j];

                    const dx = (p2.x - p1.x) * window.innerWidth / 100;
                    const dy = (p2.y - p1.y) * 70 / 100;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < connectionDistance) {
                        const line = document.createElement('div');
                        line.classList.add('particle-line');

                        const angle = Math.atan2(dy, dx) * 180 / Math.PI;

                        line.style.width = `${distance}px`;
                        line.style.height = '1px';
                        line.style.left = `${p1.x}%`;
                        line.style.top = `${p1.y}%`;
                        line.style.transform = `rotate(${angle}deg)`;
                        line.style.opacity = 0.8 - (distance / connectionDistance);

                        particlesContainer.appendChild(line);
                        particleLines.push(line);
                    }
                }
            }

            requestAnimationFrame(animateParticles);
        }

        animateParticles();
    }
});
