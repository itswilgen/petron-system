<?php

require_once __DIR__ . '/../model/Dashboard.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Fuel.php';
require_once __DIR__ . '/../includes/auth_roles.php';

class SuperAdminController {
    private $dashboard;
    private $user;
    private $fuel;

    public function __construct() {
        $this->dashboard = new Dashboard();
        $this->user = new User();
        $this->fuel = new Fuel();
    }

    private function ensureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function ensureSuperAdmin() {
        $this->ensureSession();

        $role = $_SESSION['role'] ?? '';
        if (!canAccessSuperAdminArea($role)) {
            header("Location: /petron_system/public/auth/login.php");
            exit;
        }
    }

    private function redirectToAdminAccounts($queryString = '') {
        $base = "/petron_system/public/superadmin/app.php?page=admin_accounts";
        if ($queryString !== '') {
            if (strpos($queryString, '=') === false) {
                $base .= "&{$queryString}=1";
            } else {
                $base .= "&{$queryString}";
            }
        }
        header("Location: {$base}");
        exit;
    }

    private function redirectToGlobalPricing($queryString = '') {
        $base = "/petron_system/public/superadmin/app.php?page=global_pricing";
        if ($queryString !== '') {
            $base .= "&" . $queryString;
        }
        header("Location: {$base}");
        exit;
    }

    private function buildSalesTrendForLastDays($days = 7) {
        $days = (int)$days;
        if ($days < 1) {
            $days = 7;
        }

        $startDate = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $rawRows = $this->dashboard->getRegionalSalesTrendAllBranches($startDate);

        $indexed = [];
        foreach ($rawRows as $row) {
            $day = $row['sale_day'] ?? '';
            if ($day !== '') {
                $indexed[$day] = (float)($row['total_sales'] ?? 0);
            }
        }

        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $trend[] = [
                'sale_day' => $day,
                'label' => date('M d', strtotime($day)),
                'total_sales' => (float)($indexed[$day] ?? 0)
            ];
        }

