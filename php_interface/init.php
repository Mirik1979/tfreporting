<?php

//Подключаем autoload
require_once($_SERVER['DOCUMENT_ROOT'].'/local/vendor/autoload.php');

//Выносим вызов событий в одно меесто
local\Helpers\SetEvents::init();
local\Helpers\SetConst::init();

