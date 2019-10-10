<?php

namespace local\Helpers;

use Bitrix\Main\EventManager;
use local\Services\attachCV;

class SetEvents
{

    public static function init()
    {

        attachCV::init();

        // Событие происходит при формировании списка закладок
        // В обработчике можно изменить список закладок
        EventManager::getInstance()->addEventHandler(
            'socialnetwork',
            'OnFillSocNetMenu',
            array(
                "local\\Services\\ResRequests\\SocialNetworkServices",
                "__AddSocNetMenu"
            )
        );

        // Событие происходит в комплексном компоненте при работе в ЧПУ
        // режиме при формировании списка шаблонов адресов страниц
        // комплексного компонента
        EventManager::getInstance()->addEventHandler(
            'socialnetwork',
            'OnParseSocNetComponentPath',
            array(
                "local\\Services\\ResRequests\\SocialNetworkServices",
                "__OnParseSocNetComponentPath"
            )
        );

        // Событие происходит при формировании списка дополнительного
        // функционала соц.сети
        // В обработчике можно изменить или дополнить список
        EventManager::getInstance()->addEventHandler(
            'socialnetwork',
            'OnFillSocNetFeaturesList',
            array(
                "local\\Services\\ResRequests\\SocialNetworkServices",
                "__AddSocNetFeature"
            )
        );

        // Событие происходит в комплексном компоненте при работе в
        // не ЧПУ режиме при формировании списка псевдонимов переменных
        EventManager::getInstance()->addEventHandler(
            'socialnetwork',
            'OnInitSocNetComponentVariables',
            array(
                "local\\Services\\ResRequests\\SocialNetworkServices",
                "__OnInitSocNetComponentVariables"
            )
        );

        EventManager::getInstance()->addEventHandler(
            'crm',
            '\Bitrix\Crm\Timeline\Entity\Timeline::OnAfterAdd',
            array(
                "local\\Services\\CrmEventDealTimfors",
                "OnAfterCrmAddEvent"
            )
        );

        EventManager::getInstance()->addEventHandler(
            'crm',
            'OnActivityAdd',
            array(
                "local\\Services\\CrmEventDealTimfors",
                "OnAfterCrmAddEventActivity"
            )
        );






        EventManager::getInstance()->addEventHandler(
            'crm',
            'OnAfterCrmDealProductRowsSave',
            array(
                "local\\Services\\ResourcePlan\\ResrequestsProductIdServices",
                "OnAfterCrmDealProductRowsSave"
            )
        );

        /*EventManager::getInstance()->addEventHandler(
            'sender',
            'OnBeforePostingSendRecipient',
            array(
                "local\\Services\\Recipient",
                "OnAfterPostingSendRecipient"
            )
        );*/

    }

}



