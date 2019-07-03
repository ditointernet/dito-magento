<?php

class Dito_DitoTracking_Model_System_Config_Source_View
{

  public function toOptionArray()
  {
    $statuses = Mage::getModel('sales/order_status')->getCollection()->toOptionArray();

    return $statuses;
  }

}