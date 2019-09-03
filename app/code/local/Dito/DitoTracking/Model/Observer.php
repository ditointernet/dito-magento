<?php
 
class Dito_DitoTracking_Model_Observer 
{

  public function logCartAdd() {
    $helper = Mage::helper('ditotracking');

    $product = Mage::getModel('catalog/product')->load(Mage::app()->getRequest()->getParam('product', 0));
    $data = $helper->getProductTrackingObject($product);

    $data['quantidade'] = floatval(Mage::app()->getRequest()->getParam('qty', 1));
 
    Mage::getModel('core/session')->setProductToShoppingCart(json_encode($data));
  }

  private function getOrderData($order) {
    if(!$this->isEnabled()) {
      return;
    }

    $orderdata = array();
    $productsData = $this->getProductsFromOrder($order);
    $customer_id = $order->getCustomerId();
    $customer = Mage::getModel('customer/customer')->load($customer_id);
    $customerData = $this->helper()->getUserIdentifyObject($customer);
    $user_data = array();
    $payment = $order->getPayment();

    $orderdata['id_compra'] = $order->getIncrementId();
    $orderdata['total'] = round(floatval($order->getGrandTotal()), 2);
    $orderdata['subtotal'] = round(floatval($order->getSubtotal()), 2);
    $orderdata['total_desconto'] = round(floatval($order->getDiscountAmount()), 2);
    $orderdata['total_frete'] = round(floatval($order->getShippingAmount()), 2);
    $orderdata['tipo_frete'] = round(floatval($order->getShippingMethod()), 2);
    $orderdata['quantidade_produtos'] = floatval($order->getTotalQtyOrdered());
    $orderdata['metodo_pagamento'] = $payment->getMethod();

    $user_data['name'] = $customerData['name'];
    $user_data['email'] = $customerData['email'];
    $user_data['gender'] = $customerData['gender'];
    $user_data['location'] = $customerData['location'];
    $user_data['birthday'] = $customerData['birthday'];
    $user_data['data'] = json_encode($customerData['data']);

    return array($orderdata, $productsData, $user_data, $customer);
  }

  private function getProductsFromOrder($order) {
    if(!$this->isEnabled()) {
      return;
    }

    $productsData = array();
    foreach ($order->getAllVisibleItems() as $item) {
      $_Product = $item->getProduct();
      $parent = $_Product->getParentItem();
      if ($parent) {
        $productsData[] = $this->helper()->getProductTrackingObject($parent);
      } else {
        $productsData[] = $this->helper()->getProductTrackingObject($_Product);
      }
    }

    return $productsData;
  }

  private function sendOrderDataToServer($orderData, $customer, $action = 'fez-pedido') {
    if (!$this->isEnabled()) {
      return;
    }

    try {
      $app_key = $this->config()->getApiKey();
      $app_secret = $this->config()->getAppSecret();
      $reference = $this->helper()->getUserId($customer);
      $revenue = NULL;
      
      if ($action == 'comprou') {
        $revenue = $orderData['total'];
      }

      $dito = new Dito_DitoTracking_Model_Utilities_DitoIns($app_key, $app_secret, $reference);

      $dito->setCallbacks(function($obj) {
        error_log("DITOPHPSDK ERROR - wrong request:" . json_encode($obj->getLastRequest()));
        Mage::log("DITOPHPSDK ERROR - wrong request:" . json_encode($obj->getLastRequest()));
      }, function() {

      });
      $message_id = sha1($app_key . $orderData['id_compra'] . time());
      $event = array(
        'action' => $action,
        'revenue' => $revenue,
        'data' => json_encode($orderData)
      );

      $dito->prepare_event(
        $message_id,
        $event
      )->send();
    } catch(Exception $e) {
      error_log("DITOPHPSDK ERROR:" . $e->getMessage() . ' more :' . json_encode($e));
      Mage::log("DITOPHPSDK ERROR:" . $e->getMessage() . ' more :' . json_encode($e));
    }

    return json_encode($dito->getLastRequest());
  }

