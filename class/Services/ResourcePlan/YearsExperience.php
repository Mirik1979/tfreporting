<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 24.07.2019
 * Time: 0:06
 */

namespace local\Services\ResourcePlan;

use Bitrix\Main\Loader;
use CIBlockElement;

class YearsExperience
{

    private $iblockId=IBLOCK_YEARS_EXPERIENCE;

    /**
     * SeoLinkGenerate constructor.
     */
    public function __construct()
    {
        Loader::includeModule('iblock');
    }

    public function getList(){
        $result=[];
        $arSelect = [
            "ID",
            "NAME",
            "IBLOCK_ID",
            "IBLOCK_TYPE",
        ];
        $arFilter = ["IBLOCK_ID"=>$this->iblockId, "ACTIVE"=>"Y"];
        $res = CIBlockElement::GetList(["SORT"=>"ASC"], $arFilter, false, false, $arSelect);
        while($ob = $res->GetNext())
            $result[]=[
                "ID"=>$ob["ID"],
                "NAME"=>$ob["NAME"],
            ];
        return $result;
    }

    public function getIdByName($name){
        $arSelect = [
            "ID",
            "NAME",
            "IBLOCK_ID",
            "IBLOCK_TYPE",
        ];
        $arFilter = ["IBLOCK_ID"=>$this->iblockId, "ACTIVE"=>"Y","NAME"=>$name];
        $res = CIBlockElement::GetList(["SORT"=>"ASC"], $arFilter, false, ['nTopCount'=>1], $arSelect);
        if($ob = $res->GetNext())
            return $ob["ID"];
        return false;
    }

}