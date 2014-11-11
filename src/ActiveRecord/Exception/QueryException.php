<?php

namespace Solution10\ORM\ActiveRecord\Exception;

use Exception;

class QueryException extends Exception
{
    const UNKNOWN_MODEL = 0;
    const BAD_JOIN_TYPE = 1;
}
