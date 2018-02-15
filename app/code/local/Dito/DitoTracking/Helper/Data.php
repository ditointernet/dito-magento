<?php
class Dito_DitoTracking_Helper_Data extends Mage_Core_Helper_Abstract {
  public function getDitoEnabled($store = null){
    return Mage::getStoreConfig('ditotracking_options/app_config/dito_enabled', $store);
  }

  public function getApiKey($store = null){
    return Mage::getStoreConfig('ditotracking_options/app_config/api_key', $store);
  }

  public function getTrackStatus($key, $store = null){
    return Mage::getStoreConfig('ditotracking_options/track_config/' . $key, $store);
  }

  public function getUserDataConfig($key, $store = null){
    return Mage::getStoreConfig('ditotracking_options/user_config/' . $key, $store);
  }

  public function getCacheStrategy($store = null){
    return Mage::getStoreConfig('ditotracking_options/cache_config/cache_strategy', $store);
  }

  public function getUserIdentifyObject($customer){
    $user = Array();

    if(isset($customer) && $customer->getId()) {
      $addresses = $customer->getAddresses();
      $city = '';
      $birthday = '';
      $phoneFromAddress = '';
      $phoneFromConfig = $customer->getData($this->getUserDataConfig('user_config_cellphone'));
      $phone = '';

      if(count($addresses) >= 1){
        $address = reset($addresses);
        if(isset($address)){
          $city = $address->getCity();
          $phoneFromAddress = $address->getTelephone();
        }
      }

      $phone = isset($phoneFromConfig) ? $phoneFromConfig : $phoneFromAddress;
      $email = preg_replace('/\s*/', '', strtolower($customer->getEmail());

      if($customer->getDob()){
        $birthday = split(' ', $customer->getDob())[0];
      }

      $user = Array(
        'id' => sha1($email),
        'email' => $email,
        'name' => $customer->getName(),
        'birthday' => $birthday,
        'gender' => array('', 'male', 'female')[$customer->getGender()],
        'location' => $city,
        'data' => array(
          'user_id' => $customer->getId(),
          'cpf' => $customer->getData($this->getUserDataConfig('user_config_cpf')),
          'telefone' => $phone
        )
      );
    }

    return $user;
  }

  public function getProductTrackingObject($product) {
    if(!$product->getId()){
      return;
    }

    $data = Array();

    $categoryIds = $product->getCategoryIds();
    $categories = Array();

    if(count($categoryIds)){
      foreach($categoryIds as $categoryId){
        $_category = Mage::getModel('catalog/category')->load($categoryId);
        array_push($categories, $_category->getName());
      }
    }

    if($product->getId()){
      $data['id_produto'] = $product->getId();
    }

    if(count($categories)){
      $data['categorias_produto'] = join(', ', $categories);
    }

    if($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
      $data['status_produto'] = 'Ativo';
    }
    else {
      $data['status_produto'] = 'Inativo';
    }

    if($product->getTypeId()){
      $data['tipo_produto'] = $product->getTypeId();
    }

    if($product->getPrice()){
      $data['preco_produto'] = floatval($product->getPrice());
    }

    if($product->getFinalPrice()){
      $data['preco_final_produto'] = floatval($product->getFinalPrice());
    }

    if($product->getSpecialPrice()){
      $data['preco_especial_produto'] = floatval($product->getSpecialPrice());
    }

    if($product->getName()){
      $data['nome_produto'] = $product->getName();
    }

    if($product->getSku()){
      $data['sku_produto'] = $product->getSku();
    }

    return $data;
  }
}
