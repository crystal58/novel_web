<?php
namespace YC;
class Sign{
    private $_privateKey = '-----BEGIN RSA PRIVATE KEY-----
        MIICXQIBAAKBgQCwg2ExhmPpPVjHmjJYmNiufMxmLI7Cydw4xvShpG4tlRC5BqJD
s3RJ31u8H9hrbGnogXm0I8BsWeo9PJdA1qsal4+Ve+SeIgktDVHSq0sLUGOn6FrP
ZgKezMX5d7yBQisGoMxPWiirXraR+Un3P7+uyxFfzNpEo1pDuctapdbYowIDAQAB
AoGBAKqDL8fUx/1PWszvAwWdGWxteFKXZo16zLc4Uqc3nYuA/XePlf6Kg6K9FvQD
W2BiiWimcrf29XJ15ZakSoTDGuNpLwMrvdrBcrUC3WSIhzmNeXubpCaCY9XvKFPw
U2ky2TLlNs0phVtJSEXVjBdCOjCeM4v8pXNRckYgqC7e4SrBAkEA3t65bvJ8SxTc
PF2NzV1CLjXJfdVRNCUAjHqHJU+s4RJ1+UwUF57dhW1HSMyfgny1SnAWuF1TCCgn
GcW4Hej64QJBAMrAjj1LtqEAgm8D3zOZSf+AKC0M/L0pBuzilgVSwQRmzHuMLMhF
qklIAFjz6m0AUth0HfYzBIsI7tOuuwDA6AMCQFprqHYP4Ueg7f17w1VHeds/rUDl
M+3g+UkFSSFlIvGpyL1dFWXD6YIBpQMvKaQKLo9FzKH+EvrjN4HoStuvs2ECQQCE
xDCgXMr7hNzkm1F2dWNqZt5oGcAKkioRxpJcbAMuwa4XHQRaJJxMnlRz601QEU0u
pmLUTDYmYTQa86lK5gdHAkBV2N6t2ZPAZu4KHVwCr8Jz9KST6votPfCsgXyC4Jvy
2JNOkfv6y6VXWhmq3a2GhdM9PCiLfPt46YL3N1Cm3B+4
-----END RSA PRIVATE KEY-----';
    private $_publicKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCwg2ExhmPpPVjHmjJYmNiufMxm
LI7Cydw4xvShpG4tlRC5BqJDs3RJ31u8H9hrbGnogXm0I8BsWeo9PJdA1qsal4+V
e+SeIgktDVHSq0sLUGOn6FrPZgKezMX5d7yBQisGoMxPWiirXraR+Un3P7+uyxFf
zNpEo1pDuctapdbYowIDAQAB
-----END PUBLIC KEY-----';
    private $_pubKey;
    private $_priKey;
    const MAX_LEN = 100;
    public function __construct(){

        if(!extension_loaded("openssl")){
            throw new Exception("extension openssl uninstall");
        }
        $this->_pubKey = openssl_pkey_get_public($this->_publicKey);
        $this->_priKey = openssl_pkey_get_private($this->_privateKey); 

        if(!$this->_pubKey || !$this->_priKey){
            throw new Exception ("key is error");
        }
    }


    public  function encryptData($data){
        if(!is_string($data)){
            throw new Exception ("data is not string");    
        }
        if(empty($data)){
            throw new Exception ("data is empty");
        }
        $data = array($data);
        if(strlen($data[0]) > self::MAX_LEN){
            $data = str_split($data,self::MAX_LEN);        
        }
        $encryptData = "";
        foreach($data as $value){
            openssl_public_encrypt($value,$tmp,$this->_pubKey);
            $encryptData .= "||".$tmp;
        }
        
        $result =  base64_encode($encryptData);
        return $result;
    }

    public  function decryptData($data){
        $str = str_replace(" ","+",$data);
        $data = base64_decode($str);
        $data = explode("||",$data);
        $decData = "";
        foreach($data as $value){
            if(empty($value))continue;
            openssl_private_decrypt($value,$tmp,$this->_priKey);
            $decData .= $tmp;   
        }
        return $decData;
    }

}
