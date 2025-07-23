<?php
class Cart {
    private $items = [];

    public function __construct() {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $this->items = &$_SESSION['cart'];
    }

    public function addItem($product_id, $quantity = 1) {
        if (isset($this->items[$product_id])) {
            $this->items[$product_id] += $quantity;
        } else {
            $this->items[$product_id] = $quantity;
        }
        return true;
    }

    public function updateItem($product_id, $quantity) {
        if ($quantity <= 0) {
            unset($this->items[$product_id]);
        } else {
            $this->items[$product_id] = $quantity;
        }
        return true;
    }

    public function removeItem($product_id) {
        unset($this->items[$product_id]);
        return true;
    }

    public function getItems() {
        return $this->items;
    }

    public function clear() {
        $this->items = [];
        $_SESSION['cart'] = [];
    }

    public function countItems() {
        return array_sum($this->items);
    }

    public function isEmpty() {
        return empty($this->items);
    }
}
?>