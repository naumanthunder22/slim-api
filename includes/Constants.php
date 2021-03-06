<?php

//
//-------------------------------Database Parameters-------------------
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_NAME', 'lapcs');


//-------------------------------User Defines Constants-----------------

//Constants for User Account Management Module
define('USER_CREATED', 601);
define('USER_EXISTS', 602);
define('USER_FAILURE', 603);

define('USER_AUTHENTICATED', 604);
define('USER_NOT_FOUND', 605);
define('USER_PASSWORD_DO_NOT_MATCH', 606);

define('PASSWORD_CHANGED', 607);
define('PASSWORD_DO_NOT_MATCH', 608);
define('PASSWORD_NOT_CHANGED', 609);

define('USER_DELETED', 610);
define('USER_NOT_DELETED', 611);

define('USER_UPDATED', 612);
define('USER_NOT_UPDATED', 613);

define('LINKED_USER_CREATED', 614);
define('LINKED_USER_FAILURE', 615);

define('LINKED_CHILD_USER_CREATED', 616);
define('LINKED_CHILD_USER_FAILURE', 617);

define('CHILD_USER_CREATED', 618);
define('CHILD_USER_FAILURE', 619);

//-------------------------------HTTP Status Codes---------------------

define('OK',200);
define('CREATED',201);
define('NOT_MODIFIED',304);
define('BAD_REQUEST',400);
define('UNAUTHORIZED',401);
define('FORBIDDEN',403);
define('NOT_FOUND',404);
define('CONFLICT',409);
define('UNPROCESSABLE_ENTITY',422);
define('INTERNAL_SERVER_ERROR',500);
define('SERVICE_UNAVAILABLE',503);
