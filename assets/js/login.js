$(document).ready(function() {
    // Initialize Select2
    $('#semester').select2();

    // Configure NProgress
    NProgress.configure({ 
        showSpinner: true,
        minimum: 0.1,
        easing: 'ease',
        speed: 500,
        trickle: true,
        trickleSpeed: 200
    });

    // Start progress on page load
    NProgress.start();

    // End progress when page is fully loaded
    $(window).on('load', function() {
        NProgress.done();
    });

    $('#getDetails').click(function() {
        const registration = $('#registration').val();
        const semester = $('#semester').val();
        
        if (!registration || !semester) {
            alert('Please enter registration number and select semester');
            return;
        }

        NProgress.start();
        
        $.ajax({
            url: 'get_student_details.php',
            method: 'POST',
            data: {
                registration: registration,
                semester: semester
            },
            success: function(response) {
                try {
                    const data = JSON.parse(response);
                    if (data.success) {
                        $('#studentName').text(data.name);
                        $('#studentDetails').removeClass('hidden');
                    } else {
                        alert(data.message);
                        $('#studentDetails').addClass('hidden');
                    }
                } catch(e) {
                    alert('Error processing response');
                    $('#studentDetails').addClass('hidden');
                }
            },
            error: function() {
                alert('Error fetching student details');
                $('#studentDetails').addClass('hidden');
            },
            complete: function() {
                NProgress.done();
            }
        });
    });

    // Update the form submission handler
    $('#loginForm').submit(function(e) {
        e.preventDefault(); // Prevent default form submission
        
        if($('#dob').val()) {
            // Show loading overlay
            $('#loadingOverlay').removeClass('hidden');
            
            // Wait for 2 seconds before submitting
            setTimeout(() => {
                // Submit the form programmatically
                const formData = $(this).serialize();
                
                $.ajax({
                    url: 'verify_login.php',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        window.location.href = 'dashboard.php';
                    },
                    error: function() {
                        $('#loadingOverlay').addClass('hidden');
                        alert('Error during login. Please try again.');
                    }
                });
            }, 2000);
        }
    });

    // Show progress bar on page refresh
    window.onbeforeunload = function() {
        NProgress.start();
    };
}); 