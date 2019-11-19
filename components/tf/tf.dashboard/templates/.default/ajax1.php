<?php
//print_r($_REQUEST);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
use Bitrix\Main\Loader;
use Bitrix\Crm\Timeline\Entity\TimelineTable;
$from = ($_REQUEST['datebegin'])?$_REQUEST['datebegin']:date("d.m.Y");
$to = ($_REQUEST['dateend'])?$_REQUEST['dateend']:date("d.m.Y");
/*echo "<p>";
echo $from;
echo "</p>";
echo "<p>";
echo $to;
echo "</p>";*/

// считаем число недель и вид бонуса
$dates = getWeekPeriod($from, $to);
if (count($dates)<4) {
    $bonustype = "w";
} elseif(count($dates)<12) {
    $bonustype = "m";
} else {
    $bonustype = "q";
}

//echo $bonustype;

$resresult = Array();
//print_r($dates);
// определяем вид запроса
if (!$_REQUEST['detailedpers'] && !$_REQUEST['detailedweek']) {
    $var = 1;
} elseif (!$_REQUEST['detailedpers'] && $_REQUEST['detailedweek']) {
    $var = 2;
} elseif ($_REQUEST['detailedpers'] && !$_REQUEST['detailedweek']) {
    $var = 3;
} else {
    $var = 4;
}
// готовим массивы данных для обработки


if (!Loader::includeModule('crm')) {
    echo "CRM модуль не установлен";
}


