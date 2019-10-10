<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 22.09.2019
 * Time: 16:32
 */

namespace local\Services;

use Bitrix\Sender\Entity\Letter;
use Bitrix\Main\Diag;

class Recipient
{

    /**
     * @param array $eventData
     */
    function OnAfterPostingSendRecipient($eventData){

        Diag\Debug::writeToFile("OnAfterPostingSendRecipient");
        Diag\Debug::writeToFile($eventData);

    }
}