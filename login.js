$(document).ready(function () {
  // Configuration
  const CONFIG = {
    API_URL: "http://localhost/Urbansiana%20-%20Reporter/",
    TIMEOUT_DURATION: 10000,
    MIN_PASSWORD_LENGTH: 6,
    GAUTH_CODE_LENGTH: 6,
    ALERT_DURATION: 3000,
  };

  // Validation rules
  const VALIDATORS = {
    email: {
      regex: {
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        username: /^[a-zA-Z0-9_]{3,20}$/,
      },
      validate: function (value) {
        return this.regex.email.test(value) || this.regex.username.test(value);
      },
    },
    password: {
      validate: (value) => value.length >= CONFIG.MIN_PASSWORD_LENGTH,
    },
  };

  class LoginSystem {
    constructor() {
      this.modal = $("#gauthModal");
      this.loginData = null;
      this.bindEvents();
    }

    bindEvents() {
      // Login form events
      $("#loginForm").on("submit", (e) => this.handleLogin(e));
      $("#email").on("blur", (e) =>
        this.validateField("email", e.target.value)
      );
      $("#password").on("blur", (e) =>
        this.validateField("password", e.target.value)
      );

      // Modal events
      $("#gauthCode").on("input", this.handleCodeInput);
      $("#cancelGauth").on("click", () => this.closeModal());
      $("#submitGauth").on("click", () => this.handleVerification());
      $("#gauthCode").on("keypress", (e) => {
        if (e.which === 13) this.handleVerification();
      });
    }

    validateField(type, value) {
      const trimmedValue = value.trim();
      if (trimmedValue && !VALIDATORS[type].validate(trimmedValue)) {
        const messages = {
          email: "Masukkan email atau username yang valid",
          password: `Password harus minimal ${CONFIG.MIN_PASSWORD_LENGTH} karakter`,
        };
        this.showAlert(messages[type], "error");
      }
    }

    async handleLogin(e) {
      e.preventDefault();

      const formData = {
        email: $("#email").val().trim(),
        password: $("#password").val().trim(),
        remember: $("#remember_me").is(":checked"),
      };

      if (!this.validateLoginForm(formData)) return;

      this.setLoading(true);

      try {
        const response = await this.sendLoginRequest(formData);
        if (response.success) {
          this.loginData = response.data;
          this.saveAuthToken(response.data.token, formData.remember);
          this.openModal();
          $("#gauthCode").val("").focus();
        } else {
          this.showAlert(response.message || "Login gagal", "error");
        }
      } catch (error) {
        this.handleError(error);
      } finally {
        this.setLoading(false);
      }
    }

    validateLoginForm(data) {
      if (!data.email || !data.password) {
        this.showAlert("Mohon isi semua field", "error");
        return false;
      }

      if (!VALIDATORS.email.validate(data.email)) {
        this.showAlert("Format email atau username tidak valid", "error");
        return false;
      }

      if (!VALIDATORS.password.validate(data.password)) {
        this.showAlert("Password minimal 6 karakter", "error");
        return false;
      }

      return true;
    }

    handleCodeInput() {
      this.value = this.value.replace(/[^0-9]/g, "");
    }

    async handleVerification() {
      const code = $("#gauthCode").val().trim();

      if (code.length !== CONFIG.GAUTH_CODE_LENGTH) {
        this.showAlert("Masukkan kode 6 digit yang valid", "error");
        return;
      }

      try {
        const response = await this.verifyCode(code);
        if (response.success) {
          this.closeModal();
          this.showAlert("Verifikasi berhasil! Mengalihkan...", "success");
          sessionStorage.setItem("verified2FA", "true");

          setTimeout(() => {
            window.location.href = "dashboard.php";
          }, 1500);
        } else {
          this.showAlert(response.message || "Kode verifikasi salah", "error");
        }
      } catch (error) {
        this.handleError(error);
      }
    }

    async sendLoginRequest(data) {
      return $.ajax({
        url: `${CONFIG.API_URL}backend/auth.php`,
        type: "POST",
        dataType: "json",
        contentType: "application/json",
        timeout: CONFIG.TIMEOUT_DURATION,
        data: JSON.stringify(data),
      });
    }

    async verifyCode(code) {
      return $.ajax({
        url: `${CONFIG.API_URL}backend/verify_gauth.php`,
        type: "POST",
        dataType: "json",
        contentType: "application/json",
        timeout: CONFIG.TIMEOUT_DURATION,
        data: JSON.stringify({
          code: code,
          token: this.loginData.token,
        }),
      });
    }

    handleError(error) {
      const errorMessage =
        error.status === "timeout"
          ? "Koneksi timeout. Silakan coba lagi."
          : this.parseErrorResponse(error);

      this.showAlert(errorMessage, "error");
      console.error("Error:", error);
    }

    parseErrorResponse(error) {
      try {
        const response = JSON.parse(error.responseText);
        return response.message || "Terjadi kesalahan. Silakan coba lagi.";
      } catch (e) {
        console.error("Error parsing response:", e);
        return "Terjadi kesalahan. Silakan coba lagi.";
      }
    }

    saveAuthToken(token, remember) {
      const storage = remember ? localStorage : sessionStorage;
      storage.setItem("authToken", token);
    }

    showAlert(message, type) {
      $(".error-message, .success-message").remove();
      const alertClass =
        type === "success" ? "success-message" : "error-message";
      const alert = $(`<div class="${alertClass}">${message}</div>`);
      $("#loginForm").after(alert);
      setTimeout(
        () => alert.fadeOut(() => alert.remove()),
        CONFIG.ALERT_DURATION
      );
    }

    setLoading(isLoading) {
      const $submitBtn = $('button[type="submit"]');
      const $inputs = $("#loginForm input, #loginForm button");
      const $socialButtons = $(".grid button");

      if (isLoading) {
        $submitBtn.html(`
          <span class="inline-flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Sedang Masuk...
          </span>
        `);
      } else {
        $submitBtn.html(`
          <span class="absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
            </svg>
          </span>
          Sign In
        `);
      }

      $inputs.prop("disabled", isLoading);
      $socialButtons.prop("disabled", isLoading);
    }

    openModal() {
      this.modal.removeClass("hidden");
    }

    closeModal() {
      this.modal.addClass("hidden");
      $("#gauthCode").val("");
    }
  }

  // Initialize the login system
  new LoginSystem();
});
