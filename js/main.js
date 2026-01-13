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

    // Contact form handler
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var messageDiv = $('#formMessage');
        
        $.ajax({
            type: 'POST',
            url: 'sendmail.php',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                messageDiv.html('<div class="alert alert-info">Sending...</div>');
            },
            success: function(response) {
                if(response.success) {
                    messageDiv.html('<div class="alert alert-success">' + response.message + '</div>');
                    $('#contactForm')[0].reset();
                    setTimeout(function() {
                        messageDiv.html('');
                    }, 5000);
                } else {
                    messageDiv.html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                messageDiv.html('<div class="alert alert-danger">Error: ' + (xhr.responseJSON?.message || 'An error occurred') + '</div>');
            }
        });
    });
    
})(jQuery);


