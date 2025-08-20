$(document).ready(function() {
    $('#phone').mask('+7 (000) 000-00-00');

    $('#captcha-image').attr('src', 'includes/captcha.php?' + new Date().getTime());

    function showError($field, message) {
        $field.closest('.form-group').find('.error').remove();
        $field.closest('.form-group').append(`<div class="error">${message}</div>`);
        return false;
    }

    function clearErrors() {
        $('.error').remove();
    }

    $('#email').on('blur', function() {
        const email = $(this).val().trim();
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError($(this), 'Неверный формат email');
        }
    });

    $('#phone').on('blur', function() {
        const phone = $(this).val().trim();
        const phoneDigits = phone.replace(/\D/g, '');
        if (phone && (phoneDigits.length !== 11 || !phoneDigits.startsWith('7'))) {
            showError($(this), 'Телефон должен содержать 11 цифр и начинаться с 7');
        }
    });

    $('#refresh-captcha').click(function() {
        $('#captcha-image').attr('src', 'includes/captcha.php?' + new Date().getTime());
        $('#captcha').val('');
    });

    $('#feedback-form').on('submit', function(e) {
        e.preventDefault();
        clearErrors();

        let isValid = true;
        const formData = new FormData(this);

        const fields = {
            'topic': { max: 255, required: true },
            'name': { max: 255, required: true },
            'phone': { required: true, pattern: /^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/ },
            'email': { pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, max: 255, required: true },
            'message': { max: 4096, required: true },
            'captcha': { required: true }
        };

        for (let fieldName in fields) {
            if (fieldName === 'privacy') continue;

            const $field = $(`[name="${fieldName}"]`);
            const value = $field.val().trim();

            if (fields[fieldName].required && !value) {
                isValid = showError($field, 'Поле обязательно для заполнения');
                continue;
            }

            if (fieldName === 'phone' && value) {
                const phoneDigits = value.replace(/\D/g, '');
                if (phoneDigits.length !== 11 || !phoneDigits.startsWith('7')) {
                    isValid = showError($field, 'Телефон должен содержать 11 цифр и начинаться с 7');
                }
            }

            if (fields[fieldName].pattern && value && !fields[fieldName].pattern.test(value)) {
                isValid = showError($field, 'Неверный формат');
            }

            if (fields[fieldName].max && value.length > fields[fieldName].max) {
                isValid = showError($field, `Превышено максимальное количество символов (${fields[fieldName].max})`);
            }
        }

        const $privacy = $('[name="privacy"]');
        if (!$privacy.is(':checked')) {
            isValid = showError($privacy, 'Необходимо согласие на обработку персональных данных');
        }

        if (!isValid) return;

        const dataToSend = $(this).serializeArray();
        const phoneField = dataToSend.find(item => item.name === 'phone');
        if (phoneField) {
            phoneField.value = phoneField.value.replace(/\D/g, '');
        }

        $.ajax({
            url: 'process.php',
            method: 'POST',
            data: $.param(dataToSend),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Сообщение успешно отправлено!');
                    $('#feedback-form')[0].reset();
                    $('#captcha-image').attr('src', 'includes/captcha.php?' + new Date().getTime());
                } else {
                    if (response.errors && response.errors.length > 0) {
                        let errorMessage = 'Ошибки:\n';
                        response.errors.forEach(error => {
                            errorMessage += '• ' + error + '\n';
                        });
                        alert(errorMessage);
                    } else {
                        alert('Произошла неизвестная ошибка');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Ошибка AJAX:', error);
                console.error('Ответ сервера:', xhr.responseText);
                alert('Произошла ошибка при отправке формы. Попробуйте еще раз.');
            }
        });
    });
});