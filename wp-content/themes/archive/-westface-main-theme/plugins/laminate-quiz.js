/**
 * Westface Laminate Quiz JavaScript
 * Interactive laminate selection quiz with modal functionality
 * Version: 2.2.0
 */

(function ($) {
    'use strict';

    // Quiz configuration
    const quizConfig = {
        totalQuestions: 5,
        currentQuestion: 1,
        answers: {},
        isModalMode: false
    };

    // Quiz DOM elements
    let quizContainer, modal, questions, results, contactForm, navigation;

    // Initialize quiz
    function initLaminateQuiz() {
        // Cache DOM elements for performance
        modal = $('#laminate-quiz-modal');
        quizContainer = $('#laminate-quiz-container');

        if (!quizContainer.length) {
            return; // Don't initialize if the quiz HTML is not on the page
        }

        questions = quizContainer.find('.quiz-question');
        results = quizContainer.find('.quiz-results');
        contactForm = quizContainer.find('.quiz-contact-form');
        navigation = quizContainer.find('.quiz-navigation');

        // Check if we're in modal mode or embedded mode
        if (modal.length) {
            quizConfig.isModalMode = true;
            initModalQuiz();
        } else {
            initEmbeddedQuiz();
        }

        // Bind events
        bindQuizEvents();
    }

    // Initialize modal version
    function initModalQuiz() {
        // Modal trigger buttons
        $(document).on('click', '.laminate-quiz-btn, [data-quiz="laminate"]', function (e) {
            e.preventDefault();
            e.stopPropagation();
            showLaminateQuiz();
        });

        // Modal close events
        modal.on('click', '.quiz-modal-close', function () {
            hideLaminateQuiz();
        });

        modal.on('click', function (e) {
            if (e.target === this) {
                hideLaminateQuiz();
            }
        });

        // Prevent modal from closing when clicking inside content
        modal.on('click', '.quiz-modal-content', function (e) {
            e.stopPropagation();
        });

        // Handle ESC key to close modal
        $(document).on('keydown', function (e) {
            if (e.key === "Escape" && modal.hasClass('show')) {
                hideLaminateQuiz();
            }
        });
    }

    // Initialize embedded version
    function initEmbeddedQuiz() {
        resetQuiz();
        showCurrentQuestion();
    }

    // Show modal quiz
    function showLaminateQuiz() {
        resetQuiz();
        modal.addClass('show');
        $('body').css('overflow', 'hidden'); // Prevent background scrolling
        showCurrentQuestion();
    }

    // Hide modal quiz
    function hideLaminateQuiz() {
        modal.removeClass('show');
        $('body').css('overflow', ''); // Restore background scrolling
        // A short delay to allow the fade-out animation to complete before resetting
        setTimeout(resetQuiz, 300);
    }

    // Reset quiz to initial state
    function resetQuiz() {
        quizConfig.currentQuestion = 1;
        quizConfig.answers = {};

        questions.removeClass('active').hide();
        results.hide();
        contactForm.hide();
        navigation.show();

        // Reset form fields
        $('#sample-request-form').trigger('reset');

        // Restore original form content if it was replaced by success message
        if ($('.quiz-success-message').length > 0) {
            const originalFormHtml = `
                <h3><i class="fas fa-envelope"></i> ${laminate_quiz_ajax.strings.contact_title || 'Yhteystiedot mallipalojen tilaamiseen'}</h3>
                <form id="sample-request-form" novalidate>
                    <input type="hidden" name="quiz_nonce" value="${laminate_quiz_ajax.nonce}" />
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sample_name">Nimi *</label>
                            <input type="text" id="sample_name" name="name" required autocomplete="name">
                        </div>
                        <div class="form-group">
                            <label for="sample_email">Sähköposti *</label>
                            <input type="email" id="sample_email" name="email" required autocomplete="email">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sample_phone">Puhelinnumero</label>
                            <input type="tel" id="sample_phone" name="phone" autocomplete="tel">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sample_address">Toimitusosoite *</label>
                        <textarea id="sample_address" name="address" rows="3" required placeholder="Katu, postinumero, kaupunki" autocomplete="street-address"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary sample-request-btn">
                        <i class="fas fa-cube"></i> Tilaa mallipalat
                    </button>
                </form>
            `;
            contactForm.html(originalFormHtml);
        }

        updateProgress();
    }

    // Bind quiz events
    function bindQuizEvents() {
        // Radio button selection
        quizContainer.on('change', '.quiz-question input[type="radio"]', function () {
            const $option = $(this).closest('.quiz-option');
            $option.siblings().removeClass('selected');
            $option.addClass('selected');
        });

        // Next button
        quizContainer.on('click', '.quiz-next', function () {
            handleNextQuestion();
        });

        // Previous button
        quizContainer.on('click', '.quiz-prev', function () {
            handlePreviousQuestion();
        });

        // Sample request form submission
        quizContainer.on('submit', '#sample-request-form', function (e) {
            e.preventDefault();
            handleSampleRequest($(this));
        });
    }

    // Handle next question
    function handleNextQuestion() {
        const currentQuestionEl = questions.filter('[data-question="' + quizConfig.currentQuestion + '"]');
        const selectedOption = currentQuestionEl.find('input[type="radio"]:checked');

        if (selectedOption.length === 0) {
            showAlert(laminate_quiz_ajax.strings.select_option);
            return;
        }

        quizConfig.answers[selectedOption.attr('name')] = selectedOption.val();

        if (quizConfig.currentQuestion < quizConfig.totalQuestions) {
            quizConfig.currentQuestion++;
            showCurrentQuestion();
        } else {
            showResults();
        }
    }

    // Handle previous question
    function handlePreviousQuestion() {
        if (quizConfig.currentQuestion > 1) {
            quizConfig.currentQuestion--;
            showCurrentQuestion();
        }
    }

    // Show current question
    function showCurrentQuestion() {
        questions.removeClass('active').hide();
        const currentQuestionEl = questions.filter('[data-question="' + quizConfig.currentQuestion + '"]');
        currentQuestionEl.addClass('active').fadeIn(200);

        updateNavigationButtons();
        updateProgress();
        restorePreviousAnswer();
    }

    // Update navigation buttons
    function updateNavigationButtons() {
        const $prevBtn = navigation.find('.quiz-prev');
        const $nextBtn = navigation.find('.quiz-next');

        $prevBtn.toggle(quizConfig.currentQuestion > 1);

        if (quizConfig.currentQuestion === quizConfig.totalQuestions) {
            $nextBtn.html('<i class="fas fa-check"></i> Näytä tulokset');
        } else {
            $nextBtn.html('Seuraava <i class="fas fa-arrow-right"></i>');
        }
    }

    // Update progress bar
    function updateProgress() {
        const progress = (quizConfig.currentQuestion - 1) / quizConfig.totalQuestions * 100;
        quizContainer.find('.progress-fill').css('width', progress + '%');
        quizContainer.find('.current-question').text(quizConfig.currentQuestion);
    }

    // Restore previous answer
    function restorePreviousAnswer() {
        const currentQuestionEl = questions.filter('[data-question="' + quizConfig.currentQuestion + '"]');
        const questionName = currentQuestionEl.find('input[type="radio"]').first().attr('name');

        if (quizConfig.answers[questionName]) {
            const savedValue = quizConfig.answers[questionName];
            const savedInput = currentQuestionEl.find('input[value="' + savedValue + '"]');
            savedInput.prop('checked', true).closest('.quiz-option').addClass('selected');
        }
    }

    // Show results
    function showResults() {
        questions.hide();
        navigation.hide();

        const recommendations = generateRecommendations(quizConfig.answers);
        displayRecommendations(recommendations);

        results.fadeIn(300);

        setTimeout(function () {
            contactForm.slideDown(400);
        }, 800);
    }

    // Generate product recommendations based on answers
    function generateRecommendations(answers) {
        // Placeholder for real recommendation logic
        const productRecommendations = {
            'premium-moderni-vaalea': { name: 'Premium Valkoinen Tammi', description: 'Korkealaatuinen, minimalistinen design.', price: '65€/m²', features: ['Vedenkestävä', 'Naarmuton pinta', '30v takuu'] },
            'premium-moderni-tumma': { name: 'Premium Musta Tammi', description: 'Tyylikäs tumma laminaatti moderniin kotiin.', price: '68€/m²', features: ['Vedenkestävä', 'Naarmuton pinta', '30v takuu'] },
            'vahva-perhe': { name: 'Kestävä Perhelaminaatti', description: 'Erittäin kestävä vilkkaaseen kotiin.', price: '45€/m²', features: ['AC5 kulutusluokka', 'Lapsiturvallinen', '25v takuu'] },
            'klassinen-keskihinta': { name: 'Klassinen Tammi', description: 'Ajaton valinta, joka sopii kaikkiin koteihin.', price: '38€/m²', features: ['AC4 kulutusluokka', 'Helppo asentaa', '20v takuu'] },
            'edullinen-perus': { name: 'Perus Laminaatti', description: 'Laadukas ja edullinen vaihtoehto.', price: '25€/m²', features: ['AC3 kulutusluokka', 'Hyvä hinta-laatusuhde', '15v takuu'] }
        };

        if (answers.budget === 'premium' && answers.style_preference === 'moderni') {
            return answers.color_preference === 'vaalea' ? [productRecommendations['premium-moderni-vaalea']] : [productRecommendations['premium-moderni-tumma']];
        }
        if (answers.usage_level === 'vahva') return [productRecommendations['vahva-perhe']];
        if (answers.budget === 'edullinen') return [productRecommendations['edullinen-perus']];

        return [productRecommendations['klassinen-keskihinta']]; // Default
    }

    // Display recommendations
    function displayRecommendations(recommendations) {
        let html = '<div class="product-recommendations">';
        recommendations.forEach(function (product) {
            html += `
                <div class="product-recommendation">
                    <h4>${product.name}</h4>
                    <p class="product-description">${product.description}</p>
                    <div class="product-price">${product.price}</div>
                    <ul class="product-features">
                        ${product.features.map(feature => `<li><i class="fas fa-check"></i> ${feature}</li>`).join('')}
                    </ul>
                </div>
            `;
        });
        html += '</div>';
        results.find('.recommended-products').html(html);
    }

    // Handle sample request form submission
    function handleSampleRequest($form) {
        const $button = $form.find('.sample-request-btn');
        const originalText = $button.html();

        if (!validateForm($form)) return;

        $button.addClass('loading').prop('disabled', true).html(`<i class="fas fa-spinner fa-spin"></i> ${laminate_quiz_ajax.strings.loading}`);

        const formData = {
            action: 'submit_sample_request',
            nonce: laminate_quiz_ajax.nonce,
            name: $('#sample_name').val().trim(),
            email: $('#sample_email').val().trim(),
            phone: $('#sample_phone').val().trim(),
            address: $('#sample_address').val().trim(),
            quiz_results: JSON.stringify(quizConfig.answers),
            recommended_products: JSON.stringify(generateRecommendations(quizConfig.answers))
        };

        $.ajax({
            url: laminate_quiz_ajax.ajax_url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showSuccessMessage();
                } else {
                    showAlert(response.data.message || laminate_quiz_ajax.strings.error);
                }
            },
            error: function () {
                showAlert(laminate_quiz_ajax.strings.network_error);
            },
            complete: function () {
                $button.removeClass('loading').prop('disabled', false).html(originalText);
            }
        });
    }

    // Validate form fields
    function validateForm($form) {
        let isValid = true;
        $form.find('[required]').each(function () {
            if (!$(this).val().trim()) {
                isValid = false;
            }
        });

        if (!isValid) {
            showAlert(laminate_quiz_ajax.strings.validation_error);
            return false;
        }

        const email = $('#sample_email').val().trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showAlert(laminate_quiz_ajax.strings.email_error);
            return false;
        }

        return true;
    }

    // Show success message
    function showSuccessMessage() {
        const successHtml = `
            <div class="quiz-success-message">
                <i class="fas fa-check-circle"></i>
                <h3>Kiitos tilauksestasi!</h3>
                <p>Mallipalat lähetetään sinulle pian. Saat vahvistuksen sähköpostiisi.</p>
            </div>
        `;
        contactForm.html(successHtml);

        if (quizConfig.isModalMode) {
            setTimeout(hideLaminateQuiz, 3000);
        }
    }

    // Show alert message
    function showAlert(message) {
        let $alert = quizContainer.find('.quiz-alert');
        if ($alert.length === 0) {
            $alert = $('<div class="quiz-alert" style="display: none;"></div>');
            quizContainer.find('.quiz-content').prepend($alert);
        }
        $alert.html(`<i class="fas fa-exclamation-triangle"></i> ${message}`).slideDown(200);
        setTimeout(() => $alert.slideUp(200), 5000);
    }

    // Initialize when document is ready
    $(document).ready(function () {
        initLaminateQuiz();
    });

})(jQuery);
