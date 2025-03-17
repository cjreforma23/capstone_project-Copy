<?php
function generateSidebar($role) {
    $sidebarItems = [
        'Admin' => [
            'Dashboard' => ['url' => '/views/admin/admin_dashboard.php', 'icon' => 'fas fa-tachometer-alt'],
            'Manage Users' => ['url' => '/views/admin/admin_manageuser.php', 'icon' => 'fas fa-users'],
            'Monthly Dues' => ['url' => '/views/admin/admin_dues.php', 'icon' => 'fas fa-money-bill'],
            'Reservation' => ['url' => '/views/admin/admin_reservation.php', 'icon' => 'fas fa-calendar-check'],
            'Complaints' => ['url' => '/views/admin/admin_complaints.php', 'icon' => 'fas fa-exclamation-circle'],
            'Settings' => ['url' => '/views/admin/admin_settings.php', 'icon' => 'fas fa-cog'],
        ],
        'Staff' => [
            'Dashboard' => ['url' => '/views/staff/staff_dashboard.php', 'icon' => 'fas fa-tachometer-alt'],
            'Manage Users' => ['url' => '/views/staff/staff_manageuser.php', 'icon' => 'fas fa-users'],
            'Monthly Dues' => ['url' => '/views/staff/staff_dues.php', 'icon' => 'fas fa-money-bill'],
            'Reservation' => ['url' => '/views/staff/staff_reservation.php', 'icon' => 'fas fa-calendar-check'],
            'Complaints' => ['url' => '/views/staff/staff_complaints.php', 'icon' => 'fas fa-exclamation-circle'],
            'Settings' => ['url' => '/views/staff/staff_settings.php', 'icon' => 'fas fa-cog'],
        ],
        'Guard' => [
            'Dashboard' => ['url' => '/views/guard/guard_dashboard.php', 'icon' => 'fas fa-tachometer-alt'],
            'Manage Users' => ['url' => '/views/guard/guard_manageuser.php', 'icon' => 'fas fa-users'],
            'Settings' => ['url' => '/views/guard/guard_settings.php', 'icon' => 'fas fa-cog'],
        ],
        'Homeowner' => [
            'Home' => ['url' => '/views/homeowner/homeowner_home.php', 'icon' => 'fas fa-tachometer-alt'],
            'Monthly Dues' => ['url' => '/views/homeowner/homeowner_dues.php', 'icon' => 'fas fa-money-bill'],
            'Reservation' => ['url' => '/views/homeowner/homeowner_reservation.php', 'icon' => 'fas fa-calendar-check'],
            'Complaints' => ['url' => '/views/homeowner/homeowner_complaints.php', 'icon' => 'fas fa-exclamation-circle'],
            'Settings' => ['url' => '/views/homeowner/homeowner_settings.php', 'icon' => 'fas fa-cog'],
        ],
        'Guest' => [
            'Home' => ['url' => '/views/guest/guest_home.php', 'icon' => 'fas fa-home'],
            'Reservation' => ['url' => '/views/guest/guest_reservation.php', 'icon' => 'fas fa-calendar-check'],
            'Contact Us' => ['url' => '/views/guest/guest_contact.php', 'icon' => 'fas fa-envelope'],
            'About' => ['url' => '/views/guest/guest_about.php', 'icon' => 'fas fa-info-circle'],
        ]
    ];

    if (!isset($sidebarItems[$role])) {
        echo "<p>No sidebar available for this role.</p>";
        return;
    }

    // Get current page URL to highlight active link
    $currentPage = $_SERVER['PHP_SELF'];

    echo "<ul class='sidebar-menu'>";
    foreach ($sidebarItems[$role] as $title => $item) {
        $isActive = ($currentPage == $item['url']) ? 'active' : '';
        echo "<li><a href='{$item['url']}' class='list-group-item list-group-item-action {$isActive}'><i class='{$item['icon']}'></i> $title</a></li>";
    }
    echo "</ul>";
}
?>