// выводим заголовок отчета
if ($var == 2 || $var == 4) {
    echo "<table><tr><th class='tablekpititle'>Партнеры. KPI Ресурсного менеджера</th>";
    foreach ($dates as $dateint) {
        $period = "С ";
        $num = 0;
        foreach ($dateint as $dateval) {
            if ($num==1) {
                $period = $period.$dateval;
            } else {
                $period = $period.$dateval." по ";
            }
            $num++;
        }
        echo "<th class='tablekpi'>".$period."</th>";
    }
    echo "<th class='tablekpi'>Общее Количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
} else {
    echo "<table><tr><th class='tablekpititle'>Партнеры. KPI Ресурсного менеджера</th><th class='tablekpi'>Количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
}
// строим запросы и наполняем детальную часть
if ($var==1) {
    $arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM", "DATE_CREATE", "PROPERTY_152");
    $arFilter = Array("IBLOCK_ID"=>54, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y",
        ">=DATE_CREATE" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
        "<=DATE_CREATE" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59");
    $vyvod = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>500), $arSelect);
    while ($vyvodinfo = $vyvod->GetNextElement()) {
        $arVyvod = $vyvodinfo->GetFields();
        //echo "<pre>";
        //print_r($arVyvod);
        //echo "</pre>";
        if ($arVyvod['PROPERTY_152_VALUE']!=0) {
            $company = CCrmCompany::GetbyID($arVyvod['PROPERTY_152_VALUE']);
            if ($company['COMPANY_TYPE']=='SUPPLIER' || $company['COMPANY_TYPE']=='1') {
                $resresult['resvyvod'] = $resresult['resvyvod'] + 1;
                if ($resresult['shifr']['resvyvod']) {
                    $resresult['shifr']['resvyvod'] = $resresult['shifr']['resvyvod'].",".$arVyvod['NAME'];
                } else {
                    $resresult['shifr']['resvyvod'] = $arVyvod['NAME'];
                }
            } else {
                $resresult['shifr']['recvyvodfl'] = $resresult['shifr']['recvyvodfl'] + 1;
                if ($resresult['shifr']['recvyvodfl']) {
                $resresult['shifr']['recvyvodfl'] = $resresult['shifr']['recvyvodfl'].",".$arVyvod['NAME'];
                } else {
                    $resresult['shifr']['recvyvodfl'] = $arVyvod['NAME'];
                }
            }
        } else {
            $resresult['recvyvod'] = $resresult['recvyvod'] + 1;
            if ($resresult['shifr']['recvyvod']) {
                $resresult['shifr']['recvyvod'] = $resresult['shifr']['recvyvod'].",".$arVyvod['NAME'];
            } else {
                $resresult['shifr']['recvyvod'] = $arVyvod['NAME'];
            }
        }
    }
    $comments=TimelineTable::getList(array(
        'order' => array("ID" => "DESC"),
        'filter' => array(
            '=TYPE_ID' => 7,
            '=CRM_TIMELINE_ENTITY_TIMELINE_BINDINGS_ENTITY_TYPE_ID' => 4
        ),
        'select'=>array("*", "BINDINGS")
    ));

    while($ar = $comments->Fetch())
    {
        $created = $ar['CREATED']->toString();
        if (strtotime($created) <= strtotime($to)) {
            if (strtotime($created) >= strtotime($from)) {
        //if ($created < ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59") {
        //    if ($created > ConvertDateTime($from, "DD.MM.YYYY")." 23:59:59") {

                $company = CCrmCompany::GetbyID($ar['CRM_TIMELINE_ENTITY_TIMELINE_BINDINGS_ENTITY_ID']);
                if ($company['COMPANY_TYPE'] == 'SUPPLIER' || $company['COMPANY_TYPE'] == '1') {
                    $resresult['rescall'] = $resresult['rescall'] + 1;
                } else {
                    $resresult['reccallfl'] = $resresult['reccallfl'] + 1;
                }
            }

        }
    }
    $companies2 = CCrmCompany::GetList(array("ID" => ASC), array(">=DATE_CREATE" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
        "<=DATE_CREATE" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59"));
    $exist = false;
    while ($company2  = $companies2->GetNext()) {
        //echo "yes";
        //echo "<pre>";
        //print_r($company2);
        //echo "</pre>";
        $cnt = CCrmContact::GetList(array("ID" => ASC), array("COMPANY_ID" => $company2['ID']));
        while ($cntid = $cnt->GetNext()) {
            if ($cntid ) {
                $exist = true;
            }
        }

        if ($exist==true) {
            if ($company2['COMPANY_TYPE'] == 'SUPPLIER' || $company2['COMPANY_TYPE'] == '1') {
                $resresult['newpartnres'] = $resresult['newpartnres'] + 1;
            } else {
                $resresult['newpartnfl'] = $resresult['newpartnfl'] + 1;
            }
        }
        $exist = false;

    }

    // участок по проверке внесения позиций
    $contacts3 = CCrmContact::GetList(array("ID" => ASC), array(">=DATE_CREATE" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
        "<=DATE_CREATE" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59"));
    $entersexist = false;
    if ($contacts3  = $contacts3->GetNext()) {
        $entersexist = true;
    }





    echo "<tr><th class='tablekpititle'>1. Поддержание активноси с партнерами (выводы / комментарии)</th>
            <td class='tablekpi'>".($resresult['resvyvod']?$resresult['resvyvod']:0)."/".($resresult['rescall']?$resresult['rescall']:0)."
            </td><td class='tablekpi'>".getbonustenprc($resresult['resvyvod'], $resresult['rescall'])."</td></tr>";
    echo "<tr><th class='tablekpititle'>2. Привлечение нового партнера</th>
            <td class='tablekpi'>".($resresult['newpartnres']?$resresult['newpartnres']:0)."
            </td><td class='tablekpi'>".getbonusperc($resresult['newpartnres'], $bonustype, 'newpartn')."</td></tr>";

    echo "<tr><th class='tablekpititle'>3. Число выведеннных специалистов</th>";
    if ($resresult['resvyvod']) {
        echo "<td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$resresult['shifr']['resvyvod']."'); return false;\">".($resresult['resvyvod']?$resresult['resvyvod']:0)."</a></td>";
    } else {
        echo "<td class='tablekpi'>".($resresult['resvyvod']?$resresult['resvyvod']:0)."</td>";
    }

    echo "<td class='tablekpi'>".getbonusperc($resresult['resvyvod'], $bonustype, 'vyvodres')."</td></tr></table>";
    echo "<br>";
    echo "<table><tr><th class='tablekpititle'>Открытый рынок. KPI Рекрутеров</th><th class='tablekpi'>Количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
    echo "<tr><th class='tablekpititle'>1. Число выведеннных специалистов</th>";
    if ($resresult['recvyvod']) {
        echo "<td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$resresult['shifr']['recvyvod']."'); return false;\">".($resresult['recvyvod']?$resresult['recvyvod']:0)."</a></td>";
    } else {
        echo "<td class='tablekpi'>".($resresult['recvyvod']?$resresult['recvyvod']:0)."</td>";
    }
    echo "<td class='tablekpi'>".getbonusperc($resresult['recvyvod'], $bonustype, 'vyvodrec')."</td>";
    echo "<tr><th class='tablekpititle'>2. Факт внесения данных</th>";
    if ($entersexist) {
        echo "<td class='tablekpi'>Данные вносились</td>";
        echo "<td class='tablekpi'>20%</td></table>";
    } else {
        echo "<td class='tablekpi'>Данные не вносились</td>";
        echo "<td class='tablekpi'>Вы уволены!</td></table>";
    }




    echo "<br>";
    echo "<table><tr><th class='tablekpititle'>Открытый рынок. KPI Белик</th><th class='tablekpi'>Количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
    echo "<tr><th class='tablekpititle'>1. Поддержание активноси с фрилансерами (выводы / комментарии)</th>
            <td class='tablekpi'>".($resresult['recvyvodfl']?$resresult['recvyvodfl']:0)."/".($resresult['reccallfl']?$resresult['reccallfl']:0)."
            </td><td class='tablekpi'>".getbonustenprc($resresult['recvyvodfl'], $resresult['reccallfl'])."</td></tr>";
    echo "<tr><th class='tablekpititle'>2. Привлечение нового фрилансера</th>
            <td class='tablekpi'>".($resresult['newpartnfl']?$resresult['newpartnfl']:0)."
            </td><td class='tablekpi'>".getbonusperc($resresult['newpartnfl'], $bonustype, 'newpartnfl')."</td></tr>";
    echo "<tr><th class='tablekpititle'>3. Число выведеннных специалистов</th>";
    if ($resresult['recvyvodfl']) {
        echo "<td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$resresult['shifr']['recvyvodfl']."'); return false;\">".($resresult['recvyvodfl']?$resresult['recvyvodfl']:0)."</a></td><td class='tablekpi'>".getbonusperc($resresult['recvyvodfl'], $bonustype, 'vyvodfl')."</td></table>";

    } else {
        echo "<td class='tablekpi'>".($resresult['recvyvodfl']?$resresult['recvyvodfl']:0)."</td><td class='tablekpi'>".getbonusperc($resresult['recvyvodfl'], $bonustype, 'vyvodfl')."</td></table>";
    }

} elseif ($var==2) {
    $per = 0;
    foreach ($dates as $dateint) {
        $num = 0;
        foreach ($dateint as $dateval) {
            if ($num==1) {
                $periodend = $dateval;
            } else {
                $periodbegin = $dateval;
            }
            $num++;
        }

        //echo "<pre>";
        //print_r($vyvod);
        //echo "</pre>";
        $arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM", "DATE_CREATE", "PROPERTY_152");
        $arFilter = Array("IBLOCK_ID"=>54, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y",
            ">=DATE_CREATE" => ConvertDateTime($periodbegin, "DD.MM.YYYY")." 00:00:00",
            "<=DATE_CREATE" => ConvertDateTime($periodend, "DD.MM.YYYY")." 23:59:59");
        $vyvod = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>500), $arSelect);
        while ($vyvodinfo = $vyvod->GetNextElement()) {
            $arVyvod = $vyvodinfo->GetFields();
                if ($arVyvod['PROPERTY_152_VALUE']!=0) {
                    $company = CCrmCompany::GetbyID($arVyvod['PROPERTY_152_VALUE']);
                    if ($company['COMPANY_TYPE']=='SUPPLIER' || $company['COMPANY_TYPE']=='1') {
                        if ($resresult['byper']['shifr'][$per]['resvyvod']) {
                            $resresult['byper']['shifr'][$per]['resvyvod'] = $resresult['byper']['shifr'][$per]['resvyvod'].",".$arVyvod['NAME'];
                        } else {
                            $resresult['byper']['shifr'][$per]['resvyvod'] = $arVyvod['NAME'];
                        }

                        $resresult['byper'][$per]['resvyvod'] = $resresult['byper'][$per]['resvyvod'] + 1;
                        $resresult['resvyvod'] = $resresult['resvyvod'] + 1;
                    } else {
                        if ($resresult['byper']['shifr'][$per]['recvyvodfl']) {
                            $resresult['byper']['shifr'][$per]['recvyvodfl'] = $resresult['byper']['shifr'][$per]['recvyvodfl'].",".$arVyvod['NAME'];
                        } else {
                            $resresult['byper']['shifr'][$per]['recvyvodfl'] = $arVyvod['NAME'];
                        }

                        $resresult['byper'][$per]['recvyvodfl'] = $resresult['byper'][$per]['recvyvodfl'] + 1;
                        $resresult['recvyvodfl'] = $resresult['recvyvodfl'] + 1;
                    }
                } else {
                    if ($resresult['byper']['shifr'][$per]['recvyvod']) {
                        $resresult['byper']['shifr'][$per]['recvyvod'] = $resresult['byper']['shifr'][$per]['recvyvod'].",".$arVyvod['NAME'];
                    } else {
                        $resresult['byper']['shifr'][$per]['recvyvod'] = $arVyvod['NAME'];
                    }
                    $resresult['byper'][$per]['recvyvod'] = $resresult['byper'][$per]['recvyvod'] + 1;
                    $resresult['recvyvod'] = $resresult['recvyvod'] + 1;
                }

        }
        $comments=TimelineTable::getList(array(
            'order' => array("ID" => "DESC"),
            'filter' => array(
                '=TYPE_ID' => 7,
                '=CRM_TIMELINE_ENTITY_TIMELINE_BINDINGS_ENTITY_TYPE_ID' => 4
            ),
            'select'=>array("*", "BINDINGS")
        ));

        while($ar = $comments->Fetch())
        {
            $created = $ar['CREATED']->toString();

            if (strtotime($created) <= strtotime($periodend)) {
                if (strtotime($created) >= strtotime($periodbegin)) {
                    $company = CCrmCompany::GetbyID($ar['CRM_TIMELINE_ENTITY_TIMELINE_BINDINGS_ENTITY_ID']);
                    if ($company['COMPANY_TYPE'] == 'SUPPLIER' || $company['COMPANY_TYPE'] == '1') {
                        $resresult['byper'][$per]['rescall'] = $resresult['byper'][$per]['rescall'] + 1;
                        $resresult['rescall'] = $resresult['rescall'] + 1;
                    } else {
                        $resresult['byper'][$per]['reccallfl'] = $resresult['byper'][$per]['reccallfl'] + 1;
                        $resresult['reccallfl'] = $resresult['reccallfl'] + 1;
                    }
                }

            }
        }

        $companies2 = CCrmCompany::GetList(array("ID" => ASC), array(">=DATE_CREATE" => ConvertDateTime($periodbegin, "DD.MM.YYYY")." 00:00:00",
            "<=DATE_CREATE" => ConvertDateTime($periodend, "DD.MM.YYYY")." 23:59:59"));
        $exist = false;
        while ($company2  = $companies2->GetNext()) {
            //echo "yes";
            //echo "<pre>";
            //print_r($company2);
            //echo "</pre>";
            $cnt = CCrmContact::GetList(array("ID" => ASC), array("COMPANY_ID" => $company2['ID']));
            while ($cntid = $cnt->GetNext()) {
                if ($cntid ) {
                    $exist = true;
                }
            }
            if ($exist==true) {
                if ($company2['COMPANY_TYPE'] == 'SUPPLIER' || $company2['COMPANY_TYPE'] == '1') {
                    $resresult['byper'][$per]['newpartnres'] = $resresult['byper'][$per]['newpartnres'] + 1;
                    $resresult['newpartnres'] = $resresult['newpartnres'] + 1;
                } else {
                    $resresult['byper'][$per]['newpartnfl'] = $resresult['byper'][$per]['newpartnfl'] + 1;
                    $resresult['newpartnfl'] = $resresult['newpartnfl'] + 1;
                }
            }
            $exist = false;
        }
        $per++;
    }

    echo "<tr><th class='tablekpititle'>1. Поддержание активноси с партнерами (выводы / комментарии)</th>";
    foreach ($dates as $key => $dateint) {
        echo "<td class='tablekpi'>".($resresult['byper'][$key]['resvyvod']?$resresult['byper'][$key]['resvyvod']:0)."/".
            ($resresult['byper'][$key]['rescall']?$resresult['byper'][$key]['rescall']:0)."</td>";
    }
    echo  "<td class='tablekpi'>".($resresult['resvyvod']?$resresult['resvyvod']:0)."/".($resresult['rescall']?$resresult['rescall']:0)."
            </td><td class='tablekpi'>".getbonustenprc($resresult['resvyvod'], $resresult['rescall'])."</td></tr>";
    echo "<tr><th class='tablekpititle'>2. Привлечение нового партнера</th>";
    foreach ($dates as $key => $dateint) {
        echo "<td class='tablekpi'>".($resresult['byper'][$key]['newpartnres']?$resresult['byper'][$key]['newpartnres']:0)."</td>";
    }
    echo "<td class='tablekpi'>".($resresult['newpartnres']?$resresult['newpartnres']:0)."
            </td><td class='tablekpi'>".getbonusperc($resresult['newpartnres'], $bonustype, 'newpartn')."</td></tr>";
    echo "<tr><th class='tablekpititle'>3. Число выведеннных специалистов</th>";
    foreach ($dates as $key => $dateint) {
        if ($resresult['byper'][$key]['resvyvod']) {
            echo "<td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$resresult['byper']['shifr'][$key]['resvyvod']."'); return false;\">".($resresult['byper'][$key]['resvyvod']?$resresult['byper'][$key]['resvyvod']:0)."</a></td>";
        } else {
            echo "<td class='tablekpi'>".($resresult['byper'][$key]['resvyvod']?$resresult['byper'][$key]['resvyvod']:0)."</td>";
        }
    }
    echo "<td class='tablekpi'>".($resresult['resvyvod']?$resresult['resvyvod']:0)."</td><td class='tablekpi'>".getbonusperc($resresult['resvyvod'], $bonustype, 'vyvodres')."</td></tr></table>";
    echo "<br>";
    echo "<table><tr><th class='tablekpititle'>Открытый рынок. KPI Рекрутеров</th>";
    foreach ($dates as $dateint) {
        $period = "С ";
        $num = 0;
        foreach ($dateint as $dateval) {
            if ($num==1) {
                $period = $period.$dateval;
            } else {
                $period = $period.$dateval." по ";
            }
            $num++;
        }
        echo "<th class='tablekpi'>".$period."</th>";
    }
    echo "<th class='tablekpi'>Общее количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
    echo "<tr><th class='tablekpititle'>1. Число выведеннных специалистов</th>";
    foreach ($dates as $key => $dateint) {
        if ($resresult['byper'][$key]['recvyvod']) {
            echo "<td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$resresult['byper']['shifr'][$key]['recvyvod']."'); return false;\">".($resresult['byper'][$key]['recvyvod']?$resresult['byper'][$key]['recvyvod']:0)."</a></td>";
        } else {
            echo "<td class='tablekpi'>".($resresult['byper'][$key]['recvyvod']?$resresult['byper'][$key]['recvyvod']:0)."</td>";
        }
    }
    echo  "<td class='tablekpi'>".($resresult['recvyvod']?$resresult['recvyvod']:0)."</td><td class='tablekpi'>".getbonusperc($resresult['recvyvod'], $bonustype, 'vyvodrec')."</td></table>";
    echo "<br>";
    echo "<table><tr><th class='tablekpititle'>Открытый рынок. KPI Белик</th>";
    foreach ($dates as $dateint) {
        $period = "С ";
        $num = 0;
        foreach ($dateint as $dateval) {
            if ($num==1) {
                $period = $period.$dateval;
            } else {
                $period = $period.$dateval." по ";
            }
            $num++;
        }
        echo "<th class='tablekpi'>".$period."</th>";
    }
    echo "<th class='tablekpi'>Общее количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
    echo "<tr><th class='tablekpititle'>1. Поддержание активноси с фрилансерами (выводы / комментарии)</th>";
    foreach ($dates as $key => $dateint) {
        echo "<td class='tablekpi'>".($resresult['byper'][$key]['recvyvodfl']?$resresult['byper'][$key]['recvyvodfl']:0)."/".
            ($resresult['byper'][$key]['reccallfl']?$resresult['byper'][$key]['reccallfl']:0)."</td>";
    }
    echo  "<td class='tablekpi'>".($resresult['recvyvodfl']?$resresult['recvyvodfl']:0)."/".($resresult['reccallfl']?$resresult['reccallfl']:0)."
            </td><td class='tablekpi'>".getbonustenprc($resresult['recvyvodfl'], $resresult['reccallfl'])."</td></tr>";
    echo "<tr><th class='tablekpititle'>2. Привлечение нового фрилансера</th>";
    foreach ($dates as $key => $dateint) {
        echo "<td class='tablekpi'>".($resresult['byper'][$key]['newpartnfl']?$resresult['byper'][$key]['newpartnfl']:0)."</td>";
    }
    echo "<td class='tablekpi'>".($resresult['newpartnfl']?$resresult['newpartnfl']:0)."
            </td><td class='tablekpi'>".getbonusperc($resresult['newpartnfl'], $bonustype, 'newpartnfl')."</td></tr>";
    echo "<tr><th class='tablekpititle'>3. Число выведеннных специалистов</th>";
    foreach ($dates as $key => $dateint) {
        if ($resresult['byper'][$key]['recvyvodfl']) {
            echo "<td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$resresult['byper']['shifr'][$key]['recvyvodfl']."'); return false;\">".($resresult['byper'][$key]['recvyvodfl']?$resresult['byper'][$key]['recvyvodfl']:0)."</a></td>";
        } else {
            echo "<td class='tablekpi'>".($resresult['byper'][$key]['recvyvodfl']?$resresult['byper'][$key]['recvyvodfl']:0)."</td>";
        }
    }
    echo  "<td class='tablekpi'>".($resresult['recvyvodfl']?$resresult['recvyvodfl']:0)."</td><td class='tablekpi'>".getbonusperc($resresult['recvyvodfl'], $bonustype, 'vyvodfl')."</td></table>";
    //echo "<pre>";
    //print_r($resresult);
    //echo "</pre>";
} elseif ($var==3) {
    $arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM", "DATE_CREATE", "PROPERTY_152", "PROPERTY_143");
    $arFilter = Array("IBLOCK_ID"=>54, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y",
        ">=DATE_CREATE" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
        "<=DATE_CREATE" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59");
    $vyvod = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>500), $arSelect);
    while ($vyvodinfo = $vyvod->GetNextElement()) {
        $arVyvod = $vyvodinfo->GetFields();
        //echo "<pre>";
        //print_r($arVyvod);
        //echo "</pre>";
        if ($arVyvod['PROPERTY_152_VALUE']!=0 && $arVyvod['PROPERTY_143_VALUE']!=0) {
            $company = CCrmCompany::GetbyID($arVyvod['PROPERTY_152_VALUE']);
            $arOrder = Array (
                "ID" => "desc"
            );
            $arFilter = Array (
                "ID" => $arVyvod['PROPERTY_143_VALUE']
            );
            $arSelect = Array (
                "UF_CRM_1566034018"
            );
            $deal = CCrmDeal::GetList($arOrder, $arFilter, $arSelect, false);
            while ($dealinfo  = $deal->GetNext()) {
                $resid = $dealinfo['UF_CRM_1566034018'];
            }
            if ($company['COMPANY_TYPE']=='SUPPLIER' || $company['COMPANY_TYPE']=='1') {
                if ($resresult['pers']['shifr']['resvyvod'][$resid]) {
                    $resresult['pers']['shifr']['resvyvod'][$resid] = $resresult['pers']['shifr']['resvyvod'][$resid].",".$arVyvod['NAME'];
                } else {
                    $resresult['pers']['shifr']['resvyvod'][$resid] = $arVyvod['NAME'];
                }

                $resresult['pers']['resvyvod'][$resid] = $resresult['pers']['resvyvod'][$resid] +1;
                $resresult['resvyvod'] = $resresult['resvyvod'] + 1;
            } else {
                if ($resresult['shifr']['recvyvodfl']) {
                    $resresult['shifr']['recvyvodfl'] = $resresult['shifr']['recvyvodfl'].",".$arVyvod['NAME'];
                } else {
                    $resresult['shifr']['recvyvodfl'] = $arVyvod['NAME'];
                }

                $resresult['recvyvodfl'] = $resresult['recvyvodfl'] + 1;
            }
        } elseif($arVyvod['PROPERTY_143_VALUE']!=0) {
            $deal = CCrmDeal::GetbyID($arVyvod['PROPERTY_143_VALUE']);
            //echo "<pre>";
            //print_r($deal);
            //echo "</pre>";
            $recid = $deal['ASSIGNED_BY_ID'];
            if ($resresult['pers']['shifr']['recvyvod'][$recid]) {
                $resresult['pers']['shifr']['recvyvod'][$recid] = $resresult['pers']['shifr']['recvyvod'][$recid].",".$arVyvod['NAME'];
            } else {
                $resresult['pers']['shifr']['recvyvod'][$recid] = $arVyvod['NAME'];
            }
            $resresult['pers']['recvyvod'][$recid] = $resresult['pers']['recvyvod'][$recid] + 1;
            $resresult['recvyvod'] = $resresult['recvyvod'] + 1;
        }
    }
    $comments=TimelineTable::getList(array(
        'order' => array("ID" => "DESC"),
        'filter' => array(
            '=TYPE_ID' => 7,
            '=CRM_TIMELINE_ENTITY_TIMELINE_BINDINGS_ENTITY_TYPE_ID' => 4
        ),
        'select'=>array("*", "BINDINGS")
    ));

    while($ar = $comments->Fetch())
    {
        //echo "<pre>";
        //print_r($ar);
        //echo "</pre>";
        $created = $ar['CREATED']->toString();
        $author = $ar['AUTHOR_ID'];
        if (strtotime($created) <= strtotime($to)) {
            if (strtotime($created) >= strtotime($from)) {

        //if ($created <= ConvertDateTime($to, "DD.MM.YYYY")." 00:00:00") {
         //   if ($created >= ConvertDateTime($from, "DD.MM.YYYY")." 23:59:59") {
                $company = CCrmCompany::GetbyID($ar['CRM_TIMELINE_ENTITY_TIMELINE_BINDINGS_ENTITY_ID']);
                if ($company['COMPANY_TYPE'] == 'SUPPLIER' || $company['COMPANY_TYPE'] == '1') {
                    $resresult['pers']['rescall'][$author ] = $resresult['pers']['rescall'][$author ] + 1;
                    $resresult['rescall'] = $resresult['rescall'] + 1;
                } else {
                    $resresult['reccallfl'] = $resresult['reccallfl'] + 1;
                }
            }

        }
    }
    $companies2 = CCrmCompany::GetList(array("ID" => ASC), array(">=DATE_CREATE" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
        "<=DATE_CREATE" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59"));


    $exist = false;
    while ($company2  = $companies2->GetNext()) {
        /*echo "yes";
        echo "<pre>";
        print_r($company2);
        echo "</pre>"; */
        $company3 = CCrmCompany::GetbyID($company2['ID']);
        /*echo "<pre>";
        print_r($company3);
        echo "</pre>";*/
        $cnt = CCrmContact::GetList(array("ID" => ASC), array("COMPANY_ID" => $company2['ID']));
        while ($cntid = $cnt->GetNext()) {
            if ($cntid ) {
                $exist = true;
            }
        }
        if ($exist==true) {
            $resp = $company3['ASSIGNED_BY_ID'];
            if ($company2['COMPANY_TYPE'] == 'SUPPLIER' || $company2['COMPANY_TYPE'] == '1') {
                $resresult['pers']['newpartnres'][$resp] = $resresult['pers']['newpartnres'][$resp] + 1;
                $resresult['newpartnres'] = $resresult['newpartnres'] + 1;
            } else {
                $resresult['newpartnfl'] = $resresult['newpartnfl'] + 1;
            }
        }
        $exist = false;
    }
    /*echo "<pre>";
    print_r($resresult);
    echo "</pre>";*/

    foreach ($resresult['pers']['resvyvod'] as $resman => $resvyvod) {
        $vyvcom[$resman]['vyv'] = $resvyvod;
    }
    foreach ($resresult['pers']['rescall'] as $resman => $resvyvod) {
        $vyvcom[$resman]['call'] = $resvyvod;
    }

    /*echo "<pre>";
    print_r($vyvcom);
    echo "</pre>";*/

    //echo  getname(30);
    echo "<tr><th class='tablekpititle'>1. Поддержание активноси с партнерами (выводы / комментарии), включая:</th>
            <td class='tablekpi'>".($resresult['resvyvod']?$resresult['resvyvod']:0)."/".($resresult['rescall']?$resresult['rescall']:0)."
            </td><td class='tablekpi'>".getbonustenprc($resresult['resvyvod'], $resresult['rescall'])."</td></tr>";
    foreach ($vyvcom as $resman => $resvyvod) {
        echo "<tr><th class='tablekpititleright'>".getname($resman)."</th>
            <td class='tablekpi'>".($resvyvod['vyv']?$resvyvod['vyv']:0)."/".($resvyvod['call']?$resvyvod['call']:0)."
            </td><td class='tablekpi'>".getbonustenprc($resvyvod['vyv'], $resvyvod['call'])."</td></tr>";
    }
    echo "<tr><th class='tablekpititle'>2. Привлечение нового партнера</th>
            <td class='tablekpi'>".($resresult['newpartnres']?$resresult['newpartnres']:0)."
            </td><td class='tablekpi'>".getbonusperc($resresult['newpartnres'], $bonustype, 'newpartn')."</td></tr>";
    foreach ($resresult['pers']['newpartnres'] as $resman => $resvyvod) {
        echo "<tr><td class='tablekpititleright'>".getname($resman)."</td>
            <td class='tablekpi'>".($resvyvod?$resvyvod:0)."
            </td><td class='tablekpi'>".getbonusperc($resvyvod, $bonustype, 'newpartn')."</td></tr>";
    }
    echo "<tr><th class='tablekpititle'>3. Число выведеннных специалистов</th>
        <td class='tablekpi'>".($resresult['resvyvod']?$resresult['resvyvod']:0)."</td><td class='tablekpi'>".getbonusperc($resresult['resvyvod'], $bonustype, 'vyvodres')."</td></tr>";
    foreach ($resresult['pers']['resvyvod'] as $resman => $resvyvod) {
        if ($resvyvod) {
            echo "<tr><th class='tablekpititleright'>".getname($resman)."</th>
            <td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$resresult['pers']['shifr']['resvyvod'][$resman]."'); return false;\">".($resvyvod?$resvyvod:0)."
            </a></td><td class='tablekpi'>".getbonusperc($resvyvod, $bonustype, 'newpartn')."</td></tr>";

        } else {
            echo "<tr><th class='tablekpititleright'>".getname($resman)."</th>
            <td class='tablekpi'>".($resvyvod?$resvyvod:0)."
            </td><td class='tablekpi'>".getbonusperc($resvyvod, $bonustype, 'newpartn')."</td></tr>";

        }

    }
    echo "</table>";
    echo "<br>";
    echo "<table><tr><th class='tablekpititle'>Открытый рынок. KPI Рекрутеров</th><th class='tablekpi'>Количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
    echo "<tr><th class='tablekpititle'>1. Число выведеннных специалистов</th>
        <td class='tablekpi'>".($resresult['recvyvod']?$resresult['recvyvod']:0)."</td><td class='tablekpi'>".getbonusperc($resresult['recvyvod'], $bonustype, 'vyvodrec')."</td>";
    foreach ($resresult['pers']['recvyvod'] as $resman => $recvyvod) {
        if ($recvyvod) {
            echo "<tr><th class='tablekpititleright'>".getname($resman)."</th>
            <td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$resresult['pers']['shifr']['recvyvod'][$resman]."'); return false;\">".($recvyvod?$recvyvod:0)."
            </a></td><td class='tablekpi'>".getbonusperc($recvyvod, $bonustype, 'newpartn')."</td></tr>";

        } else {
            echo "<tr><th class='tablekpititleright'>".getname($resman)."</th>
            <td class='tablekpi'>".($recvyvod?$recvyvod:0)."
            </td><td class='tablekpi'>".getbonusperc($recvyvod, $bonustype, 'newpartn')."</td></tr>";

        }
    }
    echo "</table>";
    echo "<br>";
    echo "<table><tr><th class='tablekpititle'>Открытый рынок. KPI Белик</th><th class='tablekpi'>Количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
    echo "<tr><th class='tablekpititle'>1. Поддержание активноси с фрилансерами (выводы / комментарии)</th>
            <td class='tablekpi'>".($resresult['recvyvodfl']?$resresult['recvyvodfl']:0)."/".($resresult['reccallfl']?$resresult['reccallfl']:0)."
            </td><td class='tablekpi'>".getbonustenprc($resresult['recvyvodfl'], $resresult['reccallfl'])."</td></tr>";
    echo "<tr><th class='tablekpititle'>2. Привлечение нового фрилансера</th>
            <td class='tablekpi'>".($resresult['newpartnfl']?$resresult['newpartnfl']:0)."
            </td><td class='tablekpi'>".getbonusperc($resresult['newpartnfl'], $bonustype, 'newpartnfl')."</td></tr>";
    echo "<tr><th class='tablekpititle'>3. Число выведеннных специалистов</th>";
    if ($resresult['recvyvodfl']) {
        echo "<td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$resresult['shifr']['recvyvodfl']."'); return false;\">".($resresult['recvyvodfl']?$resresult['recvyvodfl']:0)."</a></td><td class='tablekpi'>".getbonusperc($resresult['recvyvodfl'], $bonustype, 'vyvodfl')."</td></table>";
    } else {
        echo "<td class='tablekpi'>".($resresult['recvyvodfl']?$resresult['recvyvodfl']:0)."</td><td class='tablekpi'>".getbonusperc($resresult['recvyvodfl'], $bonustype, 'vyvodfl')."</td></table>";
    }

} else {
    $per = 0;
    foreach ($dates as $dateint) {
        $num = 0;
        foreach ($dateint as $dateval) {
            if ($num==1) {
                $periodend = $dateval;
            } else {
                $periodbegin = $dateval;
            }
            $num++;
        }

        //echo "<pre>";
        //print_r($vyvod);
        //echo "</pre>";
        $arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM", "DATE_CREATE", "PROPERTY_152", "PROPERTY_143");
        $arFilter = Array("IBLOCK_ID"=>54, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y",
            ">=DATE_CREATE" => ConvertDateTime($periodbegin, "DD.MM.YYYY")." 00:00:00",
            "<=DATE_CREATE" => ConvertDateTime($periodend, "DD.MM.YYYY")." 23:59:59");
        $vyvod = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
        while ($vyvodinfo = $vyvod->GetNextElement()) {
            $arVyvod = $vyvodinfo->GetFields();
            $company = CCrmCompany::GetbyID($arVyvod['PROPERTY_152_VALUE']);
            $arOrder = Array (
                "ID" => "desc"
            );
            $arFilter = Array (
                "ID" => $arVyvod['PROPERTY_143_VALUE']
            );
            $arSelect = Array (
                "UF_CRM_1566034018"
            );
            $deal = CCrmDeal::GetList($arOrder, $arFilter, $arSelect, false);
            while ($dealinfo  = $deal->GetNext()) {
                $resid = $dealinfo['UF_CRM_1566034018'];
            }
            if ($arVyvod['PROPERTY_152_VALUE']!=0 && $arVyvod['PROPERTY_143_VALUE']!=0) {
                $company = CCrmCompany::GetbyID($arVyvod['PROPERTY_152_VALUE']);
                if ($company['COMPANY_TYPE']=='SUPPLIER' || $company['COMPANY_TYPE']=='1') {
                    if ($resresult['byper']['shifr']['resvyvod']['pers'][$resid][$per]) {
                        $resresult['byper']['shifr']['resvyvod']['pers'][$resid][$per] = $resresult['byper']['shifr']['resvyvod']['pers'][$resid][$per].",".$arVyvod['NAME'];
                    } else {
                        $resresult['byper']['shifr']['resvyvod']['pers'][$resid][$per] = $arVyvod['NAME'];
                    }


                    $resresult['byper']['resvyvod']['pers'][$resid][$per] = $resresult['byper']['resvyvod']['pers'][$resid][$per] + 1;
                    $resresult['byper']['nopers'][$per]['resvyvod'] = $resresult['byper']['nopers'][$per]['resvyvod'] + 1;
                    $resresult['pers']['resvyvod'][$resid] = $resresult['pers']['resvyvod'][$resid] +1;
                    $resresult['resvyvod'] = $resresult['resvyvod'] + 1;
                } elseif($arVyvod['PROPERTY_143_VALUE']!=0) {
                    if ($resresult['byper']['shifr'][$per]['recvyvodfl']) {
                        $resresult['byper']['shifr'][$per]['recvyvodfl'] = $resresult['byper']['shifr'][$per]['recvyvodfl'].$arVyvod['NAME'];
                    } else {
                        $resresult['byper']['shifr'][$per]['recvyvodfl'] = $arVyvod['NAME'];
                    }
                    $resresult['byper'][$per]['recvyvodfl'] = $resresult['byper'][$per]['recvyvodfl'] + 1;
                    $resresult['recvyvodfl'] = $resresult['recvyvodfl'] + 1;
                }
            } else {
                $deal = CCrmDeal::GetbyID($arVyvod['PROPERTY_143_VALUE']);
                //echo "<pre>";
                //print_r($deal);
                //echo "</pre>";
                $recid = $deal['ASSIGNED_BY_ID'];
                //$resresult['byper']['shifr']['reсvyvod']['pers'][$recid][$per] = $arVyvod['NAME'];
                if ($resresult['byper']['shifr']['recvyvod']['pers'][$recid][$per]) {
                    $resresult['byper']['shifr']['recvyvod']['pers'][$recid][$per] = $resresult['byper']['shifr']['recvyvod']['pers'][$recid][$per].",".$arVyvod['NAME'];
                } else {
                    $resresult['byper']['shifr']['recvyvod']['pers'][$recid][$per] = $arVyvod['NAME'];
                }
                $resresult['byper']['recvyvod']['pers'][$recid][$per] = $resresult['byper']['recvyvod']['pers'][$recid][$per] + 1;
                $resresult['pers']['recvyvod'][$recid] = $resresult['pers']['recvyvod'][$recid] + 1;
                $resresult['byper']['nopers'][$per]['recvyvod'] = $resresult['byper']['nopers'][$per]['recvyvod'] + 1;
                $resresult['recvyvod'] = $resresult['recvyvod'] + 1;
            }

        }
        $comments=TimelineTable::getList(array(
            'order' => array("ID" => "DESC"),
            'filter' => array(
                '=TYPE_ID' => 7,
                '=CRM_TIMELINE_ENTITY_TIMELINE_BINDINGS_ENTITY_TYPE_ID' => 4
            ),
            'select'=>array("*", "BINDINGS")
        ));

        while($ar = $comments->Fetch())
        {
            $created = $ar['CREATED']->toString();
            $author = $ar['AUTHOR_ID'];
            if (strtotime($created) <= strtotime($periodend)) {
                if (strtotime($created) >= strtotime($periodbegin)) {
                    $company = CCrmCompany::GetbyID($ar['CRM_TIMELINE_ENTITY_TIMELINE_BINDINGS_ENTITY_ID']);
                    if ($company['COMPANY_TYPE'] == 'SUPPLIER' || $company['COMPANY_TYPE'] == '1') {
                        $resresult['byper']['rescall']['pers'][$author][$per] = $resresult['byper']['rescall']['pers'][$author][$per] + 1;
                        $resresult['byper']['nopers'][$per]['rescall'] = $resresult['byper']['nopers'][$per]['rescall'] + 1;
                        $resresult['pers']['rescall'][$author] = $resresult['pers']['rescall'][$author] + 1;
                        $resresult['rescall'] = $resresult['rescall'] + 1;
                    } else {
                        $resresult['byper'][$per]['reccallfl'] = $resresult['byper'][$per]['reccallfl'] + 1;
                        $resresult['reccallfl'] = $resresult['reccallfl'] + 1;
                    }
                }

            }
        }

        $companies2 = CCrmCompany::GetList(array("ID" => ASC), array(">=DATE_CREATE" => ConvertDateTime($periodbegin, "DD.MM.YYYY")." 00:00:00",
            "<=DATE_CREATE" => ConvertDateTime($periodend, "DD.MM.YYYY")." 23:59:59"));
        $exist = false;
        while ($company2  = $companies2->GetNext()) {
            //echo "yes";
            //echo "<pre>";
            //print_r($company2);
            //echo "</pre>";
            $company3 = CCrmCompany::GetbyID($company2['ID']);
            $cnt = CCrmContact::GetList(array("ID" => ASC), array("COMPANY_ID" => $company2['ID']));
            while ($cntid = $cnt->GetNext()) {
                if ($cntid ) {
                    $exist = true;
                }
            }
            if ($exist==true) {
                $resp = $company3['ASSIGNED_BY_ID'];
                if ($company2['COMPANY_TYPE'] == 'SUPPLIER' || $company2['COMPANY_TYPE'] == '1') {
                    $resresult['byper']['newpartnres']['pers'][$resp][$per] = $resresult['byper']['newpartnres']['pers'][$resp][$per] + 1;
                    $resresult['byper']['nopers'][$per]['newpartnres'] = $resresult['byper']['nopers'][$per]['newpartnres'] + 1;
                    $resresult['pers']['newpartnres'][$resp] = $resresult['pers']['newpartnres'][$resp] + 1;
                    $resresult['newpartnres'] = $resresult['newpartnres'] + 1;
                } else {
                    $resresult['byper'][$per]['newpartnfl'] = $resresult['byper'][$per]['newpartnfl'] + 1;
                    $resresult['newpartnfl'] = $resresult['newpartnfl'] + 1;
                }
            }
            $exist = false;
        }
        $per++;
    }

    //echo "<pre>";
    //print_r($resresult['byper']['shifr']['recvyvod']);
    //print_r($resresult['byper']['recvyvod']['pers']);
    //echo "</pre>";


    foreach ($resresult['pers']['resvyvod'] as $resman => $resvyvod) {
        $vyvcom['agg'][$resman]['vyv'] = $resvyvod;
    }
    foreach ($resresult['pers']['rescall'] as $resman => $resvyvod) {
        $vyvcom['agg'][$resman]['call'] = $resvyvod;
    }
    foreach ($resresult['byper']['resvyvod']['pers'] as $resman => $resvyvod) {
        foreach ($resvyvod as $keyn => $dateintn) {
            $vyvcomdet[$resman][$keyn]['vyv'] = $dateintn;
        }
    }
    foreach ($resresult['byper']['rescall']['pers'] as $resman => $resvyvod) {
        foreach ($resvyvod as $keyn => $dateintn) {
            $vyvcomdet[$resman][$keyn]['call'] = $dateintn;
        }
    }

    /*echo "<pre>";
    print_r($vyvcom);
    echo "</pre>";

    echo "<pre>";
    print_r($vyvcomdet);
    echo "</pre>"; */

    echo "<tr><th class='tablekpititle'>1. Поддержание активноси с партнерами (выводы / комментарии), включая</th>";
    foreach ($dates as $key => $dateint) {
        echo "<td class='tablekpi'>".($resresult['byper']['nopers'][$key]['resvyvod']?$resresult['byper']['nopers'][$key]['resvyvod']:0)."/".
            ($resresult['byper']['nopers'][$key]['rescall']?$resresult['byper']['nopers'][$key]['rescall']:0)."</td>";
    }
    echo  "<td class='tablekpi'>".($resresult['resvyvod']?$resresult['resvyvod']:0)."/".($resresult['rescall']?$resresult['rescall']:0)."
            </td><td class='tablekpi'>".getbonustenprc($resresult['resvyvod'], $resresult['rescall'])."</td></tr>";
    foreach ($vyvcom['agg'] as $resman => $resvyvod) {
        echo "<tr><th class='tablekpititleright'>".getname($resman);
        echo "</th>";
        foreach ($dates as $key => $dateint) {
            echo "<td class='tablekpi'>".
                ($vyvcomdet[$resman][$key]['vyv']?$vyvcomdet[$resman][$key]['vyv']:0)."/".
                ($vyvcomdet[$resman][$key]['call']?$vyvcomdet[$resman][$key]['call']:0)."
            </td>";
        }
        echo "<td class='tablekpi'>".($resvyvod['vyv']?$resvyvod['vyv']:0)."/".($resvyvod['call']?$resvyvod['call']:0)."
            </td><td class='tablekpi'>".getbonustenprc($resvyvod['vyv'], $resvyvod['call'])."</td></tr>";
    }
    echo "<tr><th class='tablekpititle'>2. Привлечение нового партнера</th>";
    foreach ($dates as $key => $dateint) {
        echo "<td class='tablekpi'>".($resresult['byper']['nopers'][$key]['newpartnres']?$resresult['byper']['nopers'][$key]['newpartnres']:0)."</td>";
    }
    echo "<td class='tablekpi'>".($resresult['newpartnres']?$resresult['newpartnres']:0)."
            </td><td class='tablekpi'>".getbonusperc($resresult['newpartnres'], $bonustype, 'newpartn')."</td></tr>";
    foreach ($resresult['pers']['newpartnres'] as $resman => $resvyvod) {
        echo "<tr><th class='tablekpititleright'>".getname($resman)."</th>";
        foreach ($dates as $key => $dateint) {
            echo "<td class='tablekpi'>".
                ($resresult['byper']['newpartnres']['pers'][$resman][$key]?$resresult['byper']['newpartnres']['pers'][$resman][$key]:0)."
            </td>";
        }
        echo "<td class='tablekpi'>".($resvyvod?$resvyvod:0)."
            </td><td class='tablekpi'>".getbonusperc($resvyvod, $bonustype, 'newpartn')."</td></tr>";
    }
    echo "<tr><th class='tablekpititle'>3. Число выведеннных специалистов</th>";
    foreach ($dates as $key => $dateint) {
        echo "<td class='tablekpi'>".($resresult['byper']['nopers'][$key]['resvyvod']?$resresult['byper']['nopers'][$key]['resvyvod']:0)."</td>";
    }
    echo "<td class='tablekpi'>".($resresult['resvyvod']?$resresult['resvyvod']:0)."</td><td class='tablekpi'>".getbonusperc($resresult['resvyvod'], $bonustype, 'vyvodres')."</td></tr>";
    foreach ($resresult['pers']['resvyvod'] as $resman => $resvyvod) {
        echo "<tr><th class='tablekpititleright'>".getname($resman)."</th>";
        foreach ($dates as $key => $dateint) {
            if ($resresult['byper']['resvyvod']['pers'][$resman][$key]) {
                echo "<td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: " . $resresult['byper']['shifr']['resvyvod']['pers'][$resman][$key] . "'); return false;\">" .
                        ($resresult['byper']['resvyvod']['pers'][$resman][$key] ? $resresult['byper']['resvyvod']['pers'][$resman][$key] : 0) . "
                        </a></td>";
                } else {
                    echo "<td class='tablekpi'>".
                        ($resresult['byper']['resvyvod']['pers'][$resman][$key]?$resresult['byper']['resvyvod']['pers'][$resman][$key]:0)."
                    </td>";
                }
        }
        echo "<td class='tablekpi'>".($resvyvod?$resvyvod:0)."
            </td><td class='tablekpi'>".getbonusperc($resvyvod, $bonustype, 'newpartn')."</td></tr>";
    }
    echo "</table>";
    echo "<br>";
    echo "<table><tr><th class='tablekpititle'>Открытый рынок. KPI Рекрутеров</th>";
    foreach ($dates as $dateint) {
        $period = "С ";
        $num = 0;
        foreach ($dateint as $dateval) {
            if ($num==1) {
                $period = $period.$dateval;
            } else {
                $period = $period.$dateval." по ";
            }
            $num++;
        }
        echo "<th class='tablekpi'>".$period."</th>";
    }
    echo "<th class='tablekpi'>Общее количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
    echo "<tr><th class='tablekpititle'>1. Число выведеннных специалистов</th>";
    foreach ($dates as $key => $dateint) {
        echo "<td class='tablekpi'>".($resresult['byper']['nopers'][$key]['recvyvod']?$resresult['byper']['nopers'][$key]['recvyvod']:0)."</td>";
    }
    echo  "<th class='tablekpi'>".($resresult['recvyvod']?$resresult['recvyvod']:0)."</th><th class='tablekpi'>".getbonusperc($resresult['recvyvod'], $bonustype, 'vyvodrec')."</th></tr>";
    foreach ($resresult['pers']['recvyvod'] as $resman => $resvyvod) {
        echo "<tr><th class='tablekpititleright'>".getname($resman)."</th>";
        foreach ($dates as $key => $dateint) {
            if ($resresult['byper']['recvyvod']['pers'][$resman][$key]) {
                echo "<td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: " . $resresult['byper']['shifr']['recvyvod']['pers'][$resman][$key] . "'); return false;\">" .
                    ($resresult['byper']['recvyvod']['pers'][$resman][$key] ? $resresult['byper']['recvyvod']['pers'][$resman][$key] : 0) . "
                        </a></td>";
            } else {
                echo "<td class='tablekpi'>".
                    ($resresult['byper']['recvyvod']['pers'][$resman][$key]?$resresult['byper']['recvyvod']['pers'][$resman][$key]:0)."
                    </td>";
            }
        }
        /*foreach ($dates as $key => $dateint) {
            echo "<td class='tablekpi'>".
                ($resresult['byper']['recvyvod']['pers'][$resman][$key]?$resresult['byper']['recvyvod']['pers'][$resman][$key]:0)."
            </td>";
        } */
        echo "<td class='tablekpi'>".($resvyvod?$resvyvod:0)."
            </td><td class='tablekpi'>".getbonusperc($resvyvod, $bonustype, 'newpartn')."</td></tr>";
    }
    echo "</table>";
    echo "<br>";
    echo "<table><tr><th class='tablekpititle'>Открытый рынок. KPI Белик</th>";
    foreach ($dates as $dateint) {
        $period = "С ";
        $num = 0;
        foreach ($dateint as $dateval) {
            if ($num==1) {
                $period = $period.$dateval;
            } else {
                $period = $period.$dateval." по ";
            }
            $num++;
        }
        echo "<th class='tablekpi'>".$period."</th>";
    }
    echo "<th class='tablekpi'>Общее количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
    echo "<tr><th class='tablekpititle'>1. Поддержание активноси с фрилансерами (выводы / комментарии)</th>";
    foreach ($dates as $key => $dateint) {
        echo "<td class='tablekpi'>".($resresult['byper'][$key]['recvyvodfl']?$resresult['byper'][$key]['recvyvodfl']:0)."/".
            ($resresult['byper'][$key]['reccallfl']?$resresult['byper'][$key]['reccallfl']:0)."</td>";
    }
    echo  "<td class='tablekpi'>".($resresult['recvyvodfl']?$resresult['recvyvodfl']:0)."/".($resresult['reccallfl']?$resresult['reccallfl']:0)."
            </td><th class='tablekpi'>".getbonustenprc($resresult['recvyvodfl'], $resresult['reccallfl'])."</th></tr>";
    echo "<tr><th class='tablekpititle'>2. Привлечение нового фрилансера</th>";
    foreach ($dates as $key => $dateint) {
        echo "<td class='tablekpi'>".($resresult['byper'][$key]['newpartnfl']?$resresult['byper'][$key]['newpartnfl']:0)."</td>";
    }
    echo "<td class='tablekpi'>".($resresult['newpartnfl']?$resresult['newpartnfl']:0)."
            </td><td class='tablekpi'>".getbonusperc($resresult['newpartnfl'], $bonustype, 'newpartnfl')."</td></tr>";
    echo "<tr><th class='tablekpititle'>3. Число выведеннных специалистов</th>";
    foreach ($dates as $key => $dateint) {
        if ($resresult['byper'][$key]['recvyvodfl']) {
            echo "<td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$resresult['byper']['shifr'][$key]['recvyvodfl']."'); return false;\">".($resresult['byper'][$key]['recvyvodfl']?$resresult['byper'][$key]['recvyvodfl']:0)."</a></td>";
        } else {
            echo "<td class='tablekpi'>".($resresult['byper'][$key]['recvyvodfl']?$resresult['byper'][$key]['recvyvodfl']:0)."</td>";
        }

        //echo "<td class='tablekpi'>".($resresult['byper'][$key]['recvyvodfl']?$resresult['byper'][$key]['recvyvodfl']:0)."</td>";
    }
    echo  "<td class='tablekpi'>".($resresult['recvyvodfl']?$resresult['recvyvodfl']:0)."</td><td class='tablekpi'>".getbonusperc($resresult['recvyvodfl'], $bonustype, 'vyvodfl')."</td></table>";

}

function getname($iduser) {
    $rsUser = CUser::GetByID($iduser);
    $arUser = $rsUser->Fetch();
    return $arUser['NAME']." ".$arUser['LAST_NAME'];
}



function getbonusperc($qty, $period, $type)
{
    if ($type == 'vyvodres' || $type == 'vyvodrec') {
        //echo "here";
        if ($period=='m') {
            if ($qty<3) {
                return 0;
            } elseif ($qty<9) {
                return 60;
            } else {
                return 120;
            }
        } elseif ($period=='q') {
            if ($qty<9) {
                return 0;
            } elseif ($qty<24) {
                return 60;
            } else {
                return 120;
            }
        } else {
            return 0;
        }
    } elseif ($type == 'newpartn') {
        if ($period=='m') {
            if ($qty<4) {
                return 0;
            } else
                return 30;
            }
        elseif ($period=='q') {
            if ($qty<12) {
                return 0;
            } else {
                return 30;
            }
        } else {
            return 0;
        }
    } elseif ($type == 'newpartnfl') {
        if ($period=='m') {
            if ($qty=0) {
                return 0;
            } else
                return 30;
        }
        elseif ($period=='q') {
            if ($qty<3) {
                return 0;
            } else {
                return 30;
            }
        } else {
            return 0;
        }
    } else {
        return 0;
    }
}

function getbonustenprc($qty1, $qty2)
{
    if ($qty1 && $qty2) {
        return 10;
    } else {
        return 0;
    }
}

function getWeekPeriod($from, $to)
{
    $weeks = [];
    $from = strtotime($from);
    $to = strtotime($to);
    while ($from < $to) {
//  echo "from:\t", date('d.m.Y', $from), RN;
        // номер дня недели начала периода
        $fromDay = date("N", $from);
//            echo "fromDay:\t", $fromDay, RN;
// если не ВС
        if ($fromDay < 7) {
            // кол-во дней до ВС
            $daysToSun = 7 - $fromDay;
//                echo "daysToSun:\t", $daysToSun, RN;
            // конец недельного периода
            $end = strtotime("+ $daysToSun day", $from);
            // если попадаем на след. месяц, то делаем новые вычисления
            if (date("n", $from) != date("n", $end)) {
                $end = strtotime("last day of this month", $from);
            }
            $weeks[] = [date('d.m.Y', $from), date('d.m.Y', $end)];
            $from = $end;
        } else {
            $weeks[] = [date('d.m.Y', $from), date('d.m.Y', $from)];
        }
//            echo "end:\t", date('d.m.Y', $from), RN, RN;
        $from = strtotime("+1 day", $from);
    }
    return $weeks;
}




