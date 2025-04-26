<?php

namespace App\Models;

use \PDO;
use stdClass;

class ChatModel extends SqlConnect {
  private $table = "messages";
  public function __construct() {
    parent::__construct();
  }
  
}