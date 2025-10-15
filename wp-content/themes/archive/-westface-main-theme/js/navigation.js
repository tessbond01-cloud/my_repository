/**
 * JavaScript for Westface Child Theme
 * Handles mobile menu functionality and animations
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const mainMenuContainer = document.querySelector('.main-menu-container');
    
    if (menuToggle && mainMenuContainer) {
        menuToggle.addEventListener('click', function() {
            menuToggle.classList.toggle('active');
            mainMenuContainer.classList.toggle('active');
            
            // Toggle aria-expanded attribute
            const expanded = menuToggle.getAttribute('aria-expanded') === 'true' || false;
            menuToggle.setAttribute('aria-expanded', !expanded);
            
            // Toggle body scroll
            document.body.classList.toggle('menu-open');
        });
    }
    
    // Handle submenu toggles on mobile
    const menuItemsWithChildren = document.querySelectorAll('.menu-item-has-children');
    
    menuItemsWithChildren.forEach(function(item) {
        // Create submenu toggle button
        const submenuToggle = document.createElement('span');
        submenuToggle.className = 'submenu-toggle';
        submenuToggle.innerHTML = '<i class="fas fa-chevron-down"></i>';
        
        // Only append to direct link
        const itemLink = item.querySelector('a');
        if (itemLink) {
            itemLink.appendChild(submenuToggle);
        }
        
        // Handle click on mobile
        submenuToggle.addEventListener('click', function(e) {
            // Only apply this behavior on mobile
            if (window.innerWidth <= 991) {
                e.preventDefault();
                e.stopPropagation();
                
                const parent = this.closest('.menu-item-has-children');
                const subMenu = parent.querySelector('.sub-menu');
                
                // Toggle active class
                parent.classList.toggle('active');
                
                // Toggle submenu visibility with slide animation
                if (subMenu) {
                    if (subMenu.classList.contains('active')) {
                        // Slide up animation
                        subMenu.style.height = subMenu.scrollHeight + 'px';
                        // Force repaint
                        subMenu.offsetHeight;
                        subMenu.style.height = '0px';
                        
                        setTimeout(function() {
                            subMenu.classList.remove('active');
                            subMenu.style.height = '';
                        }, 300);
                    } else {
                        // Slide down animation
                        subMenu.classList.add('active');
                        subMenu.style.height = '0px';
                        // Force repaint
                        subMenu.offsetHeight;
                        subMenu.style.height = subMenu.scrollHeight + 'px';
                        
                        setTimeout(function() {
                            subMenu.style.height = '';
                        }, 300);
                    }
                }
            }
        });
    });
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Reset mobile menu state when resizing to desktop
            if (window.innerWidth > 991) {
                if (mainMenuContainer && mainMenuContainer.classList.contains('active')) {
                    mainMenuContainer.classList.remove('active');
                    menuToggle.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    document.body.classList.remove('menu-open');
                }
                
                // Reset all submenus
                const activeSubmenus = document.querySelectorAll('.sub-menu.active');
                activeSubmenus.forEach(function(submenu) {
                    submenu.classList.remove('active');
                    submenu.style.height = '';
                });
                
                const activeMenuItems = document.querySelectorAll('.menu-item-has-children.active');
                activeMenuItems.forEach(function(item) {
                    item.classList.remove('active');
                });
            }
        }, 250);
    });
    
    // Sticky header functionality
    const header = document.querySelector('.site-header');
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > 100) {
            header.classList.add('sticky');
            
            // Hide on scroll down, show on scroll up
            if (scrollTop > lastScrollTop) {
                header.classList.add('hide');
            } else {
                header.classList.remove('hide');
            }
        } else {
            header.classList.remove('sticky');
            header.classList.remove('hide');
        }
        
        lastScrollTop = scrollTop;
    });
    
    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            // Skip if it's just "#" or empty
            if (targetId === '#' || targetId === '') {
                return;
            }
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                
                // Close mobile menu if open
                if (mainMenuContainer && mainMenuContainer.classList.contains('active')) {
                    mainMenuContainer.classList.remove('active');
                    menuToggle.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                    document.body.classList.remove('menu-open');
                }
                
                // Calculate header height for offset
                const headerHeight = header ? header.offsetHeight : 0;
                
                window.scrollTo({
                    top: targetElement.offsetTop - headerHeight,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Add animation classes when elements come into view
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        
        elements.forEach(function(element) {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementPosition < windowHeight - 50) {
                element.classList.add('animated');
            }
        });
    };
    
    // Run once on page load
    animateOnScroll();
    
    // Run on scroll
    window.addEventListener('scroll', animateOnScroll);
});
