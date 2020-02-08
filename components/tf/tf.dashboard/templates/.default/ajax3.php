<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
use Bitrix\Main\Loader;
use Bitrix\Crm\Timeline\Entity\TimelineTable;
//echo "Ich liebe Welt";
$from = ($_REQUEST['datebegin'])?$_REQUEST['datebegin']:date("d.m.Y");
$to = ($_REQUEST['dateend'])?$_REQUEST['dateend']:date("d.m.Y");
//print_r($_REQUEST);

if (\Bitrix\Main\Loader::includeModule('crm'))
{
    $event = CCrmEvent::GetList(array("ID" => ASC), array( /*"CREATED_BY_ID" => 59 ,*/
        "ENTITY_TYPE" => 'DEAL',
        "ENTITY_FIELD" => 'STAGE_ID',
        ">=DATE_CREATE" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
        "<=DATE_CREATE" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59"), $nPageTop = false);
    //print_r($event);
    $co = 0;
    $coios = 0;
    $coand = 0;
    $cosap = 0;
    $cojava = 0;
    $wco = 0;
    $wcoios = 0;
    $wcoand = 0;
    $wcosap = 0;
    $wcojava = 0;
    $coint = 0;
    $wcoint = 0;
    $cointh = 0;
    $wcointh = 0;
    $wrefuse = 0;
    $refuse = 0;

    while ($contacts31=$event->GetNext()) {
        if ($contacts31['EVENT_TEXT_1'] == 'Внутреннее интервью') {
            //echo "<pre>";
            //print_r($contacts31['ENTITY_ID']);
            //echo "</pre>";
            $res1 = CCrmDeal::GetList(array(), array("ID" => $contacts31['ENTITY_ID']));
            while($ob1 = $res1->GetNext()) {
                //echo "<pre>";
                //print_r($ob1);
                //echo "</pre>";
                if ($ob1['COMPANY_ID']) {
                    if ($ob1['UF_CRM_1566668516'] == 545) {
                        if (in_array(442, $ob1['UF_CRM_1566680677']) ||
                            in_array(443, $ob1['UF_CRM_1566680677'])) {
                            $coios++;
                        } else /*if (in_array(469, $ob1['UF_CRM_1566680677']) ||
                            in_array(468, $ob1['UF_CRM_1566680677'])) */ {
                            $coand++;
                        }
                    } elseif ($ob1['UF_CRM_1566668516'] == 544) {
                        $cosap++;
                    } elseif ($ob1['UF_CRM_1566668516'] == 546) {
                        if (in_array(435, $ob1['UF_CRM_1566680677'])) {
                            $cojava++;
                        }


                    } else {
                        $co++;
                    }
                } else {
                    if ($ob1['UF_CRM_1566668516']==545) {
                        if(in_array(442, $ob1['UF_CRM_1566680677']) ||
                            in_array(443, $ob1['UF_CRM_1566680677'])) {
                            $wcoios++;
                        } else /*if (in_array(469, $ob1['UF_CRM_1566680677']) ||
                            in_array(468, $ob1['UF_CRM_1566680677'])) */ {
                            $wcoand++;
                        }
                    } elseif ($ob1['UF_CRM_1566668516']==544) {
                        $wcosap++;
                    } elseif ($ob1['UF_CRM_1566668516'] == 546) {
                        if (in_array(435, $ob1['UF_CRM_1566680677'])) {
                        $wcojava++;
                        }
                        }  else {
                        $wco++;
                    }
                }
            }
        }
    }

    $res = CCrmActivity::GetList(array(), array("PROVIDER_ID" => 'CRM_MEETING', 'OWNER_TYPE_ID' => 2,
        'RESPONSIBLE_ID' => 59,
        ">=CREATED" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
        "<=CREATED" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59"));

    while($ob = $res->GetNext())
    {
        $res1 = CCrmDeal::GetList(array(), array("ID" => $ob['OWNER_ID']));
        while($ob1 = $res1->GetNext()) {
            if ($ob1['COMPANY_ID']) {
                $resresult['shifr']['coint'][$coint] = $ob1['CONTACT_FULL_NAME'];
                $coint++;
            } else {
                $resresult['shifr']['wcoint'][$wcoint] = $ob1['CONTACT_FULL_NAME'];
                $wcoint++;
            }
        }
    }

    $res = CCrmActivity::GetList(array(), array("PROVIDER_ID" => 'CRM_MEETING', 'OWNER_TYPE_ID' => 2,
        'RESPONSIBLE_ID' => 59,  'COMPLETED' => 'Y',
        ">=END_TIME" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
        "<=END_TIME" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59"));

    while($ob = $res->GetNext())
    {
        $res1 = CCrmDeal::GetList(array(), array("ID" => $ob['OWNER_ID']));
        while($ob1 = $res1->GetNext()) {
            if ($ob1['COMPANY_ID']) {
                $resresult['shifr']['cointh'][$cointh] = $ob1['CONTACT_FULL_NAME'];
                $cointh++;
            } else {
                $resresult['shifr']['wcointh'][$wcointh] = $ob1['CONTACT_FULL_NAME'];
                $wcointh++;
            }
        }
    }
    // старый блок вывода
    /*$event = CCrmEvent::GetList(array("ID" => ASC), array(
        "ENTITY_TYPE" => 'DEAL',
        "ENTITY_FIELD" => 'STAGE_ID',
        ">=DATE_CREATE" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
        "<=DATE_CREATE" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59"), $nPageTop = false);

    $offer = 0;
    $woffer = 0;

    while ($contacts31=$event->GetNext()) {
        if ($contacts31['EVENT_TEXT_2'] == 'Вышел на проект') {
            $res1 = CCrmDeal::GetList(array(), array("ID" => $contacts31['ENTITY_ID']));
            while($ob1 = $res1->GetNext()) {
                if ($ob1['COMPANY_ID']) {
                    $offer++;
                } else {
                    $woffer++;
                }
            }
        }
    } */
    // новый блок вывода
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

    $event = CCrmEvent::GetList(array("ID" => ASC), array( /*"CREATED_BY_ID" => 59 ,*/
        "ENTITY_TYPE" => 'DEAL',
        "ENTITY_FIELD" => 'STAGE_ID',
        ">=DATE_CREATE" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
        "<=DATE_CREATE" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59"), $nPageTop = false);

    $offer = 0;
    $woffer = 0;

    while ($contacts31=$event->GetNext()) {
        if ($contacts31['EVENT_TEXT_2'] == 'Отказ кандидата') {
            $res1 = CCrmDeal::GetList(array(), array("ID" => $contacts31['ENTITY_ID']));
            while($ob1 = $res1->GetNext()) {
                if ($ob1['COMPANY_ID']) {
                    $refuse++;
                } else {
                    $wrefuse++;
                }
            }
        }
    }

    $testmark = 0;
    $wtestmark = 0;

    $event = CCrmEvent::GetList(array("ID" => ASC), array( /*"CREATED_BY_ID" => 59 ,*/
        "ENTITY_TYPE" => 'DEAL',
        "ENTITY_FIELD" => 'STAGE_ID',
        ">=DATE_CREATE" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
        "<=DATE_CREATE" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59"), $nPageTop = false);

    while ($contacts31=$event->GetNext()) {
        if ($contacts31['EVENT_TEXT_1'] == 'Тестовое задание') {
            //print_r('test');
            $res1 = CCrmDeal::GetList(array(), array("ID" => $contacts31['ENTITY_ID']));
            while($ob1 = $res1->GetNext()) {
                if ($ob1['COMPANY_ID']) {
                    //$testmark = $testmark + $ob1['UF_CRM_1565804568'];
                    $testmark++;
                } else {
                    $wtestmark++;
                }
            }
        }
    }

    $testmarkn = 0;
    $wtestmarkn = 0;
    $testmarkc = 0;
    $wtestmarkc = 0;

    $event = CCrmEvent::GetList(array("ID" => ASC), array( /*"CREATED_BY_ID" => 59 ,*/
        "ENTITY_TYPE" => 'DEAL',
        "ENTITY_FIELD" => 'STAGE_ID',
        ">=DATE_CREATE" => ConvertDateTime($from, "DD.MM.YYYY")." 00:00:00",
        "<=DATE_CREATE" => ConvertDateTime($to, "DD.MM.YYYY")." 23:59:59"), $nPageTop = false);

    while ($contacts31=$event->GetNext()) {
        if ($contacts31['EVENT_TEXT_1'] == 'Тестовое задание') {
            //print_r('test');
            $res1 = CCrmDeal::GetList(array(), array("ID" => $contacts31['ENTITY_ID']));
            while($ob1 = $res1->GetNext()) {
                if ($ob1['COMPANY_ID'] && $ob1['UF_CRM_1565804568']) {
                    $testmarkn = $testmarkn + $ob1['UF_CRM_1565804568'];
                    $testmarkc++;
                    $mid = round($testmarkn / $testmarkc, 2);
                } elseif ($ob1['UF_CRM_1565804568']) {
                    $wtestmarkn = $wtestmarkn + $ob1['UF_CRM_1565804568'];
                    $wtestmarkc++;
                    //$wmid = $wtestmarkn / $wtestmarkc;
                    $wmid = round($wtestmarkn / $wtestmarkc, 2);
                }
            }
        }
    }

    $wcointnames = implode(",", $resresult['shifr']['wcoint']);
    $wcointhnames = implode(",", $resresult['shifr']['wcointh']);
    $cointnames = implode(",", $resresult['shifr']['coint']);
    $cointhnames = implode(",", $resresult['shifr']['cointh']);

    echo "<table id='sobeslist'><tr><th class='tablekpititle'>Источник CV</th>
    <th class='tablekpititle'>Получено iOS</th><th class='tablekpititle'>Получено Android</th>
    <th class='tablekpititle'>Получено SAP</th><th class='tablekpititle'>Получено Java</th>
    <th class='tablekpititle'>Получено прочие</th><th class='tablekpititle'>
    Назначено интервью</th><th class='tablekpititle'>
    Проведено интервью</th><th class='tablekpititle'>
    Job offer</th><th class='tablekpititle'>
    Отказ кандидата</th><th class='tablekpititle'>
    Прошли тестовые задания</th><th class='tablekpititle'>
    Средний балл</th></tr>";
    echo "<tr><td class='tablekpititle'>Внутренние источники</td><td class='tablekpi'>".$wcoios."</td>
    <td class='tablekpi'>".$wcoand."</td><td class='tablekpi'>".$wcosap."</td><td class='tablekpi'>".$wcojava."</td>
    <td class='tablekpi'>".$wco."</td>
    <td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$wcointnames ."'); return false;\">".$wcoint."</a>
    </td>
    <td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$wcointhnames."'); return false;\">".$wcointh."</a>
    </td><td class='tablekpi'>
    <a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$resresult['shifr']['recvyvod']."'); return false;\">".($resresult['recvyvod']?$resresult['recvyvod']:0)."
    </a>
    </td>
    <td class='tablekpi'>".$wrefuse."</td><td class='tablekpi'>".$wtestmark."</td>
    <td class='tablekpi'>".$wmid."</td></tr>";
    echo "<tr><td class='tablekpititle'>Партнеры</td><td class='tablekpi'>".$coios."</td>
    <td class='tablekpi'>".$coand."</td><td class='tablekpi'>".$cosap."</td><td class='tablekpi'>".$cojava."</td>
    <td class='tablekpi'>".$co."</td>    
    <td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$cointnames."'); return false;\">".$coint."</a></td>
    <td class='tablekpi'><a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$cointhnames."'); return false;\">".$cointh."</a></td><td class='tablekpi'>
    <a href=\"#\" onclick=\"alert('Вышедшие специалисты: ".$resresult['shifr']['resvyvod']."'); return false;\">".
        ($resresult['resvyvod']?$resresult['resvyvod']:0)."</a>   
    </td>
    <td class='tablekpi'>".$refuse."</td><td class='tablekpi'>".$testmark."</td><td class='tablekpi'>".$mid."</td></tr>";
    echo "</table>";
}

function getname($iduser) {
    $rsUser = CUser::GetByID($iduser);
    $arUser = $rsUser->Fetch();
    return $arUser['NAME']." ".$arUser['LAST_NAME'];
}