        return $trend;
    }

    public function getDashboardData() {
        $this->ensureSuperAdmin();

        return [
            'salesToday' => $this->dashboard->getTotalSalesTodayAllBranches(),
            'litersToday' => $this->dashboard->getLitersTodayAllBranches(),
            'fuelCount' => $this->dashboard->getFuelCountAllBranches(),
            'lowStock' => $this->dashboard->getLowStockAllBranches(),
            'branchSummary' => $this->dashboard->getBranchDailySummaryAllBranches(),
            'salesTrend' => $this->buildSalesTrendForLastDays(7),
            'totalBranches' => $this->user->countBranches(),
            'adminCount' => $this->user->countUsersByRole(ROLE_ADMIN),
            'staffCount' => $this->user->countUsersByRole(ROLE_STAFF)
        ];
    }

    public function getBranchOperationsData() {
        $this->ensureSuperAdmin();

        $rows = $this->dashboard->getBranchOperationsAllBranches();
        $totals = [
            'branch_count' => count($rows),
            'sales_today' => 0.0,
            'liters_today' => 0.0,
            'transactions_today' => 0,
            'deliveries_today' => 0,
            'delivered_liters_today' => 0.0,
            'low_stock_count' => 0
        ];

        foreach ($rows as $row) {
            $totals['sales_today'] += (float)($row['sales_today'] ?? 0);
            $totals['liters_today'] += (float)($row['liters_today'] ?? 0);
            $totals['transactions_today'] += (int)($row['transactions_today'] ?? 0);
            $totals['deliveries_today'] += (int)($row['deliveries_today'] ?? 0);
            $totals['delivered_liters_today'] += (float)($row['delivered_liters_today'] ?? 0);
            $totals['low_stock_count'] += (int)($row['low_stock_count'] ?? 0);
        }

        return [
            'rows' => $rows,
            'totals' => $totals
        ];
    }

    private function clampScore($score) {
        $score = (int)round($score);
        if ($score < 0) {
            return 0;
        }
        if ($score > 100) {
            return 100;
        }
        return $score;
    }

    private function buildBranchHealthRow(array $row) {
        $revenue7d = (float)($row['revenue_7d'] ?? 0);
        $revenuePrev7d = (float)($row['revenue_prev_7d'] ?? 0);
        $transactions7d = (int)($row['transactions_7d'] ?? 0);
        $transactionsToday = (int)($row['transactions_today'] ?? 0);
        $liters7d = (float)($row['liters_7d'] ?? 0);
        $stockLiters = (float)($row['stock_liters'] ?? 0);
        $totalFuels = (int)($row['total_fuels'] ?? 0);
        $lowStockCount = (int)($row['low_stock_count'] ?? 0);
        $deliveries7d = (int)($row['deliveries_7d'] ?? 0);
        $deliveredLiters7d = (float)($row['delivered_liters_7d'] ?? 0);

        $avgTicket7d = $transactions7d > 0 ? ($revenue7d / $transactions7d) : 0.0;
        $avgDailyLiters7d = $liters7d > 0 ? ($liters7d / 7.0) : 0.0;
        $stockCoverageDays = $avgDailyLiters7d > 0 ? ($stockLiters / $avgDailyLiters7d) : null;
        $lowStockRatio = $totalFuels > 0 ? ($lowStockCount / $totalFuels) : 0.0;
        $trendAmount = $revenue7d - $revenuePrev7d;
        $trendPercent = $revenuePrev7d > 0
            ? (($trendAmount / $revenuePrev7d) * 100.0)
            : ($revenue7d > 0 ? 100.0 : 0.0);

        $score = 100.0;
        $notes = [];

        if ($revenue7d <= 0) {
            $score -= 45;
            $notes[] = 'No sales in the last 7 days.';
        } elseif ($trendPercent < -20) {
            $score -= 25;
            $notes[] = 'Revenue dropped sharply versus previous week.';
        } elseif ($trendPercent < -5) {
            $score -= 12;
            $notes[] = 'Revenue is down versus previous week.';
        } elseif ($trendPercent >= 10) {
            $score += 5;
        }

        if ($transactionsToday <= 0 && $transactions7d <= 5) {
            $score -= 10;
            $notes[] = 'Low sales activity.';
        }

        if ($lowStockRatio >= 0.50) {
            $score -= 25;
            $notes[] = 'Many fuel products are low stock.';
        } elseif ($lowStockRatio >= 0.30) {
            $score -= 15;
            $notes[] = 'Low stock warnings require attention.';
        }

        if ($stockCoverageDays !== null) {
            if ($stockCoverageDays < 2) {
                $score -= 25;
                $notes[] = 'Stock coverage is critical.';
            } elseif ($stockCoverageDays < 4) {
                $score -= 12;
                $notes[] = 'Stock coverage is short.';
            }
        }

        if ($deliveries7d <= 0 && $stockCoverageDays !== null && $stockCoverageDays < 7) {
            $score -= 10;
            $notes[] = 'No recent deliveries despite limited stock coverage.';
        }

        $score = $this->clampScore($score);
        $health = 'Good';
        if ($score < 50) {
            $health = 'Bad';
        } elseif ($score < 75) {
            $health = 'Warning';
        }

        if (count($notes) === 0) {
            $notes[] = 'Operations are stable.';
        }

        return [
            'branch_id' => (int)($row['branch_id'] ?? 0),
            'branch_name' => (string)($row['branch_name'] ?? '-'),
            'location' => (string)($row['location'] ?? '-'),
            'revenue_7d' => $revenue7d,
            'revenue_prev_7d' => $revenuePrev7d,
            'trend_amount' => $trendAmount,
            'trend_percent' => $trendPercent,
            'transactions_7d' => $transactions7d,
            'transactions_today' => $transactionsToday,
            'liters_7d' => $liters7d,
            'avg_ticket_7d' => $avgTicket7d,
            'stock_liters' => $stockLiters,
            'stock_coverage_days' => $stockCoverageDays,
            'total_fuels' => $totalFuels,
            'low_stock_count' => $lowStockCount,
            'low_stock_ratio' => $lowStockRatio,
            'deliveries_7d' => $deliveries7d,
            'delivered_liters_7d' => $deliveredLiters7d,
            'health_score' => $score,
            'health' => $health,
            'note' => implode(' ', $notes)
        ];
    }

    public function getBusinessHealthData() {
        $this->ensureSuperAdmin();

        $sourceRows = $this->dashboard->getBranchBusinessHealthAllBranches();
        $rows = [];

        $summary = [
            'branch_count' => 0,
            'revenue_7d' => 0.0,
            'revenue_prev_7d' => 0.0,
            'trend_amount' => 0.0,
            'trend_percent' => 0.0,
            'good_count' => 0,
            'warning_count' => 0,
            'bad_count' => 0,
            'avg_health_score' => 0.0
        ];

        foreach ($sourceRows as $row) {
            $mapped = $this->buildBranchHealthRow($row);
            $rows[] = $mapped;

            $summary['branch_count']++;
            $summary['revenue_7d'] += $mapped['revenue_7d'];
            $summary['revenue_prev_7d'] += $mapped['revenue_prev_7d'];
            $summary['avg_health_score'] += $mapped['health_score'];

            if ($mapped['health'] === 'Good') {
                $summary['good_count']++;
            } elseif ($mapped['health'] === 'Warning') {
                $summary['warning_count']++;
            } else {
                $summary['bad_count']++;
            }
        }

        $summary['trend_amount'] = $summary['revenue_7d'] - $summary['revenue_prev_7d'];
        $summary['trend_percent'] = $summary['revenue_prev_7d'] > 0
            ? (($summary['trend_amount'] / $summary['revenue_prev_7d']) * 100.0)
            : ($summary['revenue_7d'] > 0 ? 100.0 : 0.0);

        if ($summary['branch_count'] > 0) {
            $summary['avg_health_score'] = $summary['avg_health_score'] / $summary['branch_count'];
        }

        return [
            'rows' => $rows,
            'summary' => $summary
        ];
    }

    public function getGlobalPricingData() {
        $this->ensureSuperAdmin();
        return $this->fuel->getGlobalPriceSummary();
    }

    public function updateGlobalPricing() {
        if (!isset($_POST['apply_global_price'])) {
            return null;
        }

        $this->ensureSuperAdmin();

        $fuelName = trim((string)($_POST['fuel_name'] ?? ''));
        $price = (float)($_POST['price'] ?? -1);

        if ($fuelName === '' || $price < 0) {
            return "Invalid fuel name or price.";
        }

        if (!$this->fuel->fuelNameExistsGlobally($fuelName)) {
            return "Fuel product not found.";
        }

        $updatedRows = $this->fuel->updateGlobalPriceByFuelName($fuelName, $price);

        $query = http_build_query([
            'updated' => 1,
            'fuel' => $fuelName,
            'rows' => max(0, (int)$updatedRows),
            'price' => number_format($price, 2, '.', '')
        ]);
        $this->redirectToGlobalPricing($query);
    }

    public function getBranches() {
        $this->ensureSuperAdmin();
        return $this->user->getBranches();
    }

    public function getAdmins() {
        $this->ensureSuperAdmin();
        return $this->user->getAdminsAllBranches();
    }

    public function createAdmin() {
        if (!isset($_POST['create_admin'])) {
            return null;
        }

        $this->ensureSuperAdmin();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $branchId = (int)($_POST['branch_id'] ?? 0);

        if ($username === '' || $password === '' || $branchId <= 0) {
            return "Please fill in all fields.";
        }

        if (strlen($password) < 6) {
            return "Password must be at least 6 characters.";
        }

        if (!$this->user->branchExists($branchId)) {
            return "Selected branch is invalid.";
        }

        if ($this->user->usernameExists($username)) {
            return "Username already exists.";
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $created = $this->user->createAdminWithUniqueId($username, $hashed, $branchId);

        if (($created['ok'] ?? false) === true) {
            $query = http_build_query([
                'created' => 1,
                'admin_uid' => (string)($created['admin_uid'] ?? '')
            ]);
            $this->redirectToAdminAccounts($query);
        }

        return "Failed to create admin account.";
    }

    public function deleteAdmin() {
        if (!isset($_GET['delete_admin_id'])) {
            return null;
        }

        $this->ensureSuperAdmin();

        $deleteId = (int)($_GET['delete_admin_id'] ?? 0);
        if ($deleteId <= 0) {
            $this->redirectToAdminAccounts('denied');
        }

        $target = $this->user->getUserById($deleteId);
        if (!$target || ($target['role'] ?? '') !== ROLE_ADMIN) {
            $this->redirectToAdminAccounts('denied');
        }

        $this->user->deleteUserById($deleteId);
        $this->redirectToAdminAccounts('deleted');
    }
}
