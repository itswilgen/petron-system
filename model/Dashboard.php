<?php
require_once __DIR__ . '/../database/Database.php';

class Dashboard {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Today's sales total by branch
    public function getSalesToday($branch_id) {
        $stmt = $this->conn->prepare("
            SELECT SUM(total_price) as total_sales
            FROM sales
            WHERE DATE(sale_date) = CURDATE()
              AND branch_id = ?
        ");
        $stmt->execute([$branch_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;
    }

    // Today's liters sold by branch
    public function getLitersToday($branch_id) {
        $stmt = $this->conn->prepare("
            SELECT SUM(liters) as total_liters
            FROM sales
            WHERE DATE(sale_date) = CURDATE()
              AND branch_id = ?
        ");
        $stmt->execute([$branch_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_liters'] ?? 0;
    }

    // Total fuel types count by branch
    public function getFuelCount($branch_id) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total_fuels
            FROM fuels
            WHERE branch_id = ?
        ");
        $stmt->execute([$branch_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_fuels'] ?? 0;
    }

    // Low stock fuels by branch
    public function getLowStock($branch_id) {
        $stmt = $this->conn->prepare("
            SELECT fuel_name, liters, capacity
            FROM fuels
            WHERE branch_id = ?
              AND capacity > 0
              AND (liters / capacity) <= 0.30
        ");
        $stmt->execute([$branch_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fuel levels for dashboard chart by branch
    public function getFuelLevels($branch_id) {
        $stmt = $this->conn->prepare("
            SELECT fuel_name, liters, capacity
            FROM fuels
            WHERE branch_id = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$branch_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Sales trend for dashboard chart by branch
    public function getSalesTrend($branch_id) {
        $stmt = $this->conn->prepare("
            SELECT DATE(sale_date) AS sale_day, SUM(total_price) AS total_sales
            FROM sales
            WHERE branch_id = ?
            GROUP BY DATE(sale_date)
            ORDER BY sale_day ASC
            LIMIT 10
        ");
        $stmt->execute([$branch_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalSalesTodayAllBranches() {
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(total_price), 0) AS total_sales
            FROM sales
            WHERE DATE(sale_date) = CURDATE()
        ");
        $stmt->execute();
        return (float)($stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0);
    }

    public function getLitersTodayAllBranches() {
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(liters), 0) AS total_liters
            FROM sales
            WHERE DATE(sale_date) = CURDATE()
        ");
        $stmt->execute();
        return (float)($stmt->fetch(PDO::FETCH_ASSOC)['total_liters'] ?? 0);
    }

    public function getFuelCountAllBranches() {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS total_fuels
            FROM fuels
        ");
        $stmt->execute();
        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total_fuels'] ?? 0);
    }

    public function getLowStockAllBranches() {
        $stmt = $this->conn->prepare("
            SELECT f.fuel_name, f.liters, f.capacity, b.branch_name
            FROM fuels f
            JOIN branches b ON b.id = f.branch_id
            WHERE f.capacity > 0
              AND (f.liters / f.capacity) <= 0.30
            ORDER BY (f.liters / f.capacity) ASC, b.branch_name ASC, f.fuel_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBranchDailySummaryAllBranches() {
        $stmt = $this->conn->prepare("
            SELECT
                b.id AS branch_id,
                b.branch_name,
                COALESCE(s.sales_today, 0) AS sales_today,
                COALESCE(s.liters_today, 0) AS liters_today,
                COALESCE(f.total_fuels, 0) AS total_fuels,
                COALESCE(a.admin_usernames, '-') AS admin_usernames
            FROM branches b
            LEFT JOIN (
                SELECT
                    branch_id,
                    SUM(total_price) AS sales_today,
                    SUM(liters) AS liters_today
                FROM sales
                WHERE DATE(sale_date) = CURDATE()
                GROUP BY branch_id
            ) s ON s.branch_id = b.id
            LEFT JOIN (
                SELECT branch_id, COUNT(*) AS total_fuels
                FROM fuels
                GROUP BY branch_id
            ) f ON f.branch_id = b.id
            LEFT JOIN (
                SELECT
                    branch_id,
                    GROUP_CONCAT(username ORDER BY username SEPARATOR ', ') AS admin_usernames
                FROM users
                WHERE role = 'admin'
                GROUP BY branch_id
            ) a ON a.branch_id = b.id
            ORDER BY b.id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRegionalSalesTrendAllBranches($startDate) {
        $stmt = $this->conn->prepare("
            SELECT
                DATE(sale_date) AS sale_day,
                COALESCE(SUM(total_price), 0) AS total_sales
            FROM sales
            WHERE DATE(sale_date) >= :start_date
            GROUP BY DATE(sale_date)
            ORDER BY sale_day ASC
        ");
        $stmt->execute([
            ':start_date' => $startDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBranchOperationsAllBranches() {
        $stmt = $this->conn->prepare("
            SELECT
                b.id AS branch_id,
                b.branch_name,
                b.location,
                COALESCE(s.sales_today, 0) AS sales_today,
                COALESCE(s.liters_today, 0) AS liters_today,
                COALESCE(s.transactions_today, 0) AS transactions_today,
                s.last_sale_at,
                COALESCE(d.deliveries_today, 0) AS deliveries_today,
                COALESCE(d.delivered_liters_today, 0) AS delivered_liters_today,
                d.last_delivery_at,
                COALESCE(f.total_fuels, 0) AS total_fuels,
                COALESCE(f.total_stock_liters, 0) AS total_stock_liters,
                COALESCE(f.low_stock_count, 0) AS low_stock_count,
                COALESCE(u.admin_count, 0) AS admin_count,
                COALESCE(u.staff_count, 0) AS staff_count
            FROM branches b
            LEFT JOIN (
                SELECT
                    branch_id,
                    SUM(total_price) AS sales_today,
                    SUM(liters) AS liters_today,
                    COUNT(*) AS transactions_today,
                    MAX(sale_date) AS last_sale_at
                FROM sales
                WHERE DATE(sale_date) = CURDATE()
                GROUP BY branch_id
            ) s ON s.branch_id = b.id
            LEFT JOIN (
                SELECT
                    branch_id,
                    COUNT(*) AS deliveries_today,
                    SUM(liters_added) AS delivered_liters_today,
                    MAX(delivery_date) AS last_delivery_at
                FROM deliveries
                WHERE DATE(delivery_date) = CURDATE()
                GROUP BY branch_id
            ) d ON d.branch_id = b.id
            LEFT JOIN (
                SELECT
                    branch_id,
                    COUNT(*) AS total_fuels,
                    SUM(liters) AS total_stock_liters,
                    SUM(
                        CASE
                            WHEN capacity > 0 AND (liters / capacity) <= 0.30 THEN 1
                            ELSE 0
                        END
                    ) AS low_stock_count
                FROM fuels
                GROUP BY branch_id
            ) f ON f.branch_id = b.id
            LEFT JOIN (
                SELECT
                    branch_id,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admin_count,
                    SUM(CASE WHEN role = 'staff' THEN 1 ELSE 0 END) AS staff_count
                FROM users
                GROUP BY branch_id
            ) u ON u.branch_id = b.id
            ORDER BY b.id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBranchBusinessHealthAllBranches() {
        $stmt = $this->conn->prepare("
            SELECT
                b.id AS branch_id,
                b.branch_name,
                b.location,
                COALESCE(curr.revenue_7d, 0) AS revenue_7d,
                COALESCE(prev.revenue_prev_7d, 0) AS revenue_prev_7d,
                COALESCE(curr.transactions_7d, 0) AS transactions_7d,
                COALESCE(curr.transactions_today, 0) AS transactions_today,
                COALESCE(curr.liters_7d, 0) AS liters_7d,
                COALESCE(stock.stock_liters, 0) AS stock_liters,
                COALESCE(stock.total_fuels, 0) AS total_fuels,
                COALESCE(stock.low_stock_count, 0) AS low_stock_count,
                COALESCE(del.deliveries_7d, 0) AS deliveries_7d,
                COALESCE(del.delivered_liters_7d, 0) AS delivered_liters_7d
            FROM branches b
            LEFT JOIN (
                SELECT
                    branch_id,
                    SUM(CASE WHEN DATE(sale_date) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) THEN total_price ELSE 0 END) AS revenue_7d,
                    SUM(CASE WHEN DATE(sale_date) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) THEN liters ELSE 0 END) AS liters_7d,
                    SUM(CASE WHEN DATE(sale_date) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) THEN 1 ELSE 0 END) AS transactions_7d,
                    SUM(CASE WHEN DATE(sale_date) = CURDATE() THEN 1 ELSE 0 END) AS transactions_today
                FROM sales
                GROUP BY branch_id
            ) curr ON curr.branch_id = b.id
            LEFT JOIN (
                SELECT
                    branch_id,
                    SUM(total_price) AS revenue_prev_7d
                FROM sales
                WHERE DATE(sale_date) BETWEEN DATE_SUB(CURDATE(), INTERVAL 13 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY branch_id
            ) prev ON prev.branch_id = b.id
            LEFT JOIN (
                SELECT
                    branch_id,
                    COUNT(*) AS total_fuels,
                    SUM(liters) AS stock_liters,
                    SUM(
                        CASE
                            WHEN capacity > 0 AND (liters / capacity) <= 0.30 THEN 1
                            ELSE 0
                        END
                    ) AS low_stock_count
                FROM fuels
                GROUP BY branch_id
            ) stock ON stock.branch_id = b.id
            LEFT JOIN (
                SELECT
                    branch_id,
                    COUNT(*) AS deliveries_7d,
                    SUM(liters_added) AS delivered_liters_7d
                FROM deliveries
                WHERE DATE(delivery_date) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY branch_id
            ) del ON del.branch_id = b.id
            ORDER BY b.id ASC
        ");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
