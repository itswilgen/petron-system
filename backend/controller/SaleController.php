<?php
require_once __DIR__ . '/../model/Sale.php';
require_once __DIR__ . '/../model/Fuel.php';

class SaleController {

    private function posRedirect() {
        return '/public/staff/app.php?page=pos';
    }

    private function performSale($fuel_id, $liters, $branchId, $fuelName = '') {
        if ($liters <= 0) {
            return [
                'success' => false,
                'message' => 'Please enter liters greater than 0.'
            ];
        }

        if ($branchId <= 0) {
            return [
                'success' => false,
                'message' => 'Session branch is missing. Please logout and login again.'
            ];
        }

        $fuelModel = new Fuel();
        $fuel = null;

        if ($fuel_id > 0) {
            $fuel = $fuelModel->getFuelById($fuel_id, $branchId);
        }

        // Fallback for stale forms where fuel id may be missing after option refresh.
        if (!$fuel && trim((string)$fuelName) !== '') {
            $fuel = $fuelModel->getFuelByName(trim((string)$fuelName), $branchId);
        }

        if (!$fuel || (int)($fuel['id'] ?? 0) <= 0) {
            return [
                'success' => false,
                'message' => 'Please select a valid fuel product.'
            ];
        }

        $fuel_id = (int)$fuel['id'];

        if ((float)$fuel['liters'] < $liters) {
            return [
                'success' => false,
                'message' => 'Not enough stock available.'
            ];
        }

        $conn = $fuelModel->getConnection();
        $sale = new Sale($conn);
        $price = (float)$fuel['price'];
        $newLiters = (float)$fuel['liters'] - $liters;
        $total = $liters * $price;

        try {
            $conn->beginTransaction();

            $saleId = $sale->createSale($fuel_id, $liters, $price, $total, $branchId);
            $updateOk = $fuelModel->updateFuel($fuel_id, $newLiters, $price, $fuel['status'], $branchId);

            if (!$saleId || !$updateOk) {
                throw new RuntimeException('Sale could not be saved.');
            }

            $conn->commit();

            $savedSale = $sale->getSaleById($saleId, $branchId);
            $cashier = trim((string)($_SESSION['username'] ?? ''));
            $branchName = trim((string)($_SESSION['branch_name'] ?? ''));

            return [
                'success' => true,
                'message' => 'Sale successful!',
                'new_liters' => $newLiters,
                'fuel_id' => $fuel_id,
                'receipt' => [
                    'sale_id' => (int)$saleId,
                    'reference' => 'TXN-' . str_pad((string)$saleId, 6, '0', STR_PAD_LEFT),
                    'sale_date' => $savedSale['sale_date'] ?? date('Y-m-d H:i:s'),
                    'fuel_name' => $savedSale['fuel_name'] ?? $fuel['fuel_name'],
                    'liters' => isset($savedSale['liters']) ? (float)$savedSale['liters'] : (float)$liters,
                    'price' => isset($savedSale['price']) ? (float)$savedSale['price'] : $price,
                    'total_price' => isset($savedSale['total_price']) ? (float)$savedSale['total_price'] : $total,
                    'cashier' => $cashier !== '' ? $cashier : 'Staff',
                    'branch_name' => $branchName !== '' ? $branchName : ('Branch #' . (int)$branchId)
                ]
            ];
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            $message = 'Failed to save sale.';
            if (($e->errorInfo[1] ?? null) === 1452) {
                $message = 'Sale could not be saved because the sales fuel reference is invalid.';
            }

            return [
                'success' => false,
                'message' => $message
            ];
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            return [
                'success' => false,
                'message' => 'Fuel stock update failed.'
            ];
        }
    }

    // Processes a sale transaction, updates fuel inventory, and handles errors with alerts and redirects.
    public function processSale() {
        if (!isset($_POST['pay'])) {
            return null;
        }

        $result = $this->performSale(
            (int)($_POST['fuel_id'] ?? 0),
            (float)($_POST['liters'] ?? 0),
            (int)($_SESSION['branch_id'] ?? 0),
            trim((string)($_POST['fuel_name'] ?? ''))
        );

        $redirect = $this->posRedirect();
        $message = json_encode($result['message']);

        echo "<script>alert($message);window.location='$redirect';</script>";
        exit;
    }
    
// For sales history page with pagination, search, and date filtering
    public function history($page = 1, $limit = 10, $search = '', $dateFrom = '', $dateTo = '') {
            $sale = new Sale();
            $branchId = (int)($_SESSION['branch_id'] ?? 0);

            $page = max(1, (int)$page);
            $limit = max(1, (int)$limit);
            $offset = ($page - 1) * $limit;

            $rows = $sale->getFilteredSales($branchId, $search, $dateFrom, $dateTo, $limit, $offset);
            $total = $sale->countFilteredSales($branchId, $search, $dateFrom, $dateTo);

            return [
                'rows' => $rows,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ];
    }

// For AJAX sale processing from POS page
    public function processSaleAjax() {
        if (!isset($_POST['fuel_id'], $_POST['liters'])) {
            return [
                'success' => false,
                'message' => 'Invalid request.'
            ];
        }

        return $this->performSale(
            (int)($_POST['fuel_id'] ?? 0),
            (float)($_POST['liters'] ?? 0),
            (int)($_SESSION['branch_id'] ?? 0),
            trim((string)($_POST['fuel_name'] ?? ''))
        );
    }

// For initial page load of POS page
    public function posPageData() {
    $this->processSale();

        $fuelModel = new Fuel();
        $branchId = (int)($_SESSION['branch_id'] ?? 0);

        $fuels = $fuelModel->getAllFuel($branchId);

        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $search = trim($_GET['q'] ?? '');
        $dateFrom = trim($_GET['from'] ?? '');
        $dateTo = trim($_GET['to'] ?? '');

        $data = $this->history($page, 10, $search, $dateFrom, $dateTo);

        $rows = $data['rows'];
        $total = $data['total'];
        $pageNow = $data['page'];
        $limit = $data['limit'];
        $totalPages = (int)ceil($total / $limit);

        return [
            'fuels' => $fuels,
            'rows' => $rows,
            'total' => $total,
            'pageNow' => $pageNow,
            'limit' => $limit,
            'totalPages' => $totalPages,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ];
    }
// For initial page load of sales history page
    public function salesHistoryPageData() {
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $search = trim($_GET['q'] ?? '');
        $dateFrom = trim($_GET['from'] ?? '');
        $dateTo = trim($_GET['to'] ?? '');

        $data = $this->history($page, 10, $search, $dateFrom, $dateTo);

        $rows = $data['rows'];
        $total = $data['total'];
        $pageNow = $data['page'];
        $limit = $data['limit'];
        $totalPages = (int)ceil($total / $limit);

        return [
            'rows' => $rows,
            'total' => $total,
            'pageNow' => $pageNow,
            'limit' => $limit,
            'totalPages' => $totalPages,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ];
    }
}
?>
        
