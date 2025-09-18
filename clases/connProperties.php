<?php

    OB_START() ;
	SESSION_START() ;

    ini_set( 'display_errors' , 1 ) ;
    ini_set( 'display_startup_errors' , 1 ) ;
    error_reporting( E_ALL ) ;
    date_default_timezone_set( "America/Cancun" ) ;

    class Properties {

        public static function Db() {
            return array(
                'host' => 'localhost', // Eg: localhost
                'user' => 'root', // Eg: root
                'password' => '',
                'database' => 'classicmodels',
                'mysqlport' => 3306    // or any port
            ) ;
        }

        public function baseUrl() {
            return "" ;
        }


        // public function validateEmail( $email ) {
        //     return false !== filter_var( $email , FILTER_VALIDATE_EMAIL ) ;
        // }

        public function __clone() {
            trigger_error( 'La clonación de este objeto no está permitida' , E_USER_ERROR ) ;
        }

    }

    $props = new Properties() ;