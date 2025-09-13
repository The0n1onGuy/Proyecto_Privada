<?php
class WhatsApp {
     function getDBPrefix( $id, $tabla ){
       $prefix = "" ;
       switch ($id) {
        case 1:  $prefix = "vtas_$tabla"; break;
        case 2:  $prefix = "admin_$tabla"; break;
        default:  $prefix = "INVALIDO"; break;
       }

       return $prefix;

    }

    function getwhatsApplink( $texto ){
        return $this->getwhatsAppURL() . $this->getwhatsAppnumber() . $this->whatsApptext($texto);
    }

    function getwhatsAppnumber() {
        return "9988958045";
    }

    function getwhatsAppURL(){
        return "https://wa.me/";
    }

    function whatsApptext($texto){
        return "?text=$texto";
    }

}

$Starter = new WhatsApp();