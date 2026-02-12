<?php
require_once __DIR__ . '/../model/Sale.php';
require_once __DIR__ . '/../model/Fuel.php';

class SaleController {

    public function processSale(){
        if(isset($_POST['pay'])){

            $fuel_id = $_POST['fuel_id'];
            $liters = $_POST['liters'];

            $fuelModel = new Fuel();
            $fuel = $fuelModel->getFuelById($fuel_id);

            $price = $fuel['price'];
            $total = $liters * $price;

            // save sale
            $sale = new Sale();
            $sale->createSale($fuel_id,$liters,$price,$total);

            // deduct inventory
            $newLiters = $fuel['liters'] - $liters;
            $fuelModel->updateFuel($fuel_id,$newLiters,$price,$fuel['status']);

            echo "<script>alert('Sale successful!');window.location='../views/pos.php';</script>";
        }
    }
}
?>
