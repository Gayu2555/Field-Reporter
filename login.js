$(document).ready(function () {
  // Konstanta
  const API_URL = "http://localhost/Urbansiana%20-%20Reporter/";
  const TIMEOUT_DURATION = 10000; // 10 detik

  // Fungsi untuk menampilkan pesan alert
  function showAlert(message, type) {
    $(".error-message, .success-message").remove();
    const alertClass = type === "success" ? "success-message" : "error-message";
    const alert = $(`<div class="${alertClass}">${message}</div>`);
    $("#loginForm").after(alert);
    setTimeout(() => alert.fadeOut(() => alert.remove()), 3000);
  }

  // Fungsi untuk mengatur status loading
  function setLoading(loading) {
    const $submitBtn = $('button[type="submit"]');
    const $inputs = $("#loginForm input, #loginForm button");

    if (loading) {
      $submitBtn.html(`
        <span class="inline-flex items-center">
          <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Sedang Masuk...
        </span>
      `);
      $inputs.prop("disabled", true);
    } else {
      $submitBtn.html(`
        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
          <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
          </svg>
        </span>
        Masuk
      `);
      $inputs.prop("disabled", false);
    }
  }

  // Fungsi untuk menyimpan token autentikasi
  function saveAuthToken(token, remember) {
    if (remember) {
      localStorage.setItem("authToken", token);
    } else {
      sessionStorage.setItem("authToken", token);
    }
  }

  // Fungsi untuk validasi email/username
  function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
    return emailRegex.test(email) || usernameRegex.test(email);
  }

  // Fungsi untuk validasi password
  function validatePassword(password) {
    return password.length >= 6;
  }

  // Fungsi untuk menampilkan modal Google Authenticator
  function showGoogleAuthModal() {
    const modal = `
      <div id="gauthModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-8 max-w-md w-full mx-4">
          <h2 class="text-2xl font-bold mb-4 dark:text-white">Verifikasi Dua Faktor</h2>
          <p class="text-gray-600 dark:text-gray-300 mb-4">Masukkan kode dari Google Authenticator Anda</p>
          <div class="space-y-4">
            <input type="text" id="gauthCode" 
              class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
              placeholder="Masukkan kode 6 digit"
              maxlength="6"
              pattern="[0-9]*"
              autocomplete="off">
            <div class="flex justify-end space-x-3">
              <button id="cancelGauth" class="px-4 py-2 text-gray-600 hover:text-gray-800 dark:text-gray-300">
                Batal
              </button>
              <button id="submitGauth" 
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Verifikasi
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
    $("body").append(modal);

    // Hanya terima input angka
    $("#gauthCode").on("input", function () {
      this.value = this.value.replace(/[^0-9]/g, "");
    });

    // Handler untuk tombol batal
    $("#cancelGauth").on("click", function () {
      $("#gauthModal").remove();
    });
  }

  // Fungsi untuk verifikasi kode Google Authenticator
  function verifyGoogleAuthCode(code, loginData) {
    $.ajax({
      url: API_URL + "backend/verify_gauth.php",
      type: "POST",
      dataType: "json",
      contentType: "application/json",
      timeout: TIMEOUT_DURATION,
      data: JSON.stringify({
        code: code,
        token: loginData.token,
      }),
      success: function (response) {
        if (response.success) {
          $("#gauthModal").remove();
          showAlert("Verifikasi berhasil! Mengalihkan...", "success");
          sessionStorage.setItem("verified2FA", "true");

          setTimeout(function () {
            window.location.href = "dashboard.php";
          }, 1500);
        } else {
          showAlert(response.message || "Kode verifikasi salah", "error");
        }
      },
      error: function (xhr, status, error) {
        let errorMessage = "Terjadi kesalahan saat verifikasi";

        if (status === "timeout") {
          errorMessage = "Koneksi timeout. Silakan coba lagi.";
        } else {
          try {
            const response = JSON.parse(xhr.responseText);
            if (response.message) {
              errorMessage = response.message;
            }
          } catch (e) {
            console.error("Error parsing response:", e);
          }
        }

        showAlert(errorMessage, "error");
      },
    });
  }

  // Handler untuk form login
  $("#loginForm").on("submit", function (e) {
    e.preventDefault();

    const email = $("#email").val().trim();
    const password = $("#password").val().trim();
    const remember = $("#remember_me").is(":checked");

    // Validasi input
    if (!email || !password) {
      showAlert("Mohon isi semua field", "error");
      return;
    }

    if (!validateEmail(email)) {
      showAlert("Format email atau username tidak valid", "error");
      return;
    }

    if (!validatePassword(password)) {
      showAlert("Password minimal 6 karakter", "error");
      return;
    }

    setLoading(true);

    // Kirim permintaan login
    $.ajax({
      url: API_URL + "backend/auth.php",
      type: "POST",
      dataType: "json",
      contentType: "application/json",
      timeout: TIMEOUT_DURATION,
      data: JSON.stringify({
        email: email,
        password: password,
        remember: remember,
      }),
      success: function (response) {
        if (response.success) {
          const loginData = response.data;
          saveAuthToken(loginData.token, remember);
          showGoogleAuthModal();

          // Handle submit kode Google Authenticator
          $("#submitGauth").on("click", function () {
            const code = $("#gauthCode").val().trim();
            if (code.length === 6) {
              verifyGoogleAuthCode(code, loginData);
            } else {
              showAlert("Masukkan kode 6 digit yang valid", "error");
            }
          });

          // Handle input enter pada kode
          $("#gauthCode").on("keypress", function (e) {
            if (e.which === 13) {
              const code = $(this).val().trim();
              if (code.length === 6) {
                verifyGoogleAuthCode(code, loginData);
              } else {
                showAlert("Masukkan kode 6 digit yang valid", "error");
              }
            }
          });
        } else {
          showAlert(response.message || "Login gagal", "error");
        }
      },
      error: function (xhr, status, error) {
        let errorMessage = "Terjadi kesalahan. Silakan coba lagi.";

        if (status === "timeout") {
          errorMessage = "Koneksi timeout. Silakan coba lagi.";
        } else {
          try {
            const response = JSON.parse(xhr.responseText);
            if (response.message) {
              errorMessage = response.message;
            }
          } catch (e) {
            console.error("Error parsing response:", e);
          }
        }

        showAlert(errorMessage, "error");
        console.error("Login error:", error);
      },
      complete: function () {
        setLoading(false);
      },
    });
  });

  // Validasi input saat blur
  $("#email").on("blur", function () {
    const email = $(this).val().trim();
    if (email && !validateEmail(email)) {
      showAlert("Masukkan email atau username yang valid", "error");
    }
  });

  $("#password").on("blur", function () {
    const password = $(this).val().trim();
    if (password && !validatePassword(password)) {
      showAlert("Password harus minimal 6 karakter", "error");
    }
  });
});
