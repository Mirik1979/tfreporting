<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define("EXTRANET_NO_REDIRECT", true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

use local\Services\ResourcePlan;
use local\Services\XLSXReader;
use local\Domain\Repository\ResrequestsProductIdRepository;

$ResrequestsProductIdRepository=new ResrequestsProductIdRepository();
$RoleProject=new ResourcePlan\RoleProject();
$YearsExperience=new ResourcePlan\YearsExperience();
$Product=new ResourcePlan\Product();
$PreferredLocation=new ResourcePlan\PreferredLocation();
$Grade=new ResourcePlan\Grade();
$Model=new ResourcePlan\Model();

if (!CModule::IncludeModule('crm'))
{
    return;
}

global $USER, $APPLICATION;

if(!function_exists('__CrmPropductRowListEndResponse'))
{
    function __CrmPropductRowListEndResponse($result)
    {
        $GLOBALS['APPLICATION']->RestartBuffer();
        header('Content-Type: application/json; charset='.LANG_CHARSET);


        if(!empty($result))
        {
            echo json_encode($result);
        }
        require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
        die();
    }
}

if (!$USER->IsAuthorized() || /*!check_bitrix_sessid() ||*/ $_SERVER['REQUEST_METHOD'] != 'POST')
{
    return;
}

CUtil::JSPostUnescape();
$APPLICATION->RestartBuffer();
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);


if ( 0 < $_FILES['file']['error'] ) {
    __CrmPropductRowListEndResponse(array('error'=>'Ошибка загрузки файла'));
}else{

    $filename=$_FILES['file']['name'];
    $filetype=$_FILES['file']['type'];
    $filename = strtolower($filename);
    $filetype = strtolower($filetype);

    $pos = strpos($filename,'php');
    if(!($pos === false)) {
        __CrmPropductRowListEndResponse(array('error'=>'Файл являеться скриптом'));
    }


    $file_ext = strrchr($filename, '.');

    //check if its allowed or not
    $whitelist = array(".xlsx");
    if (!(in_array($file_ext, $whitelist))) {
        __CrmPropductRowListEndResponse(array('error'=>'Неправильное разрешение файла'));
    }

    date_default_timezone_set('UTC');

    $xlsx = new XLSXReader($_FILES['file']['tmp_name']);

    $groupid=(int)$_REQUEST["groupid"];
    $ownerid=(int)$_REQUEST["ownerid"];
    $row=[];

    //if($groupid>0){


        $res=\CCrmDeal::LoadProductRows($ownerid);

        $count=1;

        foreach($res as $val){
            $new=$val;
            $new["SORT"]=$count*10;
            $count++;
            $row[]=$new;
        }

        $sheetNames = $xlsx->getSheetNames();

        $i=0;

        foreach($sheetNames as $sheetName) {

            if($i>0)
                continue;

            $sheet = $xlsx->getSheet($sheetName);
            $data=$sheet->getData();

            for ($i=1;$i<count($data);$i++){

                $table=$data[$i];

                $PreferredLocation=new ResourcePlan\PreferredLocation();

                $START_WORK = trim(isset($table[9]) ? date('d.m.Y H:i:s', XLSXReader::toUnixTimeStamp($table[9])) : '');
                $END_WORK = trim(isset($table[10]) ? date('d.m.Y H:i:s', XLSXReader::toUnixTimeStamp($table[10])) : '');
                $checkTime=new \local\Services\ResourcePlan\checkTime();
                $resultTime=$checkTime->checkStartWork($groupid,$START_WORK,$END_WORK);

                $new=[
                    "ID" => 0,
                    "PRODUCT_NAME" => $table[0],
                    "PRODUCT_ID" => 0,
                    "QUANTITY" => $checkTime->getQuantity($resultTime["DATE_START"]["VALUE"],$resultTime["DATE_FINISH"]["VALUE"]),
                    "MEASURE_CODE" => 796,
                    "MEASURE_NAME" => "шт",
                    "PRICE" => $table[7],
                    "PRICE_EXCLUSIVE" => $table[7],
                    "ROLE_PROJECT" => $RoleProject->getIdByName($table[1]),
                    "PRODUCT" => $Product->getIdByName($table[2]),
                    "MODEL" => $Model->getIdByName($table[3]),
                    "GRADE" => $Grade->getIdByName($table[4]),
                    "YEARS_EXPERIENCE" => $YearsExperience->getIdByName($table[5]),
                    "PREFERRED_LOCATION" => $PreferredLocation->getIdByName($table[6]),
                    "AMOUNT_OVERHEAD" => $table[8],
                    "START_WORK" => $resultTime["DATE_START"]["VALUE"],
                    "END_WORK" => $resultTime["DATE_FINISH"]["VALUE"],
                    "CUSTOMIZED" => "Y",
                    "SORT" => $count*10,

                    //"PRICE_NETTO" => 10.00,
                    //"PRICE_BRUTTO" => 10.00,
                    //"DISCOUNT_TYPE_ID" => 2,
                    //"DISCOUNT_RATE" => 0.00,
                    //"DISCOUNT_SUM" => 0.00,
                    //"TAX_RATE" => 0.00,
                    //"TAX_INCLUDED" =>"N",
                ];

                $count++;



                $row[]=$new;

            }

            $i++;

        }
 //   }

    if(count($row)>0)
        \CCrmDeal::SaveProductRows($ownerid, $row, true, true, false);

    $res=\CCrmDeal::LoadProductRows($ownerid);
    $summ=0;
    foreach($res as $val){
        $AmountOverhead=0;
        $search = $ResrequestsProductIdRepository->GetList([
            'limit' => 1,
            'filter' => [
                "UF_PRODUCT_ID" => $val['ID'],
            ]
        ]);
        /**
         * @var ResrequestsProductId $ResrequestsProductArray
         */
        if (count($search) == 1){
            $ResrequestsProductArray = $search[0];
            $AmountOverhead=$ResrequestsProductArray->getAmountOverhead();
        }

        $summ+=($val["PRICE"]*$val["QUANTITY"])+$AmountOverhead;
    }


    $entity = new \CCrmDeal(false);

    $Fields=["OPPORTUNITY"=>$summ];
    $entity->Update($ownerid,$Fields);

    __CrmPropductRowListEndResponse(array('sucsess'=>'Y'));
}


