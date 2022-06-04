<?php
namespace miniShop\Controller;


use miniShop\App;
use miniShop\Model\BasketModel;
use miniShop\Model\OrderModel;
use miniShop\Model\ProductModel;
use miniShop\Model\UserModel;

class Orders
{



    private $productModel;
    private $userModel;
    private $basketModel;
    private $orderModel;
    public function __construct()
    {
        $this->productModel  = new ProductModel();
        $this->userModel  = new UserModel();
        $this->basketModel  = new BasketModel();
        $this->orderModel  = new OrderModel();
        $this->userModel->loginByAuthorizationToken(true);

    }

    public function Register($json) {
        $basket = $this->basketModel->getBasket(UserModel::$userInstance['userID']);

        if(count($basket) === 0 ) {
            App::out("Basket is null");
        }
        $orderAmount = 0 ;
        foreach ($basket as $item) {
            $product = $this->productModel->GetProductByID($item['productID']);
            if($item['productQuantity'] > $product['quantity']) {
                App::out($product['productName'] . " quantity is less than " . $item['productQuantity']);
            }
            $orderAmount += $product['price'] * $item['productQuantity'];
        }

        $orderID = $this->orderModel->Register(UserModel::$userInstance['userID'], $orderAmount);

        foreach ($basket as $item) {
            $product = $this->productModel->GetProductByID($item['productID']);
            $this->orderModel->RegisterOrderProducts($orderID , $product['productID'], $item['productQuantity'], $product['price']);
        }

        $this->basketModel->clearBasket(UserModel::$userInstance['userID']);

        App::out($this->orderModel->GetOrderByID($orderID), 200);
    }

    public function GetOrderByID($json) {
        App::hasKeys($json , ['orderID']);
        $order = $this->orderModel->GetOrderByID($json->orderID);
        if($order == null || $order['userID'] != UserModel::$userInstance['userID']) {
            App::out("order not found");
        }
        App::out($order, 200);
    }

    public function GetOrderByIDWithProducts($json) {
        App::hasKeys($json , ['orderID']);
        $order = $this->orderModel->GetOrderByID($json->orderID);
        if($order == null || $order['userID'] != UserModel::$userInstance['userID']) {
            App::out("order not found");
        }

        $products = $this->orderModel->GetOrderProducts($json->orderID);

        $productArray = [];
        foreach ($products as $product) {
            $product['productModel'] = $this->productModel->GetProductByID($product['productID']);
            $productArray[] = $product;
        }
        $order['products'] = $productArray;
        App::out($order, 200);
    }


}