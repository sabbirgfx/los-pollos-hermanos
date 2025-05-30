// Testimonials section enhancement
document.addEventListener('DOMContentLoaded', function() {
    const testimonialCards = document.querySelectorAll('.testimonial-card');
    
    // Add hover effect dynamically
    testimonialCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
            this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
        });
    });
    
    // Animate testimonials when they come into view
    const observeTestimonials = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });
    
    testimonialCards.forEach(card => {
        observeTestimonials.observe(card);
    });
    
    // Add additional testimonials dynamically (if needed in the future)
    const additionalTestimonials = [
        {
            stars: 5,
            text: "I've been ordering from Los Pollos Hermanos for years, and the quality never disappoints. Their commitment to excellence is unmatched!",
            customer: "David R. from Colorado Springs"
        },
        {
            stars: 4,
            text: "The online ordering system is so convenient, and the delivery is always prompt. Great food and great service!",
            customer: "Lisa K. from Tucson"
        }
    ];
    
    // Function to load more testimonials (can be triggered by a button in the future)
    function loadMoreTestimonials() {
        const testimonialGrid = document.querySelector('.testimonial-grid');
        
        additionalTestimonials.forEach(testimonial => {
            const card = document.createElement('div');
            card.className = 'testimonial-card';
            
            // Create stars
            const starsDiv = document.createElement('div');
            starsDiv.className = 'stars';
            
            for (let i = 0; i < testimonial.stars; i++) {
                const star = document.createElement('i');
                star.className = 'fas fa-star';
                starsDiv.appendChild(star);
            }
            
            // Create testimonial text
            const text = document.createElement('p');
            text.textContent = `"${testimonial.text}"`;
            
            // Create customer name
            const customer = document.createElement('p');
            customer.className = 'customer';
            customer.textContent = `- ${testimonial.customer}`;
            
            // Append all elements to card
            card.appendChild(starsDiv);
            card.appendChild(text);
            card.appendChild(customer);
            
            // Add to grid with animation
            card.style.opacity = '0';
            testimonialGrid.appendChild(card);
            
            // Trigger animation
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    }
    
    // Uncomment this line to automatically load more testimonials when page loads
    // setTimeout(loadMoreTestimonials, 3000);
}); 