  private function sendProductsDataToServer($orderData, $productsData, $customer, $action = 'fez-pedido-produto') {
    if (!$this->isEnabled()) {
      return;
    }

    try {
      $app_key = $this->config()->getApiKey();
      $app_secret = $this->config()->getAppSecret();
      $reference = $this->helper()->getUserId($customer);

      $dito = new Dito_DitoTracking_Model_Utilities_DitoIns($app_key, $app_secret, $reference);

      $dito->setCallbacks(function($obj) {
        error_log("DITOPHPSDK ERROR - wrong request:" . json_encode($obj->getLastRequest()));
        Mage::log("DITOPHPSDK ERROR - wrong request:" . json_encode($obj->getLastRequest()));
      }, function() {

      });
      foreach($productsData as $product) {
        $message_id = sha1($app_key . $orderData['id_compra'] . $product['id_produto'] . time());
        $product['id_compra'] = $orderData['id_compra'];
        $event = array(
          'action' => $action,
          'data' => json_encode($product)
        );

        $dito->prepare_event(
          $message_id,
          $event
        )->send();
      }
      
    } catch(Exception $e) {
      error_log("DITOPHPSDK ERROR:" . $e->getMessage() . ' more :' . json_encode($e));
      Mage::log("DITOPHPSDK ERROR:" . $e->getMessage() . ' more :' . json_encode($e));
    }

    return json_encode($dito->getLastRequest());
  }

  private function sendIdentifyToServer($user_data, $customer) {
    if (!$this->isEnabled()) {
      return;
    }

    try {
      $app_key = $this->config()->getApiKey();
      $app_secret = $this->config()->getAppSecret();
      $reference = $this->helper()->getUserId($customer);
      $dito = new Dito_DitoTracking_Model_Utilities_DitoIns($app_key, $app_secret, $reference);

      $dito->setCallbacks(function($obj) {
        error_log("DITOPHPSDK ERROR - wrong request:" . json_encode($obj->getLastRequest()));
        Mage::log("DITOPHPSDK ERROR - wrong request:" . json_encode($obj->getLastRequest()));
      }, function() {

      });

      $dito->prepare_identify(
        $user_data
      )->send();
      
    } catch(Exception $e) {
      error_log("DITOPHPSDK ERROR:" . $e->getMessage() . ' more :' . json_encode($e));
      Mage::log("DITOPHPSDK ERROR:" . $e->getMessage() . ' more :' . json_encode($e));
    }

    return json_encode($dito->getLastRequest());
  }

  public function export_new_order($observer) {
    if (!$this->isEnabled()) {
      return;
    }

    $order = $observer->getEvent()->getOrder();
    $orderData = $this->getOrderData($order);
    $this->sendIdentifyToServer($orderData[2], $orderData[3]);
    $this->sendOrderDataToServer($orderData[0], $orderData[3]);
    $this->sendProductsDataToServer($orderData[0], $orderData[1], $orderData[3]);

    return $this;
  }

  public function check_order_status($observer)
  {
    if (!$this->isEnabled() || !$this->sendRevenue()) {
      return;
    }

    $order = $observer->getEvent()->getOrder();
    $orderStatus = $order->getStatus();
    $statusChoosed = $this->config()->getOrderStatus();

    if ($orderStatus == $statusChoosed)
    {
      $orderData = $this->getOrderData($order);
      $this->sendIdentifyToServer($orderData[2], $orderData[3]);
      $this->sendOrderDataToServer($orderData[0], $orderData[3], 'comprou');
      $this->sendProductsDataToServer($orderData[0], $orderData[1], $orderData[3], 'comprou-produto');
    }

    return $this;
  }

  private function isEnabled() {
    $isEnabled = $this->config()->isDitoEnabled();

    return $isEnabled;
  }

  private function sendRevenue() {
    $sendRevenue = $this->config()->sendRevenue();

    return $sendRevenue;
  }

  private function config() {
    $config = Mage::helper('ditotracking/config');

    return $config;
  }

  private function helper() {
    $helper = Mage::helper('ditotracking/data');

    return $helper;
  }
}