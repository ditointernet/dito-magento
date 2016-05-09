<?php
class Dito_DitoTracking_IndexController extends Mage_Core_Controller_Front_Action {
  public function identifyAction() {
    $block = $this->getLayout()->createBlock('core/template');
    $block->setTemplate('ditotracking/identify.phtml');
    echo $block->toHtml();
  }
}
