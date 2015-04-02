<?php
 
class Dito_DitoTracking_Model_Observer {
  public function logCartAdd() {
    $helper = Mage::helper('ditotracking');

    $product = Mage::getModel('catalog/product')->load(Mage::app()->getRequest()->getParam('product', 0));
    $data = $helper->getProductTrackingObject($product);

    $data['quantidade'] = floatval(Mage::app()->getRequest()->getParam('qty', 1));
 
    Mage::getModel('core/session')->setProductToShoppingCart(json_encode($data));
  }
}