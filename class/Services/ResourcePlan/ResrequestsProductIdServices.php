<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 24.07.2019
 * Time: 21:57
 */

namespace local\Services\ResourcePlan;

use Bitrix\Main;
use Bitrix\Crm\Security\EntityAuthorization;
use Bitrix\Crm\Synchronization\UserFieldSynchronizer;
use Bitrix\Crm\Conversion\DealConversionConfig;
use Bitrix\Crm\Conversion\DealConversionWizard;
use Bitrix\Crm\Recurring;
use Bitrix\Crm;

use local\Domain\Repository\ResrequestsProductIdRepository;
use local\Domain\Entity\ResrequestsProductId;

class ResrequestsProductIdServices
{

    public static function OnAfterCrmDealProductRowsSave($ID, $arRows){
        self::setProductRow($ID,$arRows);
        //\Bitrix\Main\Diag\Debug::writeToFile($ID);
        //\Bitrix\Main\Diag\Debug::writeToFile($arRows);
    }

    public static function setProductRow($id,$r){

        \CModule::IncludeModule('crm');

        $ResrequestsProductIdRepository=new ResrequestsProductIdRepository();

        $res=\CCrmDeal::LoadProductRows($id);

        foreach ($r as $key=>$v) {
            $resId = $res[$key]["ID"];
            $ResrequestsProductId = new ResrequestsProductId();

            $ResrequestsProductId->setProductId($resId);

            if(isset($v["ROLE_PROJECT"]))
                $ResrequestsProductId->setRoleProject($v["ROLE_PROJECT"]);
            if(isset($v["PRODUCT"]))
                $ResrequestsProductId->setProduct($v["PRODUCT"]);
            if(isset($v["MODEL"]))
                $ResrequestsProductId->setModel($v["MODEL"]);
            if(isset($v["GRADE"]))
                $ResrequestsProductId->setGrade($v["GRADE"]);
            if(isset($v["YEARS_EXPERIENCE"]))
                $ResrequestsProductId->setYearsExperience($v["YEARS_EXPERIENCE"]);
            if(isset($v["PREFERRED_LOCATION"]))
                $ResrequestsProductId->setPreferredLocatio($v["PREFERRED_LOCATION"]);
            if(isset($v["AMOUNT_OVERHEAD"]))
                $ResrequestsProductId->setAmountOverhead($v["AMOUNT_OVERHEAD"]);

            if (isset($v["START_WORK"]))
                $ResrequestsProductId->setStartWork(new \DateTime($v["START_WORK"]));

            if (isset($v["START_WORK"]))
                $ResrequestsProductId->setEndWork(new \DateTime($v["END_WORK"]));

            $search = $ResrequestsProductIdRepository->GetList([
                'limit' => 1,
                'filter' => [
                    "UF_PRODUCT_ID" => $resId,
                ]
            ]);

            if (count($search) == 1) {
                $s = $search[0];
                $ResrequestsProductIdRepository->update($s->getId(), $ResrequestsProductId);
            } else {
                $ResrequestsProductIdRepository->add($ResrequestsProductId);
            }
        }
    }

}


/*
 *         $id=20;

        $r=[
            [
                "ID" => 6,
                "PRODUCT_NAME" => "Тест",
                "PRODUCT_ID" => 0,
                "QUANTITY" => 72.0000,
                "MEASURE_CODE" => 796,
                "MEASURE_NAME" => "шт",
                "PRICE" => 10.00,
                "PRICE_EXCLUSIVE" => 10.00,
                "PRICE_NETTO" => 10.00,
                "PRICE_BRUTTO" => 10.00,
                "DISCOUNT_TYPE_ID" => 2,
                "ROLE_PROJECT" => 178,
                "PRODUCT" => 180,
                "MODEL" => 0,
                "GRADE" => 185,
                "YEARS_EXPERIENCE" => 187,
                "PREFERRED_LOCATION" => 191,
                "AMOUNT_OVERHEAD" => 111.00,
                "START_WORK" => "16.07.2019 22:03:00",
                "END_WORK" => "26.07.2019 22:03:00",
                "DISCOUNT_RATE" => 0.00,
                "DISCOUNT_SUM" => 0.00,
                "TAX_RATE" => 0.00,
                "TAX_INCLUDED" =>"N",
                "CUSTOMIZED" => "Y",
                "SORT" => 10,
            ],
            [
                "ID" => 0,
                "PRODUCT_NAME" => "Тест2",
                "PRODUCT_ID" => 0,
                "QUANTITY" => 16.0000,
                "MEASURE_CODE" => 796,
                "MEASURE_NAME" => "шт",
                "PRICE" => 21.00,
                "PRICE_EXCLUSIVE" => 21.00,
                "PRICE_NETTO" => 21.00,
                "PRICE_BRUTTO" => 21.00,
                "DISCOUNT_TYPE_ID" => 2,
                "ROLE_PROJECT" => 179,
                "PRODUCT" => 181,
                "MODEL" => 184,
                "GRADE" => 186,
                "YEARS_EXPERIENCE" => 188,
                "PREFERRED_LOCATION" => 191,
                "AMOUNT_OVERHEAD" => 140.00,
                "START_WORK" => "24.07.2019 22:03:00",
                "END_WORK" => "27.07.2019 22:03:00",
                "DISCOUNT_RATE" => 0.00,
                "DISCOUNT_SUM" => 0.00,
                "TAX_RATE" => 0.00,
                "TAX_INCLUDED" => "N",
                "CUSTOMIZED" => "Y",
                "SORT" => 20,
            ],
            [
                "ID" => 0,
                "PRODUCT_NAME" => "Тест3",
                "PRODUCT_ID" => 0,
                "QUANTITY" => 8.0000,
                "MEASURE_CODE" => 796,
                "MEASURE_NAME" => "шт",
                "PRICE" => 10.00,
                "PRICE_EXCLUSIVE" => 10.00,
                "PRICE_NETTO" => 10.00,
                "PRICE_BRUTTO" => 10.00,
                "DISCOUNT_TYPE_ID" => 2,
                "ROLE_PROJECT" => 178,
                "PRODUCT" => 180,
                "MODEL" => 0,
                "GRADE" => 185,
                "YEARS_EXPERIENCE" => 188,
                "PREFERRED_LOCATION" => 191,
                "AMOUNT_OVERHEAD" => 0.10,
                "START_WORK" => "24.07.2019 22:03:00",
                "END_WORK" => "26.07.2019 22:03:00",
                "DISCOUNT_RATE" => 0.00,
                "DISCOUNT_SUM" => 0.00,
                "TAX_RATE" => 0.00,
                "TAX_INCLUDED" => "N",
                "CUSTOMIZED" => "Y",
                "SORT" => 30,
            ],
        ];
 */