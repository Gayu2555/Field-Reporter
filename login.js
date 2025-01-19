$(document).ready(function () {
  const CONFIG = {
    API_URL: "http://localhost/Urbansiana%20-%20Reporter/",
    TIMEOUT_DURATION: 10000,
    MIN_PASSWORD_LENGTH: 6,
    GAUTH_CODE_LENGTH: 6,
    ALERT_DURATION: 3000,
  };

  const VALIDATORS = {
    email: {
      regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
      validate: function (value) {
        return this.regex.test(value);
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
      this.isVerifying = false;
      this.bindEvents();
    }

    bindEvents() {
      $("#loginForm").on("submit", (e) => this.handleLogin(e));
      $("#email").on("blur", (e) =>
        this.validateField("email", e.target.value)
      );
      $("#password").on("blur", (e) =>
        this.validateField("password", e.target.value)
      );

      $("#gauthCode").off("input").on("input", this.handleCodeInput);
      $("#submitGauth")
        .off("click")
        .on("click", () => this.handleVerification());

      // Perbaikan handler tombol batal
      $("#cancelGauth")
        .off("click")
        .on("click", (e) => {
          e.preventDefault();
          this.handleCancel();
        });

      // Perbaikan handler click overlay
      this.modal.off("click").on("click", (e) => {
        if ($(e.target).is(this.modal)) {
          this.handleCancel();
        }
      });

      // Perbaikan handler tombol Escape
      $(document)
        .off("keydown")
        .on("keydown", (e) => {
          if (e.key === "Escape" && !this.modal.hasClass("hidden")) {
            this.handleCancel();
          }
        });
    }

    validateField(type, value) {
      const trimmedValue = value.trim();
      if (trimmedValue && !VALIDATORS[type].validate(trimmedValue)) {
        const messages = {
          email: "Masukkan email yang valid",
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
          // Verifikasi status 2FA sebelum membuka modal
          if (response.data.requires2FA) {
            this.loginData = response.data;
            this.saveAuthToken(response.data.token, formData.remember);
            this.openModal();
            $("#gauthCode").val("").focus();
          } else {
            // Redirect langsung jika tidak memerlukan 2FA
            window.location.href = "dashboard.php";
          }
        } else {
          this.showAlert(response.message || "Login gagal", "error");
        }
      } catch (error) {
        this.handleError(error);
      } finally {
        this.setLoading(false);
      }
    }

    // Fungsi baru untuk menangani pembatalan
    handleCancel() {
      if (this.isVerifying) return;

      // Hapus token yang tersimpan
      localStorage.removeItem("authToken");
      sessionStorage.removeItem("authToken");

      // Reset form login
      $("#loginForm")[0].reset();

      // Tutup modal dengan proper cleanup
      this.closeModalWithCleanup();
    }

    // Fungsi yang diperbaiki untuk menutup modal
    closeModalWithCleanup() {
      this.modal.removeClass("flex").addClass("hidden");
      $("body").removeClass("overflow-hidden");

      // Clear input dan error messages
      $("#gauthCode").val("");
      $(".modal-error, .modal-success").remove();

      // Reset state
      this.loginData = null;
      this.isVerifying = false;

      // Enable semua input
      $("#gauthCode, #submitGauth, #cancelGauth").prop("disabled", false);
    }

    validateLoginForm(data) {
      if (!data.email || !data.password) {
        this.showAlert("Mohon isi semua field", "error");
        return false;
      }

      if (!VALIDATORS.email.validate(data.email)) {
        this.showAlert("Format email tidak valid", "error");
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
      if (this.value.length === CONFIG.GAUTH_CODE_LENGTH) {
        $("#submitGauth").focus();
      }
    }

    async handleVerification() {
      if (this.isVerifying) return;

      const code = $("#gauthCode").val().trim();
      if (code.length !== CONFIG.GAUTH_CODE_LENGTH) {
        this.showVerificationAlert("Masukkan kode 6 digit yang valid", "error");
        return;
      }

      this.setVerificationLoading(true);

      try {
        const response = await this.verifyCode(code);
        if (response.success) {
          this.showVerificationAlert(
            "Verifikasi berhasil! Mengalihkan...",
            "success"
          );
          sessionStorage.setItem("verified2FA", "true");

          setTimeout(() => {
            this.closeModalWithCleanup();
            window.location.href = "dashboard.php";
          }, 1500);
        } else {
          this.showVerificationAlert(
            response.message || "Kode verifikasi salah",
            "error"
          );
        }
      } catch (error) {
        this.handleVerificationError(error);
      } finally {
        this.setVerificationLoading(false);
      }
    }

    showVerificationAlert(message, type) {
      $(".modal-error, .modal-success").remove();
      const alertClass = type === "success" ? "modal-success" : "modal-error";
      const alert = $(
        `<div class="${alertClass} text-sm mt-2 ${
          type === "success" ? "text-green-600" : "text-red-600"
        }">${message}</div>`
      );
      $("#gauthCode").parent().after(alert);

      if (type === "error") {
        setTimeout(
          () => alert.fadeOut(() => alert.remove()),
          CONFIG.ALERT_DURATION
        );
      }
    }

    setVerificationLoading(isLoading) {
      this.isVerifying = isLoading;
      $("#submitGauth, #cancelGauth, #gauthCode").prop("disabled", isLoading);

      // Update tombol verifikasi
      const $submitBtn = $("#submitGauth");
      $submitBtn.html(isLoading ? "Memverifikasi..." : "Verifikasi");
      if (isLoading) {
        $submitBtn.addClass("opacity-75 cursor-wait");
      } else {
        $submitBtn.removeClass("opacity-75 cursor-wait");
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
          code,
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

    handleVerificationError(error) {
      const errorMessage =
        error.status === "timeout"
          ? "Koneksi timeout. Silakan coba lagi."
          : this.parseErrorResponse(error);

      this.showVerificationAlert(errorMessage, "error");
      console.error("Verification Error:", error);
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

      $inputs.prop("disabled", isLoading);
      $submitBtn.html(
        isLoading
          ? '<span class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Loading...</span>'
          : '<span class="absolute inset-y-0 left-0 flex items-center pl-3"><svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg></span>Sign In'
      );
    }

    openModal() {
      console.log("Opening modal...");
      this.modal.removeClass("hidden").addClass("flex");
      $("body").addClass("overflow-hidden");
      $("#gauthCode").focus();
    }
  }

  new LoginSystem();
});
