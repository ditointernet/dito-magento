<?php

class Dito_DitoTracking_Helper_Config extends Mage_Core_Helper_Abstract
{
  /**
   * @return boolean
   */
  public function isDitoEnabled($store = null) {
    return (boolean) Mage::getStoreConfig('ditotracking_options/app_config/dito_enabled', $store);
  }

  /**
   * @return string
   */
  public function getApiKey($store = null) {
    return (string) Mage::getStoreConfig('ditotracking_options/app_config/api_key', $store);
  }

  /**
   * @return string
   */
  public function getAppSecret($store = null) {
    return (string) Mage::getStoreConfig('ditotracking_options/app_config/app_secret', $store);
  }

  public function getTrackStatus($key, $store = null) {
    return (boolean) Mage::getStoreConfig('ditotracking_options/track_config/' . $key, $store);
  }

  /**
   * @return string
   */
  public function getUserDataConfig($key, $store = null) {
    return (string) Mage::getStoreConfig('ditotracking_options/user_config/' . $key, $store);
  }

  /**
   * @return boolean
   */
  public function getCacheStrategy($store = null) {
    return (boolean) Mage::getStoreConfig('ditotracking_options/cache_config/cache_strategy', $store);
  }

  /**
   * @return boolean
   */
  public function isNewsEnabled($store = null) {
    return (boolean) Mage::getStoreConfig('ditotracking_options/track_newsletter/dito_newsletter_enabled', $store);
  }

  /**
   * @return string
   */
  public function getNewsDataConfig($key, $store = null) {
    return (string) Mage::getStoreConfig('ditotracking_options/track_newsletter/' . $key, $store);
  }

  /**
   * @return boolean
   */
  public function getIdType($store = null) {
    return (boolean) Mage::getStoreConfig('ditotracking_options/user_config/user_config_id_type', $store);
  }

  /**
   * @return boolean
   */
  public function sendRevenue($store = null) {
    return (boolean) Mage::getStoreConfig('ditotracking_options/track_config/send_revenue', $store);
  }
}