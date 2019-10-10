<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $USER;
$ext = 0;
\Bitrix\Main\Diag\Debug::writeToFile("mir");
\Bitrix\Main\Diag\Debug::writeToFile($arResult);
//print_r($arResult["ITEMS"]);
if(!$USER->IsAdmin() && $arResult['ENTITY_TYPE_ID']==2) {
    $id = $USER->GetID();
    $group = $USER->GetUserGroup($id);
    foreach ($group as $groupv) {
        if ($groupv == 14) {
            $ext = 1;
        }

    }
    if ($ext==1) {
        unset($arResult["TABS"][1]);
        unset($arResult["TABS"][2]);
        unset($arResult["TABS"][3]);
        unset($arResult["TABS"][4]);
        unset($arResult["TABS"][5]);
        if ($arResult['EDITOR']['ENTITY_FIELDS'][5]['data']['items'][1]['VALUE']=="C1:10") {
            unset($arResult["TABS"][0]);
        }
    } else {
        unset($arResult["TABS"][0]);
        unset($arResult["TABS"][1]);
        unset($arResult["TABS"][2]);
        unset($arResult["TABS"][3]);
        unset($arResult["TABS"][4]);
        unset($arResult["TABS"][5]);
        if (\Bitrix\Main\Loader::includeModule('crm')) {
            $res = CCrmDeal::GetList(array("ID" => ASC), array("ID" => $arResult['ENTITY_ID']));
            while ($ob = $res->GetNext()) {
                $stage = substr($ob['STAGE_ID'], 0, 1);
                if ($stage == 'C') {
                    unset($arResult["TABS"][7]);
                }
            }
        }
    }
}






