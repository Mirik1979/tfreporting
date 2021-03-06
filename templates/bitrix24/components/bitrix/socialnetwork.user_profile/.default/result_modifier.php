<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (
	!$arResult["FatalError"]
	&& !$arResult["CurrentUserPerms"]["Operations"]["viewprofile"]
)
{
	$arResult["FatalError"] = GetMessage("SONET_P_USER_ACCESS_DENIED");
}

if (!$arResult["FatalError"])
{
	global $USER;
	$arResult['CAN_EDIT_USER'] = (
		$arResult["CurrentUserPerms"]["Operations"]["modifyuser"]
		&& $arResult["CurrentUserPerms"]["Operations"]["modifyuser_main"]
		//&& $arResult["User"]["EXTERNAL_AUTH_ID"] != 'email'
	);

	if(!IsModuleInstalled("bitrix24") && CModule::IncludeModule("socialnetwork") && $USER->IsAdmin($USER->GetID())
	)
	{
		$arResult['CAN_EDIT_USER'] = $arResult['CAN_EDIT_USER'] && CSocNetUser::IsCurrentUserModuleAdmin();
	}

	// subordinate
	if((!CModule::IncludeModule("extranet") || !CExtranet::IsExtranetSite() || CExtranet::IsIntranetUser()) && CModule::IncludeModule("iblock")
	)
	{
		$subordinate_users = array();
		if(is_array($arResult["DEPARTMENTS"]))
		{
			foreach($arResult["DEPARTMENTS"] as $key => $dep)
			{
				$dbUsers = CUser::GetList($o = "", $b = "", array(
						"!ID" => $arResult["User"]["ID"],
						'UF_DEPARTMENT' => $dep["ID"],
						'ACTIVE' => 'Y',
						'CONFIRM_CODE' => false
					), array('FIELDS' => array("ID", "NAME", "LAST_NAME", "SECOND_NAME", "LOGIN", "WORK_POSITION")));

				while($arRes = $dbUsers->GetNext())
				{
					$subordinate_users[$arRes["ID"]] = $arRes;
				}
			}
		}
		$arResult["SUBORDINATE"] = $subordinate_users;
	}

	// user activity status
	if($arResult["User"]["ACTIVE"] == "Y")
	{
		$arResult["User"]["ACTIVITY_STATUS"] = "active";
	}

	$obUser = new CUser();
	$arGroups = $obUser->GetUserGroup($arResult["User"]['ID']);
	if(in_array(1, $arGroups))
	{
		$arResult["User"]["ACTIVITY_STATUS"] = "admin";
	}

	if (IsModuleInstalled("bitrix24") && \CBitrix24::isIntegrator($arResult["User"]['ID']))
	{
		$arResult["User"]["ACTIVITY_STATUS"] = "integrator";
	}
	if(
		!is_array($arResult["User"]['UF_DEPARTMENT'])
		|| empty($arResult["User"]['UF_DEPARTMENT'][0])
	)
	{
		$arResult["User"]["ACTIVITY_STATUS"] = "extranet";
		$arResult["User"]["IS_EXTRANET"] = true;
	}
	else
	{
		$arResult["User"]["IS_EXTRANET"] = false;
	}

	if($arResult["User"]["ACTIVE"] == "N")
	{
		$arResult["User"]["ACTIVITY_STATUS"] = "fired";
	}

	if (
		$arResult["User"]["ACTIVE"] == "Y"
		&& !empty($arResult["User"]["CONFIRM_CODE"])
	)
	{
		$arResult["User"]["ACTIVITY_STATUS"] = "invited";
	}

	if ($arResult["User"]["EXTERNAL_AUTH_ID"] == "email")
	{
		$arResult["User"]["ACTIVITY_STATUS"] = "email";
	}

	if(
		$arResult["User"]["ID"] == $GLOBALS["USER"]->GetID()
		&& CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
		&& !isset($_SESSION["SONET_ADMIN"])
	)
	{
		$arResult["SHOW_SONET_ADMIN"] = true;
	}
	// competences
    $roleid = $arResult["User"]["UF_ROLE"];
    $rolename = CIBlockElement::GetByID($roleid);
    if($ar_res = $rolename->GetNext())
        $arResult["role"] = $ar_res['NAME'];
    $gradeid = $arResult["User"]["UF_GRADE"];
    $gradename = CIBlockElement::GetByID($gradeid);
    if($ar_res = $gradename->GetNext())
        $arResult["grade"] = $ar_res['NAME'];
    $expid = $arResult["User"]["UF_EXPERIENCE"];
    $expname = CIBlockElement::GetByID($expid);
    if($ar_res = $expname->GetNext())
        $arResult["exp"] = $ar_res['NAME'];
    $prodid = $arResult["User"]["UF_PROD"];
    foreach ($prodid as $value) {
        $prodname = CIBlockElement::GetByID($value);
        if($ar_res = $prodname->GetNext())
            if ($arResult["prod"])
                $arResult["prod"] = $arResult["prod"] . "," . $ar_res['NAME'];
            else
                $arResult["prod"] = $ar_res['NAME'];
    }
    $moduleid = $arResult["User"]["UF_MODULE"];
    foreach ($moduleid as $value2) {
        $modname = CIBlockElement::GetByID($value2);
        if($ar_res = $modname->GetNext())
            if ($arResult["mod"])
                $arResult["mod"] = $arResult["mod"] . "," . $ar_res['NAME'];
            else
                $arResult["mod"] = $ar_res['NAME'];
    }
}

if (\Bitrix\Main\Loader::includeModule("security"))
{
	$arResult["IS_OTP_RECOVERY_CODES_ENABLE"] = \Bitrix\Security\Mfa\Otp::isRecoveryCodesEnabled();
}
?>