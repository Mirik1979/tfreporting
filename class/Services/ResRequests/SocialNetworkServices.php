<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 15.07.2019
 * Time: 21:21
 */

namespace local\Services\ResRequests;

class SocialNetworkServices
{

    // При формировании списка закладок добавим дополнительную
    // закладку для функционала resrequests
    function __AddSocNetMenu(&$arResult)
    {
        // Достуна для показа
        if(array_key_exists("resrequests",$arResult["ActiveFeatures"]))
            $arResult["CanView"]["resrequests"] = true;
        else
            $arResult["CanView"]["resrequests"] = false;

        // Ссылка закладки
        $arResult["Urls"]["resrequests"] = \CComponentEngine::MakePathFromTemplate("/workgroups/group/#group_id#/resrequests/", array("group_id" => $arResult["Group"]["ID"]));
        // Название закладки
        $arResult["Title"]["resrequests"] = "Ресурсные заказы";
    }


    // При формировании списка шаблонов адресов страниц
    // комплексного компонента в режиме ЧПУ добавим шаблон
    // для resrequests
    function __OnParseSocNetComponentPath(&$arUrlTemplates, &$arCustomPagesPath)
    {
        // Шаблон адреса страницы
        $arUrlTemplates["resrequests"] = "group/#group_id#/resrequests/";
        // Путь относительно корня сайта,
        // по которому лежит страница
        $arCustomPagesPath["resrequests"] = "/local/php_interface/templates/";


        $arUrlTemplates["resrequests_details"] = "group/#group_id#/resrequests/details/#deal_id#/";
        $arCustomPagesPath["resrequests_details"] = "/local/php_interface/templates/";

        $arUrlTemplates["resrequests_list"] = "group/#group_id#/resrequests/list/";
        $arCustomPagesPath["resrequests_list"] = "/local/php_interface/templates/";

        $arUrlTemplates["resrequests_category"] = "group/#group_id#/resrequests/category/#category_id#/";
        $arCustomPagesPath["resrequests_category"] = "/local/php_interface/templates/";

    }

    // При формировании списка дополнительного функционала
    // добавим дополнительную запись resrequests
    function __AddSocNetFeature(&$arSocNetFeaturesSettings)
    {
        $arSocNetFeaturesSettings["resrequests"] = array(
            "FeatureName" => "Ресурсный заказ",
            "allowed" => array(SONET_ENTITY_USER, SONET_ENTITY_GROUP),
            "operations" => array(
                "write" => array(SONET_ENTITY_USER => SONET_RELATIONS_TYPE_NONE, SONET_ENTITY_GROUP => SONET_ROLES_MODERATOR),
                "view" => array(SONET_ENTITY_USER => SONET_RELATIONS_TYPE_ALL, SONET_ENTITY_GROUP => SONET_ROLES_USER),
            ),
            "minoperation" => "view",
            "title" => "Ресурсный заказ"
        );
    }

    // Если компонент соц.сети работает в режиме
    // ЧПУ, то этот обработчик не нужен
    function __OnInitSocNetComponentVariables(&$arVariableAliases, &$arCustomPagesPath)
    {

    }

}