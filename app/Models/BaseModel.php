<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class BaseModel extends Eloquent
{
    protected $dateFormat = 'Y-m-d H:i:s:u';
    protected $connection = 'mongodb';
}
