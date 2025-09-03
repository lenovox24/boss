<?php
// File: includes/nav-guest.php (REVISI FINAL FULL - Header Tamu)

// Query ini mengambil semua kategori beserta provider yang terkait dalam satu kali panggilan
$menu_data_query = "
    SELECT 
        c.name AS category_name, 
        p.nama_provider
    FROM categories c
    LEFT JOIN providers p ON LOWER(p.kategori) = LOWER(c.name)
    WHERE c.is_active = 1 AND p.id IS NOT NULL
    ORDER BY c.sort_order, p.sort_order
";

$menu_result = $conn->query($menu_data_query);

// Mengolah data menjadi struktur yang mudah digunakan
$menu_items = [];
if ($menu_result) {
    while ($row = $menu_result->fetch_assoc()) {
        $menu_items[$row['category_name']][] = $row['nama_provider'];
    }
}
?>

<header class="main-header sticky-top">
    <div class="container">
        <!-- Mobile Layout -->
        <div class="d-lg-none d-flex justify-content-between align-items-center">
            <div style="width: 60px;"></div> <!-- Spacer -->
            <a href="index" class="logo text-center">
                <img src="<?php echo $base_url; ?>assets/images/<?php echo htmlspecialchars($settings['main_logo'] ?? 'logo.png'); ?>" alt="Logo Situs" class="main-logo-animated" style="height: 50px;">
            </a>
            <button class="btn modern-menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                <div class="hamburger-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
        </div>
        
        <!-- Desktop Layout -->
        <div class="d-none d-lg-flex justify-content-between align-items-center">
            <nav class="main-nav">
                <a href="index">Home</a>
                <?php foreach ($menu_items as $category => $providers): ?>
                    <div class="nav-item-dropdown">
                        <a href="#" class="dropdown-toggle"><?php echo htmlspecialchars($category); ?></a>
                        <div class="dropdown-menu-custom">
                            <?php foreach ($providers as $provider): ?>
                                <a href="#" class="dropdown-item provider-link"><?php echo htmlspecialchars($provider); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <a href="promo">Bonus</a>
            </nav>

            <a href="index" class="logo-center">
                <img src="<?php echo $base_url; ?>assets/images/<?php echo htmlspecialchars($settings['main_logo'] ?? 'logo.png'); ?>" alt="Logo Situs" class="main-logo-animated" style="height: 60px;">
            </a>

            <div class="auth-buttons-desktop">
                <a href="login" class="btn btn-outline-light btn-sm">Login</a>
                <a href="daftar" class="btn btn-warning btn-sm fw-bold">Daftar</a>
            </div>
        </div>
    </div>
</header>

<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title" id="mobileMenuLabel">MENU</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="mobile-nav">
            <a href="index">Home</a>
            <?php foreach (
                $menu_items as $category => $providers
            ): ?>
                <div class="mobile-nav-item-dropdown">
                    <a href="#" class="mobile-dropdown-toggle d-flex justify-content-between align-items-center">
                        <span><?php echo htmlspecialchars($category); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="mobile-dropdown-menu">
                        <?php foreach ($providers as $provider): ?>
                            <a href="#" class="mobile-dropdown-item provider-link"><?php echo htmlspecialchars($provider); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <a href="promo">Bonus</a>
        </nav>
    </div>
</div>

<script>
// Enhanced Mobile Menu JavaScript for Guest
document.addEventListener('DOMContentLoaded', function() {
    // Mobile dropdown functionality
    const mobileDropdownToggles = document.querySelectorAll('.mobile-dropdown-toggle');
    
    mobileDropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const dropdownMenu = this.nextElementSibling;
            const icon = this.querySelector('i');
            
            // Close other dropdowns
            mobileDropdownToggles.forEach(otherToggle => {
                if (otherToggle !== this) {
                    otherToggle.classList.remove('active');
                    otherToggle.nextElementSibling.style.display = 'none';
                }
            });
            
            // Toggle current dropdown
            this.classList.toggle('active');
            
            if (dropdownMenu.style.display === 'block') {
                dropdownMenu.style.display = 'none';
            } else {
                dropdownMenu.style.display = 'block';
            }
        });
    });
    
    // Add shimmer effect to sidebar header
    const sidebarHeader = document.querySelector('#mobileMenu .offcanvas-header');
    if (sidebarHeader) {
        // Trigger shimmer on sidebar open
        const mobileMenuElement = document.getElementById('mobileMenu');
        if (mobileMenuElement) {
            mobileMenuElement.addEventListener('shown.bs.offcanvas', function() {
                // Add a subtle glow effect when opened
                this.style.boxShadow = '4px 0 25px rgba(255, 193, 7, 0.3)';
            });
            
            mobileMenuElement.addEventListener('hidden.bs.offcanvas', function() {
                // Reset shadow when closed
                this.style.boxShadow = '4px 0 20px rgba(0, 0, 0, 0.5)';
            });
        }
    }
});
</script>