<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;

$resresult = Array();

$from = ($_REQUEST['datebegin'])?$_REQUEST['datebegin']:date("d.m.Y");
$to = ($_REQUEST['dateend'])?$_REQUEST['dateend']:date("d.m.Y");

if (!Loader::includeModule('crm')) {
    echo "CRM модуль не установлен";
}

///echo "plus";


$arFilter = Array("IBLOCK_ID"=>32, "ACTIVE_DATE"=>"Y",
    ">=DATE_CREATE" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
    "<=DATE_CREATE" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59");
//$arFilter = Array("IBLOCK_ID"=>32, "ACTIVE_DATE"=>"Y");
$arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM", "DATE_CREATE", "PROPERTY_94",
    "PROPERTY_97", "PROPERTY_124", "PROPERTY_132", "PROPERTY_140");
$vacancy = CIBlockElement::GetList(Array(), $arFilter, Array("ID", "NAME", "DATE_CREATE"), Array("nPageSize"=>500), $arSelect);
$num = 0;
while ($vacancyinfo = $vacancy->GetNext()) {
    //echo "<pre>";
    //print_r($vacancyinfo);
    //echo "</pre>";
    $resresult[$num]['ID'] = $vacancyinfo['ID'];
    $resresult[$num]['NAME'] = $vacancyinfo['NAME'];
    $resresult[$num]['DATE_CREATE'] = ConvertDateTime($vacancyinfo['DATE_CREATE'], "DD.MM.YYYY");

    $iteratorzakazchik = CIBlockElement::GetProperty(32, $vacancyinfo['ID'], array("sort" => "asc"), Array(
        "CODE" => 'ZAKAZCHIK'));
    while ($row = $iteratorzakazchik->Fetch())
    {
        $res = CCrmCompany::GetList(array(), array("ID" => $row['VALUE']));
        while($ob = $res->GetNext()) {
            $resresult[$num]['ZAKAZCHIK'] = $ob['TITLE'];

        }
    }

    $iteratorbeginprj = CIBlockElement::GetProperty(32, $vacancyinfo['ID'], array("sort" => "asc"), Array(
        "CODE" => 'NACHALO_PROEKTA'));
    while ($row = $iteratorbeginprj->Fetch())
    {
        $resresult[$num]['NACHALOPRJ'] = ConvertDateTime($row['VALUE'], "DD.MM.YYYY");
    }

    $iteratorendprj = CIBlockElement::GetProperty(32, $vacancyinfo['ID'], array("sort" => "asc"), Array(
        "CODE" => 'KONETS_PROEKTA'));
    while ($row = $iteratorendprj->Fetch())
    {
        $resresult[$num]['KONEZPRJ'] =  ConvertDateTime($row['VALUE'], "DD.MM.YYYY");
    }

    $iteratorstatus = CIBlockElement::GetProperty(32, $vacancyinfo['ID'], array("sort" => "asc"), Array(
        "CODE" => 'STATUS_BULEVO'));
    while ($row = $iteratorstatus->Fetch())
    {
        $arFilter = Array("IBLOCK_ID"=>38, "ID"=>$row['VALUE']);
        $status = CIBlockElement::GetList(Array(), $arFilter, Array("ID", "NAME"), Array());
        while ($statusinfo = $status->GetNext()) {
            $resresult[$num]['STATUS'] = $statusinfo['NAME'];


        }
        /*echo "<pre>";
        print_r($row);
        echo "</pre>"; */
    }
    $iteratorprooduct = CIBlockElement::GetProperty(32, $vacancyinfo['ID'], array("sort" => "asc"), Array(
         "CODE" => 'KOMPETENTSII_PRODUKTY'));
    while ($row = $iteratorprooduct->Fetch())
    {
        //echo "<pre>";
        //print_r($row);
        //echo "</pre>";
        if ($row['VALUE']) {
            $arFilter = Array("IBLOCK_ID" => 47, "ID" => $row['VALUE']);
            $product = CIBlockElement::GetList(Array(), $arFilter, Array("ID", "NAME"), Array());
            while ($productinfo = $product->GetNext()) {
                if ($resresult[$num]['PRODUCT']) {
                    $resresult[$num]['PRODUCT'] = $resresult[$num]['PRODUCT'] . "," . $productinfo['NAME'];
                } else {
                    $resresult[$num]['PRODUCT'] = $productinfo['NAME'];
                }
            }
        } else {
            $resresult[$num]['PRODUCT'] = "Не указан";
        }
    }
    $iteratorzakr = CIBlockElement::GetProperty(32, $vacancyinfo['ID'], array("sort" => "asc"), Array("CODE" => 'DATA_ZAKRYTIYA_VAKANSII'));
    while ($row = $iteratorzakr->Fetch())
    {
        $resresult[$num]['DATAZAKR'] = $row['VALUE'];
    }
    $iteratorzakaz = CIBlockElement::GetProperty(32, $vacancyinfo['ID'], array("sort" => "asc"), Array(
        "CODE" => 'ZAKAZ'));
    while ($row = $iteratorzakaz->Fetch())
    {
        //echo "<pre>";
        //print_r($row);
        //echo "</pre>";
        if($row['VALUE']) {
            $res = CCrmDeal::GetList(array(), array("ID" => $row['VALUE']));
            while($ob = $res->GetNext()) {
                $stage = $ob['STAGE_ID'];
                //$resresult[$num]['STAGE'] = $stage;
                $res2 = CCrmStatus::GetList(array(), array("ENTITY_ID" => 'DEAL_STAGE', "STATUS_ID" => $stage));
                while($ob2 = $res2->GetNext()) {
                    $resresult[$num]['STAGE'] = $ob2['NAME'];
	            }
            }
        }
    }
    $iteratornapr = CIBlockElement::GetProperty(32, $vacancyinfo['ID'], array("sort" => "asc"), Array("CODE" => 'NAPR_NIYA'));
    while ($row = $iteratornapr->Fetch())
    {
        $arFilter = Array("IBLOCK_ID"=>49, "ID"=>$row['VALUE']);
        $napr = CIBlockElement::GetList(Array(), $arFilter, Array("ID", "NAME"), Array());
        while ($naprav = $napr->GetNext()) {
            $resresult[$num]['NAPR'] = $naprav['NAME'];

        }
        //echo "<pre>";
        //print_r($row);
        //echo "</pre>";
    }
    $num++;
}

