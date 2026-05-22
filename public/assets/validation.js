(function () {
    const form = document.getElementById("contact-form");

    if (!form) {
        return;
    }

    const fields = {
        name: {
            element: form.elements.name,
            validate(value) {
                if (value.trim() === "") {
                    return "Please enter your name.";
                }

                if (value.trim().length > 120) {
                    return "Name must be 120 characters or fewer.";
                }

                return "";
            },
        },
        email: {
            element: form.elements.email,
            validate(value) {
                const trimmed = value.trim();

                if (trimmed === "") {
                    return "Please enter your email address.";
                }

                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
                    return "Please enter a valid email address.";
                }

                if (trimmed.length > 190) {
                    return "Email must be 190 characters or fewer.";
                }

                return "";
            },
        },
        phone: {
            element: form.elements.phone,
            validate(value) {
                const normalized = value.trim().replace(/[\s().-]+/g, "");

                if (normalized === "") {
                    return "Please enter your South African phone number.";
                }

                if (!/^(?:\+27|27|0)[1-8][0-9]{8}$/.test(normalized)) {
                    return "Use a valid SA number, for example 021 123 4567 or +27 82 123 4567.";
                }

                return "";
            },
        },
        message: {
            element: form.elements.message,
            validate(value) {
                if (value.trim() === "") {
                    return "Please enter a message.";
                }

                if (value.trim().length > 2000) {
                    return "Message must be 2,000 characters or fewer.";
                }

                return "";
            },
        },
    };

    function setFieldState(name, message) {
        const field = fields[name].element;
        const error = document.getElementById(`${name}-error`);

        field.classList.toggle("is-invalid", message !== "");
        field.classList.toggle("is-valid", message === "" && field.value.trim() !== "");
        field.setAttribute("aria-invalid", message !== "" ? "true" : "false");

        if (error && message !== "") {
            error.textContent = message;
        }
    }

    function validateField(name) {
        const message = fields[name].validate(fields[name].element.value);
        setFieldState(name, message);

        return message === "";
    }

    Object.keys(fields).forEach((name) => {
        const field = fields[name].element;
        field.addEventListener("blur", () => validateField(name));
        field.addEventListener("input", () => {
            if (field.classList.contains("is-invalid") || field.classList.contains("is-valid")) {
                validateField(name);
            }
        });
    });

    form.addEventListener("submit", (event) => {
        const isValid = Object.keys(fields).every(validateField);

        if (!isValid) {
            event.preventDefault();
            const firstInvalid = form.querySelector(".is-invalid");
            firstInvalid?.focus();
        }
    });
})();
