<?php

require_once __DIR__ . '/../model/Delivery.php';
require_once __DIR__ . '/../model/Fuel.php';

class DeliveryController {

    private function deliveryRedirect() {
        return "/public/admin/app.php?page=delivery";
    }

    public function index($page = 1, $limit = 3) {
        $delivery = new Delivery();
        $branchId = (int)($_SESSION['branch_id'] ?? 0);

        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        $rows  = $delivery->getDeliveriesPaginated($branchId, $limit, $offset);
        $total = $delivery->countDeliveries($branchId);

        return [
            'rows'  => $rows,
            'total' => $total,
            'page'  => $page,
            'limit' => $limit
        ];
    }

    // Handles form submission for adding a new delivery and updating fuel inventory
    public function store() {

        if (!isset($_POST['add_delivery'])) {
            return null;
        }

        $fuel_id      = (int)($_POST['fuel_id'] ?? 0);
        $liters_added = (float)($_POST['liters_added'] ?? 0);
        $branchId     = (int)($_SESSION['branch_id'] ?? 0);

        $redirect = $this->deliveryRedirect();

        if ($fuel_id <= 0 || $liters_added <= 0 || $branchId <= 0) {
            $_SESSION['error'] = "Invalid delivery data.";
            header("Location: $redirect");
            exit;
        }

        $fuelModel = new Fuel();
        $fuel = $fuelModel->getFuelById($fuel_id, $branchId);

        if (!$fuel) {
            $_SESSION['error'] = "Fuel not found.";
            header("Location: $redirect");
            exit;
        }

        $currentLiters = (float)$fuel['liters'];
        $capacity      = (float)$fuel['capacity'];
        $newLiters     = $currentLiters + $liters_added;

        if ($capacity > 0 && $newLiters > $capacity) {
            $allowed = $capacity - $currentLiters;

            $_SESSION['error'] =
                "Delivery denied: Tank capacity is " . number_format($capacity, 2) .
                " L. You can only add up to " . number_format(max($allowed, 0), 2) . " L.";

            header("Location: $redirect");
            exit;
        }

        $conn = $fuelModel->getConnection();
        $deliveryModel = new Delivery($conn);

        try {
            $conn->beginTransaction();

            $deliveryOk = $deliveryModel->addDelivery($fuel_id, $liters_added, $branchId);
            $updateOk = $fuelModel->updateFuel($fuel_id, $newLiters, $fuel['price'], $fuel['status'], $branchId);

            if (!$deliveryOk || !$updateOk) {
                throw new RuntimeException('Delivery could not be saved.');
            }

            $conn->commit();
            $_SESSION['success'] = "Delivery added and inventory updated!";
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            if (($e->errorInfo[1] ?? null) === 1452) {
                $_SESSION['error'] = "Delivery failed: deliveries.fuel_id is linked to the wrong parent table. Update the foreign key to reference fuels.id.";
            } else {
                $_SESSION['error'] = "Failed to save delivery.";
            }
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            $_SESSION['error'] = "Failed to update delivery inventory.";
        }

        header("Location: $redirect");
        exit;
    }

    // For initial page load of delivery management page
    public function pageData() {
        $this->store();

        $fuelModel = new Fuel();
        $branchId = (int)($_SESSION['branch_id'] ?? 0);
        $fuelsStmt = $fuelModel->getAllFuel($branchId);

        $historyPage = isset($_GET['history_page']) ? (int)$_GET['history_page'] : 1;
        $history = $this->index($historyPage, 5);

        $deliveries   = $history['rows'];
        $totalHistory = $history['total'];
        $pageNow      = $history['page'];
        $limit        = $history['limit'];

        $shownSoFar = $pageNow * $limit;
        $hasMore    = $shownSoFar < $totalHistory;

        return [
            'fuelsStmt'     => $fuelsStmt,
            'deliveries'    => $deliveries,
            'totalHistory'  => $totalHistory,
            'pageNow'       => $pageNow,
            'limit'         => $limit,
            'hasMore'       => $hasMore
        ];
    }

    // For delivery history with pagination, search, and date filtering
    public function history($page = 1, $limit = 10, $search = '', $dateFrom = '', $dateTo = '') {

        $delivery = new Delivery();
        $branchId = (int)($_SESSION['branch_id'] ?? 0);

        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        $rows = $delivery->getFilteredDeliveries($branchId, $limit, $offset, $search, $dateFrom, $dateTo);
        $total = $delivery->countFilteredDeliveries($branchId, $search, $dateFrom, $dateTo);

        return [
            'rows'  => $rows,
            'total' => $total,
            'page'  => $page,
            'limit' => $limit
        ];
    }

    // For initial page load of delivery history
    public function historyPageData() {
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $search = trim($_GET['q'] ?? '');
        $dateFrom = trim($_GET['from'] ?? '');
        $dateTo = trim($_GET['to'] ?? '');

        $data = $this->history($page, 10, $search, $dateFrom, $dateTo);

        $rows = $data['rows'];
        $total = $data['total'];
        $limit = $data['limit'];
        $pageNow = $data['page'];
        $totalPages = (int)ceil($total / $limit);

        return [
            'rows' => $rows,
            'total' => $total,
            'limit' => $limit,
            'pageNow' => $pageNow,
            'totalPages' => $totalPages,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ];
    }
}