//echo "<pre>";
//print_r($resresult);
//echo "</pre>";

echo "<table id='vacancylist'><tr><th class='tablekpititle'>Название вакансии</th>
<th class='tablekpititle'>Проект</th><th class='tablekpititle'>Клиент</th>
<th class='tablekpititle'>Начало проекта</th>
<th class='tablekpititle'>Конец проекта</th>
<th class='tablekpititle'>Статус</th>
<th class='tablekpititle'>Продукт</th><th class='tablekpititle'>Дата создания</th>
<th class='tablekpititle'>Дата закрытия</th>
<th class='tablekpititle'>Этап сделки</th></tr>";
foreach ($resresult as $vacancy) {
    echo "<tr>";
    echo "<td class='tablekpi'>".$vacancy['NAPR']."</td>";
    echo "<td class='tablekpi'>".$vacancy['NAME']."</td>";
    echo "<td class='tablekpi'>".$vacancy['ZAKAZCHIK']."</td>";
    echo "<td class='tablekpi'>".$vacancy['NACHALOPRJ']."</td>";
    echo "<td class='tablekpi'>".$vacancy['KONEZPRJ']."</td>";
    echo "<td class='tablekpi'>".$vacancy['STATUS']."</td>";
    echo "<td class='tablekpi'>".$vacancy['PRODUCT']."</td>";
    echo "<td class='tablekpi'>".$vacancy['DATE_CREATE']."</td>";
    echo "<td class='tablekpi'>".$vacancy['DATAZAKR']."</td>";
    echo "<td class='tablekpi'>".$vacancy['STAGE']."</td>";
    echo "</tr>";
}
echo "</table>";