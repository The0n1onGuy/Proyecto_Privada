<?php

    class connectionDB {

            private $connectionConfig ;

            public function __construct() {
                $this->connectionConfig = Properties::Db() ;
            }

            public function connect() {
                $host = $this->connectionConfig['host'] ;
                $database = $this->connectionConfig['database'] ;
                $dsn = "mysql:host=$host;dbname=$database" ;

                try {
                    $pdo = new PDO( $dsn , $this->connectionConfig[ 'user' ] , $this->connectionConfig[ 'password'] ) ;
                    $pdo->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION ) ;

                    if( $pdo ) {
                        // echo "Connected..." ;
                        return $pdo ;
                    }
                } catch( PDOException $e ) {
                    echo "ErrorCode: " . $e->getCode() ;
                    echo "<br/>" ;
                    echo $e->getMessage() ;
                }
            }

            public function __clone() {
                trigger_error( 'La clonación de este objeto no está permitida' , E_USER_ERROR ) ;
            }

    }

    // $connC = new connectionDB() ;