$(document).ready(function () {
  // Constants
  const API_URL = "http://localhost/Urbansiana%20-%20Reporter/";

  // Show alert message function
  function showAlert(message, type) {
    // Remove any existing alerts
    $(".error-message, .success-message").remove();

    // Create new alert
    const alertClass = type === "success" ? "success-message" : "error-message";
    const alert = $(`<div class="${alertClass}">${message}</div>`);

    // Insert after form
    $("#loginForm").after(alert);

    // Auto hide after 3 seconds
    setTimeout(() => alert.fadeOut(() => alert.remove()), 3000);
  }

  // Set loading state
  function setLoading(loading) {
    const $submitBtn = $('button[type="submit"]');
    const $inputs = $("#loginForm input, #loginForm button");

    if (loading) {
      $submitBtn.prop("disabled", true);
      $inputs.prop("disabled", true);
    } else {
      $submitBtn.prop("disabled", false);
      $inputs.prop("disabled", false);
    }
  }

  // Save auth token
  function saveAuthToken(token, remember) {
    if (remember) {
      localStorage.setItem("authToken", token);
    } else {
      sessionStorage.setItem("authToken", token);
    }
  }

  // Login form submission
  $("#loginForm").on("submit", function (e) {
    e.preventDefault();

    const email = $("#email").val().trim();
    const password = $("#password").val().trim();
    const remember = $("#remember_me").is(":checked");

    // Basic validation
    if (!email || !password) {
      showAlert("Please fill in all fields", "error");
      return;
    }

    setLoading(true);

    // Send login request
    $.ajax({
      url: API_URL + "backend/auth.php",
      type: "POST",
      dataType: "json",
      contentType: "application/json",
      data: JSON.stringify({
        email: email,
        password: password,
        remember: remember,
      }),
      success: function (response) {
        if (response.success) {
          showAlert("Login successful! Redirecting...", "success");
          saveAuthToken(response.data.token, remember);

          // Redirect after successful login
          setTimeout(function () {
            window.location.href = "dashboard.php";
          }, 1500);
        } else {
          showAlert(response.message || "Login failed", "error");
        }
      },
      error: function (xhr, status, error) {
        let errorMessage = "An error occurred. Please try again.";
        try {
          const response = JSON.parse(xhr.responseText);
          if (response.message) {
            errorMessage = response.message;
          }
        } catch (e) {
          console.error("Error parsing error response:", e);
        }
        showAlert(errorMessage, "error");
        console.error("Login error:", error);
      },
      complete: function () {
        setLoading(false);
      },
    });
  });
});
