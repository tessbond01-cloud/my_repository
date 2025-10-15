/**
 * Laminate Quiz JavaScript
 * Handles interactive laminate selection quiz
 */

(function($) {
    'use strict';

    const quizData = {
        questions: [
            {
                id: 1,
                question: "Mihin tilaan etsit laminaattia?",
                options: [
                    "Olohuone",
                    "Makuuhuone", 
                    "Keittiö",
                    "Kylpyhuone",
                    "Työhuone"
                ]
            },
            {
                id: 2,
                question: "Kuinka paljon liikennettä tilassa on?",
                options: [
                    "Vähän (makuuhuone)",
                    "Keskiverto (olohuone)",
                    "Paljon (käytävä, keittiö)",
                    "Erittäin paljon (kaupallinen tila)"
                ]
            },
            {
                id: 3,
                question: "Mikä värisävy miellyttää sinua?",
                options: [
                    "Vaalea (valkoinen, beige)",
                    "Keskitumma (ruskea, harmaa)",
                    "Tumma (musta, tummanruskea)",
                    "Värikäs (sininen, vihreä)"
                ]
            },
            {
                id: 4,
                question: "Mikä on budjettisi neliömetrille?",
                options: [
                    "Alle 20€/m²",
                    "20-40€/m²",
                    "40-60€/m²",
                    "Yli 60€/m²"
                ]
            },
            {
                id: 5,
                question: "Kuinka tärkeää on vedenkestävyys?",
                options: [
                    "Ei tärkeää",
                    "Jonkin verran tärkeää",
                    "Tärkeää",
                    "Erittäin tärkeää"
                ]
            }
        ]
    };

    let currentQuestion = 0;
    let answers = [];

    function initLaminateQuiz() {
        // Create quiz modal HTML
        if (!$('#laminate-quiz-modal').length) {
            const modalHTML = `
                <div id="laminate-quiz-modal" class="quiz-modal" style="display: none;">
                    <div class="quiz-modal-content">
                        <span class="quiz-modal-close">&times;</span>
                        <div id="quiz-content">
                            <div id="quiz-questions"></div>
                            <div id="quiz-contact-form" style="display: none;">
                                <h3>Jätä yhteystietosi saadaksesi suositukset</h3>
                                <form id="quiz-contact-form-element">
                                    <div class="form-group">
                                        <label for="quiz-contact-name">Nimi *</label>
                                        <input type="text" id="quiz-contact-name" name="name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="quiz-contact-email">Sähköposti *</label>
                                        <input type="email" id="quiz-contact-email" name="email" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="quiz-contact-phone">Puhelin</label>
                                        <input type="tel" id="quiz-contact-phone" name="phone">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Tilaa mallipalat</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHTML);
        }

        // Open quiz modal
        $(document).on('click', '.laminate-quiz-btn, [data-quiz="laminate"]', function(e) {
            e.preventDefault();
            currentQuestion = 0;
            answers = [];
            showQuestion();
            $('#laminate-quiz-modal').fadeIn();
        });

        // Close modal
        $(document).on('click', '.quiz-modal-close, #laminate-quiz-modal', function(e) {
            if (e.target === this) {
                $('#laminate-quiz-modal').fadeOut();
            }
        });

        // Prevent modal from closing when clicking inside content
        $(document).on('click', '.quiz-modal-content', function(e) {
            e.stopPropagation();
        });

        // Handle answer selection
        $(document).on('click', '.quiz-option', function() {
            const answer = $(this).data('answer');
            answers[currentQuestion] = answer;
            
            currentQuestion++;
            
            if (currentQuestion < quizData.questions.length) {
                showQuestion();
            } else {
                showContactForm();
            }
        });

        // Handle contact form submission
        $(document).on('submit', '#quiz-contact-form-element', function(e) {
            e.preventDefault();
            
            const contactData = {
                name: $('#quiz-contact-name').val(),
                email: $('#quiz-contact-email').val(),
                phone: $('#quiz-contact-phone').val(),
                answers: answers
            };

            // Simple validation
            if (!contactData.name || !contactData.email) {
                alert('Nimi ja sähköposti ovat pakollisia kenttiä.');
                return;
            }

            // Show success message
            alert('Mallipalapyyntö lähetetty onnistuneesti! Lähetämme sinulle sopivat mallipalat.');
            $('#laminate-quiz-modal').fadeOut();
        });
    }

    function showQuestion() {
        const question = quizData.questions[currentQuestion];
        const progressPercent = ((currentQuestion + 1) / quizData.questions.length) * 100;
        
        let optionsHTML = '';
        question.options.forEach((option, index) => {
            optionsHTML += `<button class="quiz-option" data-answer="${option}">${option}</button>`;
        });

        const questionHTML = `
            <div class="quiz-progress">
                <div class="quiz-progress-bar" style="width: ${progressPercent}%"></div>
            </div>
            <h3>Kysymys ${currentQuestion + 1}/${quizData.questions.length}</h3>
            <h2>${question.question}</h2>
            <div class="quiz-options">
                ${optionsHTML}
            </div>
        `;

        $('#quiz-questions').html(questionHTML);
        $('#quiz-contact-form').hide();
        $('#quiz-questions').show();
    }

    function showContactForm() {
        $('#quiz-questions').hide();
        $('#quiz-contact-form').show();
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initLaminateQuiz();
    });

})(jQuery);

