<?php

  /**
   * @file
   * Drupal\node_csv_uploader\GoogleMaps.
   */

namespace Drupal\node_csv_uploader;

class GoogleMaps {

  private $results = [];

  private $address = '';

  private $url = 'http://maps.google.com/maps/api/geocode/json';

  public function setAddress($address) {
    $this->address = '?address=' . urlencode($address);
    return $this;
  }

  public function getUrl() {
    return $this->url;
  }

  public function get() {
    if(!empty($this->address)) {
      $this->url = $this->url . $this->address .'&sensor=false';

      $ch = curl_init($this->url);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60);
      curl_setopt($ch, CURLOPT_VERBOSE, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

      $results = curl_exec($ch);
      curl_close($ch);
      if(!empty($results)) {
        $this->results = json_decode($results, 1);
        $this->results = $this->results['results'][0];
      }
    }
    return $this;
  }

  public function getLocation($key) {
    if(!empty($this->results))
      return $this->results['geometry']['location'][$key];
    else
      return NULL;
  }

  public function getLatitude() {
    return $this->getLocation('lat');
  }

  public function getLongitude() {
    return $this->getLocation('lng');
  }

  public function getFormattedAddress() {
    if(!empty($this->results))
      return $this->results['formatted_address'];
  }
}