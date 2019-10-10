<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>


<?
$TimforsCompany=false;
$TimforsCompanyUser=[];

if($arResult["VARIABLES"]["group_id"]>0 && CModule::IncludeModule('crm')){

    $arGroup = CSocNetGroup::GetByID($arResult["VARIABLES"]["group_id"]);
    $TimforsCompany=$arGroup["UF_TIMFORS_COMPANY"];
    $dbRequests = CSocNetUserToGroup::GetList(
        array("USER_LAST_NAME" => "ASC", "USER_NAME" => "ASC"),
        array(
            "GROUP_ID" => $arResult["VARIABLES"]["group_id"],
            //"<=ROLE" => SONET_ROLES_MODERATOR,
            "USER_ACTIVE" => "Y"
        ),
        false,
        $arNavParams,
        array("ID", "USER_ID", "DATE_CREATE", "DATE_UPDATE", "USER_NAME", "USER_LAST_NAME", "USER_PERSONAL_PHOTO")
    );

    while ($arRequests = $dbRequests->GetNext())
        if(!in_array($arRequests["USER_ID"],$TimforsCompanyUser))
            $TimforsCompanyUser[]=$arRequests["USER_ID"];

}
?>



<? $APPLICATION->IncludeComponent(
    "bitrix:socialnetwork.group_menu",
    "",
    Array(
        "GROUP_VAR" => $arResult["ALIASES"]["group_id"],
        "PAGE_VAR" => $arResult["ALIASES"]["page"],
        "PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
        "PATH_TO_GROUP_MODS" => $arResult["PATH_TO_GROUP_MODS"],
        "PATH_TO_GROUP_USERS" => $arResult["PATH_TO_GROUP_USERS"],
        "PATH_TO_GROUP_EDIT" => $arResult["PATH_TO_GROUP_EDIT"],
        "PATH_TO_GROUP_REQUEST_SEARCH" => $arResult["PATH_TO_GROUP_REQUEST_SEARCH"],
        "PATH_TO_GROUP_REQUESTS" => $arResult["PATH_TO_GROUP_REQUESTS"],
        "PATH_TO_GROUP_REQUESTS_OUT" => $arResult["PATH_TO_GROUP_REQUESTS_OUT"],
        "PATH_TO_GROUP_BAN" => $arResult["PATH_TO_GROUP_BAN"],
        "PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
        "PATH_TO_GROUP_PHOTO" => $arResult["PATH_TO_GROUP_PHOTO"],
        "PATH_TO_GROUP_FORUM" => $arResult["PATH_TO_GROUP_FORUM"],
        "PATH_TO_GROUP_CALENDAR" => $arResult["PATH_TO_GROUP_CALENDAR"],
        "PATH_TO_GROUP_FILES" => $arResult["PATH_TO_GROUP_FILES"],
        "PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
        "GROUP_ID" => $arResult["VARIABLES"]["group_id"],
        "PAGE_ID" => "group_superficha",
    ),
    $component
); ?>


<?$APPLICATION->IncludeComponent("bitrix:crm.deal", "resrequests", Array(

    "TIMFORS_COMPANY"=>$TimforsCompany,
    "TIMFORS_COMPANY_USER"=>$TimforsCompanyUser,

	"SEF_MODE" => "Y",	// Включить поддержку ЧПУ
		"PATH_TO_CONTACT_SHOW" => "/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/crm/contact/edit/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/crm/company/show/#company_id#/",
		"PATH_TO_COMPANY_EDIT" => "/crm/company/edit/#company_id#/",
		"PATH_TO_INVOICE_SHOW" => "/crm/invoice/show/#invoice_id#/",
		"PATH_TO_INVOICE_EDIT" => "/crm/invoice/edit/#invoice_id#/",
		"PATH_TO_LEAD_SHOW" => "/crm/lead/show/#lead_id#/",
		"PATH_TO_LEAD_EDIT" => "/crm/lead/edit/#lead_id#/",
		"PATH_TO_LEAD_CONVERT" => "/crm/lead/convert/#lead_id#/",
		"PATH_TO_USER_PROFILE" => "/company/personal/user/#user_id#/",
		"PATH_TO_PRODUCT_EDIT" => "/crm/product/edit/#product_id#/",
		"PATH_TO_PRODUCT_SHOW" => "/crm/product/show/#product_id#/",
		"ELEMENT_ID" => $arResult["VARIABLES"]["deal_id"],	// ID сделки
    "GroupId" => $arResult["VARIABLES"]["group_id"],
		"SEF_FOLDER" => SITE_DIR."workgroups/group/{$arResult["VARIABLES"]["group_id"]}/resrequests/",	// Каталог ЧПУ (относительно корня сайта)
		"SEF_URL_TEMPLATES" => array(
			"index" => "index.php",
			"list" => "list/",
			"edit" => "edit/#deal_id#/",
			"show" => "show/#deal_id#/",
		),
		"VARIABLE_ALIASES" => array(
			"index" => "",
			"list" => "",
			"edit" => "",
			"show" => "",
		)
	),
    $component
);?>

<script>



    BX.SidePanel.Instance.bindAnchors({
        rules: [
            {
                condition: [ new RegExp("<?=SITE_DIR?>workgroups/group/<?=$arResult["VARIABLES"]["group_id"]?>/resrequests/details/[0-9]+/", "i") ],
                loader: "crm-entity-details-loader",
                options: { cacheable: false }
            }
        ]
    });



</script>

