<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 14.08.2019
 * Time: 1:51
 */

namespace local\Domain\Repository;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use local\Domain\Entity\CrmEventDealTimfors;
use local\Domain\Factory\CrmEventDealTimforsFactory;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use Bitrix\Main\ORM\Data\DataManager;

class CrmEventDealTimforsRepository
{
    private $hlbl=HIGHLOAD_CRMEVENTDEALTIMFORS;
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
     * @param CrmEventDealTimfors $el
     * @return array
     */
    private function getArParams($el){
        $return=[];
        if($el->getTimelineId())
            $return["UF_TIMELINE_ID"]=$el->getTimelineId();
        if($el->getDealId())
            $return["UF_DEAL_ID"]=$el->getDealId();
        if($el->getStageId())
            $return["UF_STAGE_ID"]=$el->getStageId();
        if($el->getStageName())
            $return["UF_STAGE_NAME"]=$el->getStageName();
        if($el->getType())
            $return["UF_TYPE"]=$el->getType();
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

        if(isset($arr['UF_TIMELINE_ID']))
            $params['TimelineId']=$arr['UF_TIMELINE_ID'];

        if(isset($arr['UF_DEAL_ID']))
            $params['DealId']=$arr['UF_DEAL_ID'];

        if(isset($arr['UF_STAGE_ID']))
            $params['StageId']=$arr['UF_STAGE_ID'];

        if(isset($arr['UF_STAGE_NAME']))
            $params['StageName']=$arr['UF_STAGE_NAME'];

        if(isset($arr['UF_TYPE']))
            $params['Type']=$arr['UF_TYPE'];

        return $params;
    }

    /**
     * @param CrmEventDealTimfors $el
     * @return string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function add(CrmEventDealTimfors $el){
        $entity_data_class=$this->getEntity();
        $result=$entity_data_class::add($this->getArParams($el));
        if (!$result->isSuccess())
            throw new Exception("Error adding item");
        return $result->getId();
    }

    /**
     * @param int $Id
     * @param CrmEventDealTimfors $el
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function update($Id,CrmEventDealTimfors $el){
        $arParams=$this->getArParams($el);
        $entity_data_class=$this->getEntity();
        $rsData = $entity_data_class::getList(array(
            "select" => ["*"],
            "order" => ["ID" => "ASC"],
            "limit" => 1,
            "filter" => ["ID"=>$Id],
        ));
        if($arData = $rsData->Fetch()){
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
     * @return CrmEventDealTimfors
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
            return CrmEventDealTimforsFactory::createFromArray($param);
        }else{
            throw new Exception("Item not found");
        }
    }

    /**
     * @param array $params
     * @return CrmEventDealTimfors[]
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
        return CrmEventDealTimforsFactory::createFromCollection($res);
    }
}