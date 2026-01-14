(function ($) {
    "use strict";
    
    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();
    
    
    // Initiate the wowjs
    new WOW().init();
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        items: 1,
        dots: false,
        loop: true,
        nav : true,
        navText : [
            '<i class="bi bi-arrow-left"></i>',
            '<i class="bi bi-arrow-right"></i>'
        ],
    });

    $(document).on('click', '#contactSubmit', function () {

        const form = document.getElementById('contactForm');
        const messageDiv = $('#formMessage');

        // 🔐 Basic validation (since submit is disabled)
        if (!form.checkValidity()) {
            form.reportValidity(); // shows browser validation
            return;
        }

        const submitBtn = $(this);
        const btnText = submitBtn.find('.btn-text');
        const btnLoader = submitBtn.find('.btn-loader');

        // Loader ON
        submitBtn.prop('disabled', true);
        btnText.addClass('d-none');
        btnLoader.removeClass('d-none');

        messageDiv.html('');

        $.ajax({
            type: 'POST',
            url: form.action, // sendmail.php
            data: $(form).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    messageDiv.html('<div class="alert alert-success">' + response.message + '</div>');
                    form.reset();
                    setTimeout(() => messageDiv.html(''), 5000);
                } else {
                    messageDiv.html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function () {
                messageDiv.html('<div class="alert alert-danger">Something went wrong. Please try again.</div>');
            },
            complete: function () {
                // Loader OFF
                submitBtn.prop('disabled', false);
                btnLoader.addClass('d-none');
                btnText.removeClass('d-none');
            }
        });
    });


    // Reservation form handler
    $('#reservationForm').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        var messageDiv = $('#reservationMessage');

        $.ajax({
            type: 'POST',
            url: 'sendmail.php',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                messageDiv.html('<div class="alert alert-info">Sending reservation...</div>');
            },
            success: function(response) {
                if (response.success) {
                    messageDiv.html('<div class="alert alert-success">' + response.message + '</div>');
                    $('#reservationForm')[0].reset();
                } else {
                    messageDiv.html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                messageDiv.html('<div class="alert alert-danger">Something went wrong. Please try again.</div>');
            }
        });
        return false;
    });

    
})(jQuery);


