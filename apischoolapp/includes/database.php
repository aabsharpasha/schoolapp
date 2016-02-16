<?php
Class database {
    public $dbh;
    private static $instance;
    function __construct() {
        $user = 'root';
        $pass = '';
        
//        $user = 'neshornt_vivek';
//        $pass = 'vivek@123';
        
        try {
            $this->dbh = new PDO('mysql:host=localhost;dbname=neshornt_schoolapp', $user, $pass);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }
    
    static function set_instance() {
      if(null === static::$instance) {
          static::$instance = new static();
      }  
      
      return static::$instance;
    }
}
?>
