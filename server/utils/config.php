<?php
date_default_timezone_set('Europe/Kiev');

define ( 'DB_HOST', 'localhost' );
define ( 'DB_USER', 'user1' );
define ( 'DB_PASSWORD', 'tuser1' );
define ( 'DB_DB', 'user2');
define ( 'FORMAT_RESPONSE', '.json');

/* Email */

define('TO','mashaod_93@mail.ru');
define('EMAIL','booker');

/* ERROR's */
define('ERR_001','I need params');
define('ERR_002','Incorrect params');
define('ERR_003','Incorrect request');
define('ERR_004','Success');


define('ERR_101','We didn\' find user by this login');
define('ERR_102','Incorrect password');
define('ERR_103','Incorrect hash');
define('ERR_104','Login reserved');
define('ERR_105','Last admin');
define('ERR_106','Reserved');
define('ERR_107',' success operation/-s');
define('ERR_108','Empty');
