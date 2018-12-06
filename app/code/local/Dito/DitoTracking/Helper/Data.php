<?php
class Dito_DitoTracking_Helper_Data extends Mage_Core_Helper_Abstract 
{

  private function helper() {
    $helper = Mage::helper('ditotracking/config');

    return $helper;
  }

  public function getUserId($customer) {
    $id = '';

    if($this->helper()->getIdType()) {
      $cpf = $customer->getData($this->helper()->getUserDataConfig('user_config_cpf'));

      if(empty($cpf)) {
        return;
      }
      
      $id = $this->limpa_cpf($cpf);
    } else {
      $email = $customer->getEmail();

      if(empty($email)) {
        return;
      }

      $id = sha1($this->validate_email($customer->getEmail()));
    }

    return $id;
  }

  private function validate_email($email) {
    return preg_replace('/\s*/', '', strtolower($email));
  }

  private function limpa_cpf($valor) {
    $valor = preg_replace('/[^0-9]/', '', $valor);
    
    return $valor;
  }

  public function getUserIdentifyObject($customer = null) {
    $customer = ($customer) ? $customer : Mage::getSingleton('customer/session')->getCustomer();

    $user = array();

    if(isset($customer) && $customer->getId()) {
      $addresses = $customer->getAddresses();
      $city = '';
      $birthday = '';
      $phoneFromConfig = $this->helper()->getUserDataConfig('user_config_cellphone');
      $phone = '';
      $id = $this->getUserId($customer);
      $cpfFromConfig = $this->helper()->getUserDataConfig('user_config_cpf');

      if(count($addresses) >= 1){
        $address = reset($addresses);
        if(isset($address)){
          $city = $address->getCity();
          $region = $address->getRegion();
        }
      }
      $cpf = empty($cpfFromConfig) ? '' : $customer->getData($cpfFromConfig);
      $phone = empty($phoneFromConfig) ? '' : $customer->getData($phoneFromConfig);
      $email = $this->validate_email($customer->getEmail());

      if($customer->getDob()){
        $birthday = explode(' ', $customer->getDob())[0];
      }

      if($id) {
        $user = array(
          'id' => $id,
          'email' => $email,
          'name' => $customer->getName(),
          'birthday' => $birthday,
          'gender' => array('', 'male', 'female')[$customer->getGender()],
          'location' => $city,
          'data' => array(
            'user_id' => $customer->getId(),
            'cpf' => $cpf,
            'telefone' => $phone,
            'estado' => $region
          )
        );
      }
    }

    return $user;
  }

  public function getProductTrackingObject($product) {
    if(!$product->getId()){
      return;
    }

    $data = array();
    $categoryIds = $product->getCategoryIds();
    $categories = array();

    if(count($categoryIds)){
      foreach($categoryIds as $categoryId){
        $_category = Mage::getModel('catalog/category')->load($categoryId);
        array_push($categories, $_category->getName());
      }
    }

    $data['id_produto'] = $product->getId();
    $data['sku_produto'] = $product->getSku();
    $data['nome_produto'] = $product->getName();
    $data['categorias_produto'] = join(', ', $categories);
    $data['tipo_produto'] = $product->getTypeId();
    $data['preco_produto'] = floatval($product->getPrice());
    $data['preco_final_produto'] = floatval($product->getFinalPrice());
    $data['preco_especial_produto'] = floatval($product->getSpecialPrice());
    $data['image'] = ($product) ? (string) Mage::helper('catalog/image')->init($product, 'image')->keepFrame(false)->resize(null, 438) : '';

    if($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
      $data['status_produto'] = 'Ativo';
    }
    else {
      $data['status_produto'] = 'Inativo';
    }    

    return $data;
  }
}
