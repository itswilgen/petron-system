<?php
require_once __DIR__ . '/../model/Fuel.php';

class FuelController {

    public function index() {
        $fuel = new Fuel();
        return $fuel->getAllFuel();
    }

    public function update() {
        if(isset($_POST['update'])){
            $id = $_POST['id'];
            $liters = $_POST['liters'];
            $price = $_POST['price'];
            $status = $_POST['status'];

            $fuel = new Fuel();
            $fuel->updateFuel($id,$liters,$price,$status);

            header("Location: ../views/inventory_list.php");
            exit;
        }
    }

}
?>
