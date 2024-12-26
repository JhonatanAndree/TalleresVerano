class ValidationUtils {
    static patterns = {
        dni: /^\d{8}$/,
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        phone: /^9\d{8}$/,
        nombres: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/,
        decimales: /^\d+(\.\d{1,2})?$/
    };

    static messages = {
        required: 'Este campo es obligatorio',
        dni: 'DNI debe tener 8 dígitos',
        email: 'Email no válido',
        phone: 'Celular debe empezar con 9 y tener 9 dígitos',
        nombres: 'Solo letras, mínimo 2 caracteres',
        min: 'Valor mínimo es ',
        max: 'Valor máximo es ',
        decimales: 'Formato de número no válido'
    };

    static validateField(input, rules = {}) {
        const value = input.value.trim();
        const errors = [];

        if (rules.required && !value) {
            errors.push(this.messages.required);
            return errors;
        }

        if (value) {
            if (rules.pattern && this.patterns[rules.pattern]) {
                if (!this.patterns[rules.pattern].test(value)) {
                    errors.push(this.messages[rules.pattern]);
                }
            }

            if (rules.min !== undefined && parseFloat(value) < rules.min) {
                errors.push(this.messages.min + rules.min);
            }

            if (rules.max !== undefined && parseFloat(value) > rules.max) {
                errors.push(this.messages.max + rules.max);
            }

            if (rules.custom) {
                const customError = rules.custom(value);
                if (customError) errors.push(customError);
            }
        }

        return errors;
    }

    static showFieldError(input, errors) {
        const errorDiv = input.nextElementSibling;
        if (errorDiv && errorDiv.classList.contains('error-message')) {
            errorDiv.textContent = errors.join('. ');
            errorDiv.style.display = errors.length ? 'block' : 'none';
            input.classList.toggle('is-invalid', errors.length > 0);
        }
    }

    static validateForm(formId, rules) {
        const form = document.getElementById(formId);
        let isValid = true;
        const formData = new FormData();

        Object.keys(rules).forEach(fieldName => {
            const input = form.querySelector(`[name="${fieldName}"]`);
            if (input) {
                const errors = this.validateField(input, rules[fieldName]);
                this.showFieldError(input, errors);
                if (errors.length) isValid = false;
                formData.append(fieldName, input.value.trim());
            }
        });

        return { isValid, formData };
    }
}

// Form Validators
const FormValidators = {
    estudiante: {
        nombre: { required: true, pattern: 'nombres' },
        apellido: { required: true, pattern: 'nombres' },
        dni: { required: true, pattern: 'dni' },
        edad: { required: true, min: 3, max: 17 },
        id_taller: { required: true }
    },

    docente: {
        nombre: { required: true, pattern: 'nombres' },
        apellido: { required: true, pattern: 'nombres' },
        email: { required: true, pattern: 'email' },
        telefono: { required: true, pattern: 'phone' }
    },

    taller: {
        nombre: { required: true },
        capacidad_maxima: { required: true, min: 5, max: 30 },
        id_sede: { required: true },
        id_aula: { required: true }
    },

    pago: {
        monto: { required: true, pattern: 'decimales', min: 0.01 },
        concepto: { required: true }
    }
};

// Ejemplo de uso
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-validator]');
    
    forms.forEach(form => {
        const validatorName = form.dataset.validator;
        if (FormValidators[validatorName]) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const { isValid, formData } = ValidationUtils.validateForm(
                    form.id, 
                    FormValidators[validatorName]
                );
                
                if (isValid) {
                    // Enviar formulario
                    fetch(form.action, {
                        method: form.method,
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al procesar la solicitud');
                    });
                }
            });
        }
    });
});