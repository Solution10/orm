<?php

namespace Solution10\ORM\ActiveRecord\Exception;

use Exception;

class QueryException extends Exception
{
    const UNKNOWN_MODEL = 0;
    const BAD_JOIN_TYPE = 1;
    const CONNECTION_MISSING = 2;
    const MISSING_ALIAS = 3;
}
