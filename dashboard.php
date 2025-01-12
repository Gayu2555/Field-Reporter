<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Reporter Lapangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/luxon/3.4.4/luxon.min.js"></script>
    <!-- Toast notification library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.12.0/toastify.min.css">
</head>

<body class="bg-gradient-to-br from-indigo-50 to-indigo-100 min-h-screen">
    <nav class="bg-white/80 backdrop-blur-md border-b border-indigo-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <span class="text-white text-xl font-bold">R</span>
                    </div>
                    <span class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-indigo-800 bg-clip-text text-transparent">
                        URBANSIANA | REPORTER SYSTEM
                    </span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="index.html" class="text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg transition-colors">
                        Home
                    </a>
                    <a href="report-logs.html" class="text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg transition-colors">
                        Report Logs
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobileMenuBtn" class="text-gray-700 hover:text-indigo-600 p-2 rounded-lg hover:bg-indigo-50">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="hidden md:hidden py-2">
                <div class="space-y-1">
                    <a href="index.html" class="block text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg transition-colors">
                        Home
                    </a>
                    <a href="report-logs.html" class="block text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg transition-colors">
                        Report Logs
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto py-8 px-4">
        <form id="reportForm" class="bg-white/80 backdrop-blur-md p-8 rounded-xl shadow-lg border border-indigo-100 space-y-6">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-indigo-800 bg-clip-text text-transparent">
                    Form Reportase Lapangan
                </h2>
                <p class="text-gray-600 mt-2">Lengkapi informasi laporan dengan detail</p>
            </div>

            <!-- Upload Photos Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-semibold text-gray-700">Unggah Foto</label>
                    <span class="text-xs text-gray-500" id="photoCount">0/4 foto</span>
                </div>
                <div class="flex justify-center items-center w-full">
                    <label for="photos" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-indigo-300 rounded-lg cursor-pointer bg-indigo-50 hover:bg-indigo-100 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="text-sm text-gray-600">Klik atau seret foto ke sini</p>
                            <p class="text-xs text-gray-500">PNG, JPG (Maks. 4 foto)</p>
                        </div>
                        <input type="file" id="photos" name="photos[]" accept="image/*" multiple class="hidden">
                    </label>
                </div>
                <div id="preview" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>
            </div>

            <!-- 5W + 1H Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="who" class="block text-sm font-semibold text-gray-700">Siapa</label>
                    <input type="text" id="who" name="who" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Nama atau identitas terkait">
                </div>
                <div class="space-y-2">
                    <label for="what" class="block text-sm font-semibold text-gray-700">Apa</label>
                    <input type="text" id="what" name="what" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Deskripsi kejadian">
                </div>
                <div class="space-y-2">
                    <label for="where" class="block text-sm font-semibold text-gray-700">Dimana</label>
                    <input type="text" id="where" name="where" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Lokasi kejadian">
                </div>
                <div class="space-y-2">
                    <label for="when" class="block text-sm font-semibold text-gray-700">Kapan</label>
                    <input type="datetime-local" id="when" name="when" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>

            <div class="space-y-2">
                <label for="why" class="block text-sm font-semibold text-gray-700">Mengapa</label>
                <textarea id="why" name="why" required rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Alasan atau penyebab kejadian"></textarea>
            </div>

            <div class="space-y-2">
                <label for="how" class="block text-sm font-semibold text-gray-700">Bagaimana</label>
                <textarea id="how" name="how" required rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Proses atau cara terjadinya"></textarea>
            </div>

            <div class="space-y-2">
                <label for="details" class="block text-sm font-semibold text-gray-700">Detail Tambahan</label>
                <textarea id="details" name="details" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Informasi tambahan yang relevan"></textarea>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" id="resetBtn"
                    class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Reset
                </button>
                <button type="submit" id="submitBtn"
                    class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                    <span>Kirim Laporan</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('reportForm');
            const photoInput = document.getElementById('photos');
            const previewContainer = document.getElementById('preview');
            const photoCountElement = document.getElementById('photoCount');
            const resetBtn = document.getElementById('resetBtn');
            const MAX_PHOTOS = 4;
            const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
            let currentPhotos = [];

            // Set default datetime-local to current time
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('when').value = now.toISOString().slice(0, 16);

            // Photo handling
            photoInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);

                // Validate number of files
                if (currentPhotos.length + files.length > MAX_PHOTOS) {
                    showToast('Maksimal 4 foto yang diperbolehkan', 'error');
                    return;
                }

                // Process each file
                files.forEach(file => {
                    // Validate file size
                    if (file.size > MAX_FILE_SIZE) {
                        showToast(`File ${file.name} terlalu besar. Maksimal 5MB`, 'error');
                        return;
                    }

                    // Validate file type
                    if (!file.type.startsWith('image/')) {
                        showToast(`File ${file.name} bukan format gambar yang valid`, 'error');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const photoDiv = document.createElement('div');
                        photoDiv.className = 'relative';

                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.className = 'w-full h-32 object-cover rounded-lg';

                        const deleteBtn = document.createElement('button');
                        deleteBtn.className = 'absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600';
                        deleteBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                        deleteBtn.onclick = function() {
                            photoDiv.remove();
                            currentPhotos = currentPhotos.filter(p => p !== file);
                            updatePhotoCount();
                        };

                        photoDiv.appendChild(img);
                        photoDiv.appendChild(deleteBtn);
                        previewContainer.appendChild(photoDiv);
                        currentPhotos.push(file);
                        updatePhotoCount();
                    };
                    reader.readAsDataURL(file);
                });
            });

            function updatePhotoCount() {
                photoCountElement.textContent = `${currentPhotos.length}/${MAX_PHOTOS} foto`;
            }

            function showToast(message, type = 'info') {
                Toastify({
                    text: message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: type === 'error' ? "#EF4444" : "#10B981",
                }).showToast();
            }

            // Form submission
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                // Validate required fields
                const requiredFields = ['who', 'what', 'where', 'when', 'why', 'how'];
                let isValid = true;
                requiredFields.forEach(field => {
                    const element = document.getElementById(field);
                    if (!element.value.trim()) {
                        showToast(`Field ${field} harus diisi`, 'error');
                        element.classList.add('border-red-500');
                        isValid = false;
                    } else {
                        element.classList.remove('border-red-500');
                    }
                });

                if (!isValid) return;

                try {
                    const formData = new FormData(form);

                    // Add photos to FormData
                    formData.delete('photos[]'); // Remove the empty file input
                    currentPhotos.forEach(photo => {
                        formData.append('photos[]', photo);
                    });

                    const response = await fetch('backend/report.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const result = await response.text();
                    showToast('Laporan berhasil dikirim!');

                    // Optional: Reset form after successful submission
                    if (confirm('Laporan berhasil dikirim. Buat laporan baru?')) {
                        resetForm();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat mengirim laporan', 'error');
                }
            });

            // Reset form
            resetBtn.addEventListener('click', () => {
                if (confirm('Anda yakin ingin mereset form?')) {
                    resetForm();
                }
            });

            function resetForm() {
                form.reset();
                previewContainer.innerHTML = '';
                currentPhotos = [];
                updatePhotoCount();

                // Reset datetime-local to current time
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                document.getElementById('when').value = now.toISOString().slice(0, 16);
            }
        });
    </script>
</body>

</html>