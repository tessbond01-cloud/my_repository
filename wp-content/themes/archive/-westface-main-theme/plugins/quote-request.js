/**
 * Quote Request JavaScript
 * Handles quote request modal and form functionality
 */

(function($) {
    'use strict';

    // Quote Request Modal functionality
    function initQuoteRequestModal() {
        // Create modal HTML if it doesn't exist
        if (!$('#quote-request-modal').length) {
            const modalHTML = `
                <div id="quote-request-modal" class="quote-modal" style="display: none;">
                    <div class="quote-modal-content">
                        <span class="quote-modal-close">&times;</span>
                        <h2>Jätä tarjouspyyntö</h2>
                        <form id="quote-request-form">
                            <div class="form-group">
                                <label for="quote-name">Nimi *</label>
                                <input type="text" id="quote-name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="quote-email">Sähköposti *</label>
                                <input type="email" id="quote-email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="quote-phone">Puhelin</label>
                                <input type="tel" id="quote-phone" name="phone">
                            </div>
                            <div class="form-group">
                                <label for="quote-category">Tuotekategoria</label>
                                <select id="quote-category" name="category">
                                    <option value="">Valitse kategoria</option>
                                    <option value="laminaatti">Laminaatti</option>
                                    <option value="parketti">Parketti</option>
                                    <option value="vinyyli">Vinyyli</option>
                                    <option value="muu">Muu</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="quote-message">Viesti</label>
                                <textarea id="quote-message" name="message" rows="4"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Lähetä tarjouspyyntö</button>
                        </form>
                    </div>
                </div>
            `;
            $('body').append(modalHTML);
        }

        // Open modal when quote request buttons are clicked
        $(document).on('click', '.btn-accent, .quote-request-btn, [href="#contact"]', function(e) {
            e.preventDefault();
            $('#quote-request-modal').fadeIn();
        });

        // Close modal
        $(document).on('click', '.quote-modal-close, #quote-request-modal', function(e) {
            if (e.target === this) {
                $('#quote-request-modal').fadeOut();
            }
        });

        // Prevent modal from closing when clicking inside content
        $(document).on('click', '.quote-modal-content', function(e) {
            e.stopPropagation();
        });

        // Handle form submission
        $(document).on('submit', '#quote-request-form', function(e) {
            e.preventDefault();
            
            const formData = {
                name: $('#quote-name').val(),
                email: $('#quote-email').val(),
                phone: $('#quote-phone').val(),
                category: $('#quote-category').val(),
                message: $('#quote-message').val()
            };

            // Simple validation
            if (!formData.name || !formData.email) {
                alert('Nimi ja sähköposti ovat pakollisia kenttiä.');
                return;
            }

            // Show success message
            alert('Tarjouspyyntö lähetetty onnistuneesti! Otamme yhteyttä pian.');
            $('#quote-request-modal').fadeOut();
            $('#quote-request-form')[0].reset();
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initQuoteRequestModal();
    });

})(jQuery);

