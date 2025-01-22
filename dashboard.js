const mobileMenuBtn = document.getElementById("mobileMenuBtn");
const mobileMenu = document.getElementById("mobileMenu");

mobileMenuBtn.addEventListener("click", () => {
  const isHidden = mobileMenu.classList.contains("hidden");
  if (isHidden) {
    mobileMenu.classList.remove("hidden");
    mobileMenu.classList.add("animate-fade-in");
  } else {
    mobileMenu.classList.add("hidden");
    mobileMenu.classList.remove("animate-fade-in");
  }
});

document.addEventListener("click", (event) => {
  const isClickInsideMenu = mobileMenu.contains(event.target);
  const isClickOnButton = mobileMenuBtn.contains(event.target);

  if (
    !isClickInsideMenu &&
    !isClickOnButton &&
    !mobileMenu.classList.contains("hidden")
  ) {
    mobileMenu.classList.add("hidden");
    mobileMenu.classList.remove("animate-fade-in");
  }
});

document.addEventListener("DOMContentLoaded", function () {
  // Check if required elements exist
  const requiredElements = [
    "reportForm",
    "photos",
    "preview",
    "photoCount",
    "resetBtn",
    "submitBtn",
    "when",
  ];

  const elements = {};
  for (const elementId of requiredElements) {
    const element = document.getElementById(elementId);
    if (!element) {
      console.error(`Required element #${elementId} not found`);
      return;
    }
    elements[elementId] = element;
  }

  const {
    reportForm: form,
    photos: photoInput,
    preview: previewContainer,
    photoCount: photoCountDisplay,
    resetBtn,
    submitBtn,
    when: whenInput,
  } = elements;

  let uploadedFiles = [];
  const MAX_FILES = 4;
  const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

  // Set initial datetime
  const updateDateTime = () => {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    whenInput.value = now.toISOString().slice(0, 16);
  };
  updateDateTime();

  // File handling functions
  const isValidImageFile = (file) => {
    if (!file.type.startsWith("image/")) {
      showToast("Error: Hanya file gambar yang diperbolehkan", "error");
      return false;
    }

    if (file.size > MAX_FILE_SIZE) {
      showToast("Error: Ukuran file tidak boleh melebihi 5MB", "error");
      return false;
    }

    return true;
  };

  const handleFileSelection = (files) => {
    const validFiles = files.filter(isValidImageFile);

    if (uploadedFiles.length + validFiles.length > MAX_FILES) {
      showToast(
        `Error: Maksimal ${MAX_FILES} foto yang diperbolehkan`,
        "error"
      );
      return;
    }

    uploadedFiles = [...uploadedFiles, ...validFiles];
    updatePhotoCount();
    displayPreviews();
  };

  // File input change handler
  photoInput.addEventListener("change", (e) =>
    handleFileSelection(Array.from(e.target.files))
  );

  // Drag and drop handling
  const dropZone = document.querySelector('label[for="photos"]');

  const preventDefaults = (e) => {
    e.preventDefault();
    e.stopPropagation();
  };

  const highlight = () => dropZone.classList.add("bg-indigo-200");
  const unhighlight = () => dropZone.classList.remove("bg-indigo-200");

  ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
    dropZone.addEventListener(eventName, preventDefaults, false);
  });

  ["dragenter", "dragover"].forEach((eventName) => {
    dropZone.addEventListener(eventName, highlight, false);
  });

  ["dragleave", "drop"].forEach((eventName) => {
    dropZone.addEventListener(eventName, unhighlight, false);
  });

  dropZone.addEventListener(
    "drop",
    (e) => {
      const files = Array.from(e.dataTransfer.files);
      handleFileSelection(files);
    },
    false
  );

  // Preview handling
  const updatePhotoCount = () => {
    photoCountDisplay.textContent = `${uploadedFiles.length}/${MAX_FILES} foto`;
  };

  const displayPreviews = () => {
    previewContainer.innerHTML = "";

    uploadedFiles.forEach((file, index) => {
      const reader = new FileReader();
      reader.onload = function (e) {
        const previewWrapper = document.createElement("div");
        previewWrapper.className =
          "relative aspect-square rounded-lg overflow-hidden group";

        const preview = document.createElement("img");
        preview.src = e.target.result;
        preview.className = "w-full h-full object-cover";

        const deleteButton = document.createElement("button");
        deleteButton.className =
          "absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity";
        deleteButton.innerHTML = `
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        `;
        deleteButton.onclick = function (e) {
          e.preventDefault();
          uploadedFiles.splice(index, 1);
          updatePhotoCount();
          displayPreviews();
        };

        previewWrapper.appendChild(preview);
        previewWrapper.appendChild(deleteButton);
        previewContainer.appendChild(previewWrapper);
      };
      reader.readAsDataURL(file);
    });
  };

  resetBtn.addEventListener("click", function () {
    if (confirm("Apakah Anda yakin ingin mereset form?")) {
      form.reset();
      uploadedFiles = [];
      updatePhotoCount();
      displayPreviews();
      updateDateTime();
    }
  });

  // Form validation
  const validateForm = () => {
    const requiredFields = ["who", "what", "where", "when", "why", "how"];
    let isValid = true;

    requiredFields.forEach((field) => {
      const input = document.getElementById(field);
      if (!input || !input.value.trim()) {
        if (input) input.classList.add("border-red-500");
        isValid = false;
      } else {
        input.classList.remove("border-red-500");
      }
    });

    if (!isValid) {
      showToast("Error: Mohon lengkapi semua field yang wajib diisi", "error");
      return false;
    }

    if (uploadedFiles.length === 0) {
      showToast("Error: Mohon unggah minimal 1 foto", "error");
      return false;
    }

    return true;
  };

  // Form submission handler
  const setSubmitButtonState = (isLoading) => {
    submitBtn.disabled = isLoading;
    submitBtn.innerHTML = isLoading
      ? `<svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
           <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
           <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
         </svg>
         Mengirim...`
      : `<span>Kirim Laporan</span>
         <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
         </svg>`;
  };

  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    if (!validateForm()) return;

    setSubmitButtonState(true);

    try {
      const formData = new FormData(form);
      uploadedFiles.forEach((file) => {
        formData.append("photos[]", file);
      });

      const response = await fetch("backend/proces.php", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();

      if (result.success) {
        showToast("Laporan berhasil dikirim!", "success");
        form.reset();
        uploadedFiles = [];
        updatePhotoCount();
        displayPreviews();
        updateDateTime();
      } else {
        showToast(`Error: ${result.message || "Terjadi kesalahan"}`, "error");
      }
    } catch (error) {
      console.error("Submission error:", error);
      console.error("Error message:", error.message);
      console.error("Error stack:", error.stack);
      showToast(
        `Error: ${error.message || "Terjadi kesalahan pada server"}`,
        "error"
      );
    } finally {
      setSubmitButtonState(false);
    }
  });

  // Toast notification function
  function showToast(message, type = "success") {
    if (typeof Toastify === "undefined") {
      console.error("Toastify is not loaded");
      alert(message); // Fallback to regular alert
      return;
    }

    Toastify({
      text: message,
      duration: 3000,
      gravity: "top",
      position: "right",
      style: {
        background: type === "success" ? "#10B981" : "#EF4444",
      },
    }).showToast();
  }
});
