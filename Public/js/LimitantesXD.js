  const passwordInput = document.getElementById("add_password");
  const message = document.getElementById("password_message");

  passwordInput.addEventListener("input", function () {
    const password = passwordInput.value;
    const errors = [];

    // Verificar longitud mínima de 8 caracteres
    if (password.length < 8) {
      errors.push("Debe tener al menos 8 caracteres");
    }

    // Verificar al menos una mayúscula
    if (!/[A-Z]/.test(password)) {
      errors.push("Debe incluir al menos una letra mayúscula");
    }

    // Verificar al menos un número
    if (!/[0-9]/.test(password)) {
      errors.push("Debe incluir al menos un número");
    }

    // Verificar al menos un carácter especial
    if (!/[!@#$%^&*(),.?":{}|<>_\-]/.test(password)) {
      errors.push("Debe incluir al menos un carácter especial");
    }

    // Mostrar los mensajes de error
    if (errors.length > 0) {
      message.textContent = "❌ " + errors.join(" | ");
      message.style.color = "red";
    } else {
      message.textContent = "✅ Contraseña válida";
      message.style.color = "green";
    }
  });

// ELIMINAR SI NO SE USA ESTE SCRIPT 29-10-25

