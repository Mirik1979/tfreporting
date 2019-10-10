<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 24.07.2019
 * Time: 21:29
 */

namespace local\Domain\Repository;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use local\Domain\Entity\ResrequestsProductId;
use local\Domain\Factory\ResrequestsProductIdFactory;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use Bitrix\Main\ORM\Data\DataManager;



class ResrequestsProductIdRepository
{
    private $hlbl=HIGHLOAD_RESREQUESTSPRODUCTID;
    /**
     * RepositoryCertificate constructor.
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::includeModule("highloadblock");
    }

    /**
     * @return DataManager
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getEntity(){
        $hlblock = HL\HighloadBlockTable::getById($this->hlbl)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        return $entity_data_class;
    }

    /**
     * @param ResrequestsProductId $el
     * @return array
     */
    private function getArParams($el){
        $return=[];
        if($el->getProductId())
            $return["UF_PRODUCT_ID"]=$el->getProductId();
        if($el->getRoleProject())
            $return["UF_ROLE_PROJECT"]=$el->getRoleProject();
        if($el->getProduct())
            $return["UF_PRODUCT"]=$el->getProduct();
        if($el->getModel())
            $return["UF_MODEL"]=$el->getModel();
        if($el->getGrade())
            $return["UF_GRADE"]=$el->getGrade();
        if($el->getYearsExperience())
            $return["UF_YEARS_EXPERIENCE"]=$el->getYearsExperience();
        if($el->getPreferredLocatio())
            $return["UF_PREFERRED_LOCATIO"]=$el->getPreferredLocatio();
        if($el->getAmountOverhead())
            $return["UF_AMOUNT_OVERHEAD"]=$el->getAmountOverhead();
        if($el->getExecutorName())
            $return["UF_EXECUTOR_NAME"]=$el->getExecutorName();
        if($el->getExecutorId())
            $return["UF_EXECUTOR_ID"]=$el->getExecutorId();
        if($el->getStartWork())
            $return["UF_START_WORK"]=\Bitrix\Main\Type\DateTime::createFromPhp($el->getStartWork());
        if($el->getEndWork())
            $return["UF_END_WORK"]=\Bitrix\Main\Type\DateTime::createFromPhp($el->getEndWork());

        if($el->getRatePerHour())
            $return["UF_RATE_PER_HOUR"]=$el->getRatePerHour();


        return $return;
    }

    /**
     * @param array $arr
     * @return array
     */
    private function getParamToFabric($arr){
        $params=[];

        if(isset($arr['ID']))
            $params['id']=$arr['ID'];

        if(isset($arr['UF_PRODUCT_ID']))
            $params['ProductId']=$arr['UF_PRODUCT_ID'];

        if(isset($arr['UF_ROLE_PROJECT']))
            $params['RoleProject']=$arr['UF_ROLE_PROJECT'];

        if(isset($arr['UF_PRODUCT']))
            $params['Product']=$arr['UF_PRODUCT'];

        if(isset($arr['UF_MODEL']))
            $params['Model']=$arr['UF_MODEL'];

        if(isset($arr['UF_GRADE']))
            $params['Grade']=$arr['UF_GRADE'];

        if(isset($arr['UF_YEARS_EXPERIENCE']))
            $params['YearsExperience']=$arr['UF_YEARS_EXPERIENCE'];

        if(isset($arr['UF_PREFERRED_LOCATIO']))
            $params['PreferredLocatio']=$arr['UF_PREFERRED_LOCATIO'];

        if(isset($arr['UF_AMOUNT_OVERHEAD']))
            $params['AmountOverhead']=$arr['UF_AMOUNT_OVERHEAD'];

        if(isset($arr['UF_START_WORK']))
            $params['StartWork']=$arr['UF_START_WORK'];

        if(isset($arr['UF_END_WORK']))
            $params['EndWork']=$arr['UF_END_WORK'];

        if(isset($arr['UF_EXECUTOR_NAME']))
            $params['ExecutorName']=$arr['UF_EXECUTOR_NAME'];

        if(isset($arr['UF_EXECUTOR_ID']))
            $params['ExecutorId']=$arr['UF_EXECUTOR_ID'];

        if(isset($arr['UF_RATE_PER_HOUR']))
            $params['RatePerHour']=$arr['UF_RATE_PER_HOUR'];

        if(isset($arr['UF_VACANCY']))
            $params['Vacancy']=$arr['UF_VACANCY'];

        return $params;
    }

    /**
     * @param ResrequestsProductId $el
     * @return string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function add(ResrequestsProductId $el){
        $entity_data_class=$this->getEntity();
        $result=$entity_data_class::add($this->getArParams($el));
        if (!$result->isSuccess())
            throw new Exception("Error adding item");
        return $result->getId();
    }

    /**
     * @param int $Id
     * @param ResrequestsProductId $el
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function update($Id,ResrequestsProductId $el){
        $arParams=$this->getArParams($el);
        $entity_data_class=$this->getEntity();
        $rsData = $entity_data_class::getList(array(
            "select" => ["*"],
            "order" => ["ID" => "ASC"],
            "limit" => 1,
            "filter" => ["ID"=>$Id],
        ));
        if($arData = $rsData->Fetch()){
            //\Bitrix\Main\Diag\Debug::writeToFile($arData["ID"]);
            //\Bitrix\Main\Diag\Debug::writeToFile($arParams);
            $result = $entity_data_class::update($arData["ID"],$arParams);
            if (!$result->isSuccess())
                throw new Exception("Item update error");
        }else{
            throw new Exception("Item not found");
        }
        return true;
    }

    /**
     * @param int $Id
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function Delete($Id){
        $entity_data_class=$this->getEntity();
        $rsData = $entity_data_class::getList(array(
            "select" => ["*"],
            "order" => ["ID" => "ASC"],
            "limit" => 1,
            "filter" => ["ID"=>$Id],
        ));
        if($arData = $rsData->Fetch()) {
            $result = $entity_data_class::delete($arData["ID"]);
            if (!$result->isSuccess())
                throw new Exception("Item delete error");
        }else{
            throw new Exception("Item not found");
        }
    }

    /**
     * @param int $Id
     * @return ResrequestsProductId
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function GetById($Id){
        $entity_data_class=$this->getEntity();
        $rsData = $entity_data_class::getList(array(
            "select" => ["*"],
            "order" => ["ID" => "ASC"],
            "limit" => 1,
            "filter" => ["ID"=>$Id],
        ));
        if($arData = $rsData->Fetch()) {
            $param=$this->getParamToFabric($arData);
            return ResrequestsProductIdFactory::createFromArray($param);
        }else{
            throw new Exception("Item not found");
        }
    }

    /**
     * @param array $params
     * @return ResrequestsProductId[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function GetList($params=[]){
        $entity_data_class=$this->getEntity();
        $rsData = $entity_data_class::getList($params);
        $res=[];
        while($arData = $rsData->Fetch()) {
            $param=$this->getParamToFabric($arData);
            $res[]=$param;
        }
        return ResrequestsProductIdFactory::createFromCollection($res);
    }
}