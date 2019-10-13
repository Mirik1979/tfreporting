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
$dates = getWeekPeriod($from, $to);
//print_r($dates);
if ($_REQUEST['detailedpers'] && !$_REQUEST['detailedweek']) {
    $reqresult1 = Array();
} elseif (!$_REQUEST['detailedpers'] && $_REQUEST['detailedweek']) {
    $reqresult2 = Array();
    $detweek = true;
} elseif ($_REQUEST['detailedpers'] && $_REQUEST['detailedweek']) {
    $reqresult3 = Array();
    $detweek = true;
} else {
    $reqresult4 = Array();
}

if ($detweek) {
    echo "<table><tr><th class='tablekpi'>Партнеры. KPI Ресурсного менеджера</th>";
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
    echo "<th class='tablekpi'>Общее Количество</th><th class='tablekpi'>% от бонусного фонда</th></tr></table>";
} else {
    echo "<table><tr><th class='tablekpi'>Партнеры. KPI Ресурсного менеджера</th><th class='tablekpi'>Количество</th><th class='tablekpi'>% от бонусного фонда</th></tr></table>";
}

if (Loader::includeModule('crm')) {
    $res = CCrmCompany::GetList(array("ID" => ASC), array());
    while ($ob = $res->GetNext()) {
        echo "<pre>";
        print_r($ob);
        echo "</pre>";
    }
    $rs=TimelineTable::getList(array(
        'order' => array("ID" => "DESC"),
        'filter' => array(
            '=TYPE_ID' => 7,
            '=CRM_TIMELINE_ENTITY_TIMELINE_BINDINGS_ENTITY_TYPE_ID' => 4
        ),
        'select'=>array("*", "BINDINGS")
    ));
    while($ar = $rs->Fetch())
    {
        echo "<pre>";
        print_r($ar);
        echo "</pre>";
    }
}

$arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM", "CREATED_DATE", "PROPERTY_152");
$arFilter = Array("IBLOCK_ID"=>54, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
$res1 = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
while($ob = $res1->GetNextElement())
{
    echo "<pre>";
    print_r($ob);
    echo "</pre>";
}



function getWeekPeriod($from, $to)
{
    $weeks = [];
    $from = strtotime($from);
    $to = strtotime($to);
    while ($from < $to) {
//            echo "from:\t", date('d.m.Y', $from), RN;
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




