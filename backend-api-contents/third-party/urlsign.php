<?php

namespace Urlsign;

// stream host "d3vj8ggx5v2llu.cloudfront.net"
// download host "duxvmik4bpmng.cloudfront.net"
class Urlsign {
  private $__private_key;

  function __construct(string $secretPrivateKey) {
    $this->__private_key = $secretPrivateKey;
  }

  protected function _rsa_sha1_sign($policy){
    if ($this->__private_key == null) {
      return false;
    }
    $signature = "";
    $pkeyid = openssl_get_privatekey($this->__private_key);
    openssl_sign($policy, $signature, $pkeyid);
    openssl_free_key($pkeyid);
    return $signature;
  }

  protected function _url_safe_base64_encode($value) {
    $encoded = base64_encode($value);
    return str_replace(
      array('+', '=', '/'),
      array('-', '_', '~'),
      $encoded);
  }

  protected function _combine_policy($url, $policy, $signature) {
    $result = $url;
    $separator = strpos($url, '?') == FALSE ? '?' : '&';
    $result .= $separator . "Policy=" . $policy . "&Signature=" . $signature;
    return str_replace('\n', '', $result);
  }

  # operation functions

  function getSignedUrl($url, $policy){
    $awsPolicy = array(
      'Resource' => $policy['Resource'],
      'Condition' => [
        'IpAddress' => $policy['SourceIp'].'/'.$policy['IpSubnet'],
        'DateLessThan' => [
          'EpochTime' => time() + $policy['ExpireTimeSec'],
        ],
      ],
    );

    $_json_awsPolicy = json_encode($awsPolicy);
    $encoded_policy = $this->_url_safe_base64_encode($_json_awsPolicy);
    $signature = $this->_rsa_sha1_sign($_json_awsPolicy);
    if ($signature == false) {
      return false;
    }
    $encoded_signature = $this->_url_safe_base64_encode($signature);
    return $this->_combine_policy($url, $encoded_policy, $encoded_signature );
  }
}
