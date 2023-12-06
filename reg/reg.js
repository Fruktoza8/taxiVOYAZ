document.addEventListener("DOMContentLoaded", function() {
    const roleButtons = document.querySelectorAll(".role-button");
    const selectedRoleInput = document.getElementById("selected_role");
    const registrationForm = document.querySelector("form");
    const errorMessage = document.getElementById("error-message");
    const passwordField = document.getElementById("password");
    const confirmPasswordField = document.getElementById("confirm_password");

    // Установка выбранной роли при загрузке
    roleButtons.forEach(function(button) {
        if (button.getAttribute("data-role") === selectedRoleInput.value) {
            button.classList.add("selected");
        }

        button.addEventListener("click", function() {
            // Очистить выделение со всех кнопок
            roleButtons.forEach(function(btn) {
                btn.classList.remove("selected");
            });

            // Выделить выбранную кнопку
            button.classList.add("selected");

            // Установить выбранную роль в скрытое поле
            selectedRoleInput.value = button.getAttribute("data-role");
        });
    });

    registrationForm.addEventListener("submit", function(event) {
        let formValid = true;
        errorMessage.style.display = "none";

        // Получение значений полей
        const name = document.getElementById("name").value;
        const lastname = document.getElementById("lastname").value;

        // Проверка на содержание только русских букв в name и lastname
        if (!/^[а-яё]+$/iu.test(name) || !/^[а-яё]+$/iu.test(lastname)) {
            errorMessage.textContent = "Имя и фамилия должны содержать только русские буквы";
            errorMessage.style.display = "block";
            formValid = false;
        }

        // Проверка сложности пароля
        if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(passwordField.value)) {
            errorMessage.textContent = "Пароль должен содержать как минимум одну букву верхнего регистра, одну букву нижнего регистра и одну цифру, и иметь длину не менее 8 символов";
            formValid = false;
        } else if (passwordField.value !== confirmPasswordField.value) {
            errorMessage.textContent = "Пароли не совпадают";
            formValid = false;
        }

        if (!formValid) {
            errorMessage.style.display = "block";
            event.preventDefault(); // Остановка отправки формы
        }

        // AJAX запрос для проверки email
        var xhr = new XMLHttpRequest();
        xhr.open("POST", 'check_email_unique.php', true); // предполагается, что есть такой скрипт
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                // Проверяем, существует ли email
                if (this.responseText === "exists") {
                    errorMessage.textContent = "Этот email уже зарегистрирован";
                    errorMessage.style.display = "block";
                    formValid = false;
                    event.preventDefault(); // Остановка отправки формы
                }
            }
        }
        xhr.send("email=" + encodeURIComponent(document.getElementById("email").value));

        if (!formValid) {
            errorMessage.style.display = "block";
            event.preventDefault(); // Остановка отправки формы
        }
    });
});
