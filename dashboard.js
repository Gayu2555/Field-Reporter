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
  const form = document.getElementById("reportForm");
  const photoInput = document.getElementById("photos");
  const previewContainer = document.getElementById("preview");
  const photoCountDisplay = document.getElementById("photoCount");
  const resetBtn = document.getElementById("resetBtn");
  const submitBtn = document.getElementById("submitBtn");

  let uploadedFiles = [];
  const MAX_FILES = 4;
  const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

  const whenInput = document.getElementById("when");
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  whenInput.value = now.toISOString().slice(0, 16);

  photoInput.addEventListener("change", function (e) {
    const files = Array.from(e.target.files);
    handleFileSelection(files);
  });

  const dropZone = document.querySelector('label[for="photos"]');

  ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
    dropZone.addEventListener(eventName, preventDefaults, false);
  });

  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  ["dragenter", "dragover"].forEach((eventName) => {
    dropZone.addEventListener(eventName, highlight, false);
  });

  ["dragleave", "drop"].forEach((eventName) => {
    dropZone.addEventListener(eventName, unhighlight, false);
  });

  function highlight(e) {
    dropZone.classList.add("bg-indigo-200");
  }

  function unhighlight(e) {
    dropZone.classList.remove("bg-indigo-200");
  }

  dropZone.addEventListener("drop", handleDrop, false);

  function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = Array.from(dt.files);
    handleFileSelection(files);
  }

  function handleFileSelection(files) {
    const validFiles = files.filter((file) => {
      if (!file.type.startsWith("image/")) {
        showToast("Error: Hanya file gambar yang diperbolehkan", "error");
        return false;
      }

      if (file.size > MAX_FILE_SIZE) {
        showToast("Error: Ukuran file tidak boleh melebihi 5MB", "error");
        return false;
      }

      return true;
    });

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
  }

  function updatePhotoCount() {
    photoCountDisplay.textContent = `${uploadedFiles.length}/${MAX_FILES} foto`;
  }

  function displayPreviews() {
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
  }

  resetBtn.addEventListener("click", function () {
    if (confirm("Apakah Anda yakin ingin mereset form?")) {
      form.reset();
      uploadedFiles = [];
      updatePhotoCount();
      displayPreviews();
      whenInput.value = now.toISOString().slice(0, 16);
    }
  });

  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const requiredFields = ["who", "what", "where", "when", "why", "how"];
    let isValid = true;

    requiredFields.forEach((field) => {
      const input = document.getElementById(field);
      if (!input.value.trim()) {
        input.classList.add("border-red-500");
        isValid = false;
      } else {
        input.classList.remove("border-red-500");
      }
    });

    if (!isValid) {
      showToast("Error: Mohon lengkapi semua field yang wajib diisi", "error");
      return;
    }

    // Validate photos
    if (uploadedFiles.length === 0) {
      showToast("Error: Mohon unggah minimal 1 foto", "error");
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Mengirim...
        `;

    try {
      const formData = new FormData(form);
      uploadedFiles.forEach((file) => {
        formData.append("photos[]", file);
      });

      //Handling Request to Backend
      const response = await fetch("backend/proces.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        showToast("Laporan berhasil dikirim!", "success");
        form.reset();
        uploadedFiles = [];
        updatePhotoCount();
        displayPreviews();
        whenInput.value = new Date().toISOString().slice(0, 16);
      } else {
        throw new Error(result.message || "Terjadi kesalahan");
      }
    } catch (error) {
      showToast(`Error: ${error.message}`, "error");
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = `
                <span>Kirim Laporan</span>
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            `;
    }
  });

  // Toast notification function
  function showToast(message, type = "success") {
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
