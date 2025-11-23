<?php
// WARNING: This is only for development/testing purposes
session_start();
$_SESSION['nama'] = 'Admin'; // Temporary admin name
$_SESSION['role'] = 'admin';

// NOTE: For design/testing purposes we do NOT connect to the database here.
// Use static dummy statistics so the dashboard and charts render without DB.
$statistik = [
    'total_pendaki'   => 120,
    'pendaki_aktif'   => 45,
    'booking_pending' => 10,
    'total_booking'   => 200
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Gunung Raung</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <style>
        .logo-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            opacity: 0.2;
            pointer-events: none;
        }
        
        .logo-container img {
            width: 150px;
            height: auto;
        }
        
        .chart-card {
            position: relative;
            background: linear-gradient(to bottom right, #ffffff, #f8f9fa);
        }
        
        .chart-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(45deg, rgba(231, 76, 60, 0.03), rgba(231, 76, 60, 0.02));
            border-radius: 10px;
            z-index: 0;
        }
        
        .chart-card canvas {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="../images/Gunung_Raung.jpg" alt="Logo Gunung Raung">
    </div>
    
    <main class="dashboard-content">
        <div class="dashboard-panel">
            <div class="panel-header">
                <div class="panel-header-inner">
                    <div class="panel-logo">
                        <img src="../images/Gunung_Raung.jpg" alt="Logo Gunung Raung">
                        <div class="brand-text">
                            <span class="brand-title">Gunung Raung</span>
                            <span class="brand-sub">3332 MDPL</span>
                        </div>
                    </div>
                        <h2>Dashboard Admin</h2>
                        <!-- Summary toggle button (opens side drawer) -->
                        
                </div>
            </div>

        

        <div style="height:18px"></div>

        <div class="profile-section">
            <div class="profile-header">
                <div class="profile-avatar">
                    <span>A</span>
                </div>
                <div>
                    <h1>Dashboard Admin</h1>
                    <div class="online-status">
                        <span class="online-dot"></span>
                        <span>Online</span>
                    </div>
                </div>
            </div>
        </div>

        <section class="stats-section">
            <div class="stat-card">
            <div class="stat-top">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $statistik['total_pendaki']; ?></div>
                        <div class="stat-label">Total Pendaki</div>
                    </div>
                </div>
                <div class="stat-meta"><a href="data_pendaki.php" class="stat-link">Selengkapnya →</a></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon success"><i class="fas fa-hiking"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $statistik['pendaki_aktif']; ?></div>
                        <div class="stat-label">Pendaki Aktif</div>
                    </div>
                </div>
                <div class="stat-meta"><a href="data_pendaki.php?status=active" class="stat-link">Selengkapnya →</a></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $statistik['booking_pending']; ?></div>
                        <div class="stat-label">Menunggu Konfirmasi</div>
                    </div>
                </div>
                <div class="stat-meta"><a href="data_booking.php?status=pending" class="stat-link">Selengkapnya →</a></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon info"><i class="fas fa-calendar-check"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $statistik['total_booking']; ?></div>
                        <div class="stat-label">Total Booking</div>
                    </div>
                </div>
                <div class="stat-meta"><a href="data_booking.php" class="stat-link">Selengkapnya →</a></div>
            </div>
        </section>
        </aside>

        <!-- Grafik Statistik -->
        <div class="chart-section">
            <div class="chart-card">
                <h3>Statistik Booking Bulanan</h3>
                <canvas id="bookingChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Status Booking</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
            </div>
        </div>
    </main>

    <script>
    // Inisialisasi DataTable
    $(document).ready(function() {
        $('#tablePendaki').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
            }
        });
    });

    // Konfigurasi warna tema
    const themeColors = {
        primary: '#e74c3c',
        secondary: '#c0392b',
        success: '#27ae60',
        warning: '#f39c12',
        danger: '#c0392b',
        info: '#2980b9',
        light: '#ecf0f1',
        dark: '#2c3e50',
        gradient: ['#e74c3c', '#c0392b']
    };

    // Grafik Booking Bulanan
    const bookingCtx = document.getElementById('bookingChart').getContext('2d');
    const gradient = bookingCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(231, 76, 60, 0.4)');
    gradient.addColorStop(1, 'rgba(231, 76, 60, 0.0)');

    new Chart(bookingCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Jumlah Booking',
                data: [65, 59, 80, 81, 56, 55, 40, 45, 70, 85, 90, 100],
                fill: true,
                backgroundColor: gradient,
                borderColor: themeColors.primary,
                borderWidth: 2,
                tension: 0.4,
                pointBackgroundColor: themeColors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            family: 'Poppins',
                            size: 12
                        }
                    }
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

        // context for the status/doughnut chart (was missing in earlier version)
        const statusCtx = document.getElementById('statusChart').getContext('2d');

        new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Sukses', 'Pending', 'Dibatalkan'],
            datasets: [{
                data: [65, 20, 15],
                backgroundColor: [
                    themeColors.success,
                    themeColors.warning,
                    themeColors.danger
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            family: 'Poppins',
                            size: 12
                        },
                        padding: 20
                    }
                }
            },
            cutout: '75%'
        }
    });

    // Fungsi untuk menangani aksi CRUD
    function editPendaki(id) {
        window.location.href = `edit_pendaki.php?id=${id}`;
    }

    function deletePendaki(id) {
        if(confirm('Apakah Anda yakin ingin menghapus data pendaki ini?')) {
            // Implementasi delete via AJAX
        }
    }

    function printData(id) {
        window.open(`cetak_data.php?id=${id}`, '_blank');
    }
    
    // Side summary drawer toggle
    const summaryToggle = document.getElementById('summaryToggle');
    const sideSummary = document.getElementById('sideSummary');
    const sideOverlay = document.getElementById('sideOverlay');
    const drawerClose = document.getElementById('drawerClose');

    function openSummary() {
        sideSummary.classList.add('open');
        sideOverlay.classList.add('open');
        sideSummary.setAttribute('aria-hidden','false');
    }

    function closeSummary() {
        sideSummary.classList.remove('open');
        sideOverlay.classList.remove('open');
        sideSummary.setAttribute('aria-hidden','true');
    }

    summaryToggle.addEventListener('click', function(e){
        if(sideSummary.classList.contains('open')) closeSummary(); else openSummary();
    });
    drawerClose.addEventListener('click', closeSummary);
    sideOverlay.addEventListener('click', closeSummary);
    </script>
</body>
</html>