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
$arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM", "DATE_CREATE", "PROPERTY_152");
$arFilter = Array("IBLOCK_ID"=>54, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y",
    ">=DATE_CREATE" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
    "<=DATE_CREATE" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59");
$vyvod = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);

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
    echo "<th class='tablekpititle'>Общее Количество</th><th class='tablekpi'>% от бонусного фонда</th></tr></table>";
} else {
    echo "<table><tr><th class='tablekpititle'>Партнеры. KPI Ресурсного менеджера</th><th class='tablekpi'>Количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
}
// строим запросы и наполняем детальную часть
if ($var==1) {
    while ($vyvodinfo = $vyvod->GetNextElement()) {
        $arVyvod = $vyvodinfo->GetFields();
        //echo "<pre>";
        //print_r($arVyvod);
        //echo "</pre>";
        if ($arVyvod['PROPERTY_152_VALUE']!=0) {
            $company = CCrmCompany::GetbyID($arVyvod['PROPERTY_152_VALUE']);
            if ($company['COMPANY_TYPE']=='SUPPLIER' || $company['COMPANY_TYPE']=='1') {
                $resresult['resvyvod'] = $resresult['resvyvod'] + 1;
            } else {
                $resresult['recvyvodfl'] = $resresult['recvyvodfl'] + 1;
            }
        } else {
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
        if ($created < ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59") {
            if ($created > ConvertDateTime($from, "DD.MM.YYYY")." 23:59:59") {

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
    echo "<tr><th class='tablekpititle'>1. Поддержание активноси с партнерами:  выводы / комментарии</th>
            <th class='tablekpi'>".($resresult['resvyvod']?$resresult['resvyvod']:0)."/".($resresult['rescall']?$resresult['rescall']:0)."
            </th><th class='tablekpi'>".getbonustenprc($resresult['resvyvod'], $resresult['rescall'])."</th></tr>";
    echo "<tr><th class='tablekpititle'>2. Привлечение нового партнера</th>
            <th class='tablekpi'>".($resresult['newpartnres']?$resresult['newpartnres']:0)."
            </th><th class='tablekpi'>".getbonusperc($resresult['newpartnres'], $bonustype, 'newpartn')."</th></tr>";
    echo "<tr><th class='tablekpititle'>3. Число выведеннных специалистов</th>
        <th class='tablekpi'>".($resresult['resvyvod']?$resresult['resvyvod']:0)."</th><th class='tablekpi'>".getbonusperc($resresult['resvyvod'], $bonustype, 'vyvodres')."</th></tr></table>";
    echo "<br>";
    echo "<table><tr><th class='tablekpititle'>Открытый рынок. KPI Рекрутеров</th><th class='tablekpi'>Количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
    echo "<tr><th class='tablekpititle'>1. Число выведеннных специалистов</th>
        <th class='tablekpi'>".($resresult['recvyvod']?$resresult['recvyvod']:0)."</th><th class='tablekpi'>".getbonusperc($resresult['recvyvod'], $bonustype, 'vyvodrec')."</th></table>";
    echo "<br>";
    echo "<table><tr><th class='tablekpititle'>Открытый рынок. KPI Белик</th><th class='tablekpi'>Количество</th><th class='tablekpi'>% от бонусного фонда</th></tr>";
    echo "<tr><th class='tablekpititle'>1. Поддержание активноси с фрилансерами:  выводы / комментарии</th>
            <th class='tablekpi'>".($resresult['recvyvodfl']?$resresult['recvyvodfl']:0)."/".($resresult['reccallfl']?$resresult['reccallfl']:0)."
            </th><th class='tablekpi'>".getbonustenprc($resresult['recvyvodfl'], $resresult['reccallfl'])."</th></tr>";
    echo "<tr><th class='tablekpititle'>2. Привлечение нового фрилансера</th>
            <th class='tablekpi'>".($resresult['newpartnfl']?$resresult['newpartnfl']:0)."
            </th><th class='tablekpi'>".getbonusperc($resresult['newpartnfl'], $bonustype, 'newpartnfl')."</th></tr>";
    echo "<tr><th class='tablekpititle'>3. Число выведеннных специалистов</th>
        <th class='tablekpi'>".($resresult['recvyvodfl']?$resresult['recvyvodfl']:0)."</th><th class='tablekpi'>".getbonusperc($resresult['recvyvodfl'], $bonustype, 'vyvodfl')."</th></table>";
} elseif ($var==2) {



} elseif ($var==3) {



} else {


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




