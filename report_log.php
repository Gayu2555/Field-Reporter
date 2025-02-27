<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Logs - URBANSIANA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/luxon/3.4.4/luxon.min.js"></script>
</head>

<body class="bg-gradient-to-br from-indigo-50 to-indigo-100 min-h-screen">
    <!-- Navbar - sama seperti index.html -->
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
            <div class="hidden md:flex items-center space-x-4">
                <a href="dashboard.php" class="text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg transition-colors">
                    Home
                </a>
                <a href="report_log.php" class="text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg transition-colors">
                    Report Logs
                </a>
                <a href="logout.php" class="text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition-colors">
                    Logout
                </a>
            </div>


            <div class="md:hidden flex items-center">
                <button id="mobileMenuBtn" class="text-gray-700 hover:text-indigo-600 p-2 rounded-lg hover:bg-indigo-50">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div id="mobileMenu" class="hidden md:hidden py-2 absolute w-full left-0 bg-white/95 backdrop-blur-md border-b border-indigo-100">
            <div class="space-y-1 px-4">
                <a href="dashboard.php" class="block text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg transition-colors">
                    Home
                </a>
                <a href="report_log.php" class="block text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg transition-colors">
                    Report Logs
                </a>
                <div class="pt-2 border-t border-gray-200">
                    <a href="logout.php" class="block text-red-600 hover:text-red-700 hover:bg-red-50 px-4 py-2 rounded-lg transition-colors">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Riwayat Laporan</h1>
            <p class="mt-2 text-gray-600">Daftar semua laporan yang telah dikirim</p>
        </div>


        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" id="startDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                    <input type="date" id="endDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cari</label>
                    <input type="text" id="searchInput" placeholder="Cari berdasarkan kata kunci..."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex items-end">
                    <button id="filterBtn" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </div>


        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siapa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dimana</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="reportsTableBody" class="bg-white divide-y divide-gray-200">

                    </tbody>
                </table>
            </div>


            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Menampilkan <span id="startCount">1</span> - <span id="endCount">10</span> dari
                        <span id="totalCount">20</span> data
                    </div>
                    <div class="flex space-x-2">
                        <button id="prevPage" class="px-3 py-1 border rounded-md hover:bg-gray-50">
                            Previous
                        </button>
                        <button id="nextPage" class="px-3 py-1 border rounded-md hover:bg-gray-50">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="reportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Detail Laporan</h3>
                <button id="closeModal" class="text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="modalContent" class="space-y-4">

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentPage = 1;
            const itemsPerPage = 10;


            loadReports();


            document.getElementById('filterBtn').addEventListener('click', function() {
                currentPage = 1;
                loadReports();
            });


            document.getElementById('prevPage').addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    loadReports();
                }
            });

            document.getElementById('nextPage').addEventListener('click', function() {
                currentPage++;
                loadReports();
            });

            function loadReports() {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const searchQuery = document.getElementById('searchInput').value;

                // Fetch data from backend
                fetch(`backend/get_reports.php?page=${currentPage}&limit=${itemsPerPage}&start=${startDate}&end=${endDate}&search=${searchQuery}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            renderReports(data.reports || []);
                            updatePagination(data.total);
                        } else {
                            console.error('Error:', data.message);
                            renderReports([]);
                            updatePagination(0);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        renderReports([]);
                        updatePagination(0);
                    });
            }

            function renderReports(reports) {
                const tbody = document.getElementById('reportsTableBody');
                tbody.innerHTML = '';

                reports.forEach(report => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${formatDate(report.created_at)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${report.who}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${report.what}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${report.where}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <button onclick="showReportDetail(${report.id})" 
                                    class="text-indigo-600 hover:text-indigo-900">
                                Detail
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }

            function updatePagination(total) {
                const startCount = ((currentPage - 1) * itemsPerPage) + 1;
                const endCount = Math.min(currentPage * itemsPerPage, total);

                document.getElementById('startCount').textContent = startCount;
                document.getElementById('endCount').textContent = endCount;
                document.getElementById('totalCount').textContent = total;

                document.getElementById('prevPage').disabled = currentPage === 1;
                document.getElementById('nextPage').disabled = endCount >= total;
            }

            function formatDate(dateString) {
                return new Date(dateString).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        });

        function showReportDetail(reportId) {
            fetch(`backend/get-report-detail.php?id=${reportId}`)
                .then(response => response.json())
                .then(report => {
                    const modalContent = document.getElementById('modalContent');
                    modalContent.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-semibold">Siapa</h4>
                                <p>${report.who}</p>
                            </div>
                            <div>
                                <h4 class="font-semibold">Apa</h4>
                                <p>${report.what}</p>
                            </div>
                            <div>
                                <h4 class="font-semibold">Dimana</h4>
                                <p>${report.where}</p>
                            </div>
                            <div>
                                <h4 class="font-semibold">Kapan</h4>
                                <p>${formatDate(report.when)}</p>
                            </div>
                            <div class="col-span-2">
                                <h4 class="font-semibold">Mengapa</h4>
                                <p>${report.why}</p>
                            </div>
                            <div class="col-span-2">
                                <h4 class="font-semibold">Bagaimana</h4>
                                <p>${report.how}</p>
                            </div>
                            <div class="col-span-2">
                                <h4 class="font-semibold">Detail Tambahan</h4>
                                <p>${report.details || '-'}</p>
                            </div>
                            <div class="col-span-2">
                                <h4 class="font-semibold">Foto</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
                                    ${report.photos.map(photo => `
                                        <img src="uploads/${photo}" 
                                             alt="Report photo" 
                                             class="w-full h-32 object-cover rounded-lg">
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    `;
                    document.getElementById('reportModal').classList.remove('hidden');
                })
                .catch(error => console.error('Error:', error));
        }

        // Modal close handler
        document.getElementById('closeModal').addEventListener('click', function() {
            document.getElementById('reportModal').classList.add('hidden');
        });
    </script>
    <script src="dashboard.js"></script>
</body>

</html>