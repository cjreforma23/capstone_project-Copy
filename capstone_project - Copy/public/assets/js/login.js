
        document.getElementById('forgot-password-form').addEventListener('submit', function(event) {
            event.preventDefault();
            document.getElementById('otp-boxes').style.display = 'block';
        });
        
        document.getElementById('otp-form').addEventListener('submit', function(event) {
            event.preventDefault();
            document.getElementById('change-password-box').style.display = 'block';
        });

        // Gender JS
        function toggleCustomGender() {
            var genderSelect = document.getElementById("gender");
            var customGenderField = document.getElementById("customGenderField");

            // If "Custom" is selected, show the custom gender text input field
            if (genderSelect.value === "Custom") {
                customGenderField.style.display = "block";
            } else {
                customGenderField.style.display = "none";
            }
        }
    