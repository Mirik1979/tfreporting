<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 10.08.2019
 * Time: 23:52
 */

namespace local\Services;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Crm\Timeline\Entity\TimelineTable;
use Bitrix\Main\Entity\Event;
use CCrmDeal;
use CCrmStatus;
use CCrmContact;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use local\Domain\Repository\CrmEventDealTimforsRepository;
use local\Domain\Factory\CrmEventDealTimforsFactory;

class CrmEventDealTimfors
{

    /**
     * CrmEventDealTimfors constructor.
     */
    public function __construct()
    {
        Loader::includeModule("crm");
    }

    /**
     * @param int $id
     * @param bool $TIMELINE
     * @return string
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getNameAsId($id,$TIMELINE=true){
        $filter=["UF_TIMELINE_ID"=>$id];
        if($TIMELINE)
            $filter["UF_TYPE"]='TIMELINE';
        else
            $filter["UF_TYPE"]='ACTIVITY';
        $CrmEventDealTimforsRepository=new CrmEventDealTimforsRepository();
        $el=$CrmEventDealTimforsRepository->GetList([
            "select" => ["*"],
            "order" => ["ID" => "ASC"],
            "limit" => 1,
            "filter" => $filter,
        ]);
        if(count($el)>0)
            return "Статус: ".$el[0]->getStageName();
        else{
            $CrmEventDealTimfors=new self();
            $param=$CrmEventDealTimfors->getEventInfo($id,false);
            if($param){
                if($TIMELINE)
                    $param["Type"]='TIMELINE';
                else
                    $param["Type"]='ACTIVITY';
                $CrmEventDealTimforsRepository->add(CrmEventDealTimforsFactory::createFromArray($param));
                $el=$CrmEventDealTimforsRepository->GetList([
                    "select" => ["*"],
                    "order" => ["ID" => "ASC"],
                    "limit" => 1,
                    "filter" => $filter,
                ]);
                if(count($el)>0)
                    return "Статус: ".$el[0]->getStageName();
            }
        }
        return "";
    }

    /**
     * @param int $EVENT_ID
     * @param array $arFields
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function OnAfterCrmAddEventActivity($EVENT_ID, $arFields){
        if($arFields["OWNER_TYPE_ID"]==2 && $arFields["OWNER_ID"]>0 && in_array($arFields['PROVIDER_ID'],['TASKS','CRM_MEETING','CRM_EMAIL','VOXIMPLANT_CALL'])){
            $res = CCrmDeal::GetList([],[
                'CHECK_PERMISSIONS'=> 'N',
                'ID'=>$arFields["OWNER_ID"],
            ],[],1);
            if($arr=$res->GetNext()) {
                $STAGE_ID = $arr["STAGE_ID"];
                $STAGE = CCrmStatus::GetList([], ['STATUS_ID' => $STAGE_ID /*, 'ENTITY_ID' => 'DEAL_STAGE'*/])->GetNext();
                $arrResult = [
                    "Type" => 'ACTIVITY',
                    "TimelineId" => $EVENT_ID,
                    "DealId" => $arFields["OWNER_ID"],
                    "StageId" => $arr["STAGE_ID"],
                    "StageName" => $STAGE["NAME"],
                ];
                $CrmEventDealTimforsRepository=new CrmEventDealTimforsRepository();
                $CrmEventDealTimforsRepository->add(CrmEventDealTimforsFactory::createFromArray($arrResult));
            }
        }
    }

    /**
     * @param Event $event
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function OnAfterCrmAddEvent(Event $event){
        $id = $event->getParameter("id");
        $fields = $event->getParameter("fields");
        $CrmEventDealTimfors=new self();
        $param=$CrmEventDealTimfors->getEventInfo($id);
        if($param && in_array($fields['ASSOCIATED_ENTITY_CLASS_NAME'],['TASKS','CRM_MEETING','CRM_EMAIL','VOXIMPLANT_CALL'])){
            $param["Type"]='TIMELINE';
            $CrmEventDealTimforsRepository=new CrmEventDealTimforsRepository();
            $CrmEventDealTimforsRepository->add(CrmEventDealTimforsFactory::createFromArray($param));
        }
    }

    /**
     * @param int $EVENT_ID
     * @param bool $isEvent
     * @return array|bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
	  * @throws SystemException
     */

	public function getEventInfo($EVENT_ID,$isEvent=true){
        $arrResult=false;
        $dealId=0;
        $rs=TimelineTable::getList(array(
            'order' => array("ID" => "DESC"),
            'filter' => array(
                '=ID' => $EVENT_ID
            ),
            'select'=>array("*", "BINDINGS")
        ));
        $new=[];
        $Event=[];
        while($ar = $rs->Fetch())
        {
			$Event=$ar;
            $new[]=$ar;
            if($ar['CRM_TIMELINE_ENTITY_TIMELINE_BINDINGS_ENTITY_TYPE_ID']==2)
                $dealId=(int)$ar['CRM_TIMELINE_ENTITY_TIMELINE_BINDINGS_ENTITY_ID'];
			if($ar['COMMENT']) {
				if(Loader::includeModule("bizproc"))
				{
					$d = $ar['CRM_TIMELINE_ENTITY_TIMELINE_BINDINGS_ENTITY_ID']; 
					$arWorkflowParameters = array("comment" => $ar['COMMENT']);
					$deal = 'DEAL_'.$d;
					$wfId = \CBPDocument::StartWorkflow(
								76,
								array("crm","CCrmDocumentDeal", $deal),
								$arWorkflowParameters,
								$arErrorsTmp
					);
				}
			}


        }

        if($dealId<=0){
            \Bitrix\Crm\Timeline\TimelineManager::prepareDisplayData($new, 0, false);
            foreach ($new as $ar){
                if($ar["ASSOCIATED_ENTITY"]["OWNER_TYPE_ID"]==2)
                    $dealId=(int)$ar['ASSOCIATED_ENTITY']["OWNER_ID"];
                if($ar["ASSOCIATED_ENTITY_TYPE_ID"]==3 && $ar["SETTINGS"]["letterId"] && $ar["SETTINGS"]["recipient"] && is_array($ar["SETTINGS"]["recipient"])){
                    $CCrmDeal=new CCrmContact(false);
                    $arFields=["UF_CRM_1568876411"=>"Y"];
                    $CCrmDeal->Update($ar["ASSOCIATED_ENTITY_ID"],$arFields);
                }
            }
		}

	    if($dealId>0){
            $res = CCrmDeal::GetList([],[
                'CHECK_PERMISSIONS'=> 'N',
                'ID'=>$dealId,
            ],[],1);
            if($arr=$res->GetNext()){
                $STAGE_ID=$arr["STAGE_ID"];
                $STAGE=CCrmStatus::GetList([],['STATUS_ID'=>$STAGE_ID/*,'ENTITY_ID'=>'DEAL_STAGE'*/])->GetNext();
                $arrResult=[
                    "TimelineId"=>$EVENT_ID,
                    "DealId"=>$dealId,
                    "StageId"=>$arr["STAGE_ID"],
                    "StageName"=>$STAGE["NAME"],
                ];
            }
        }
		if(!in_array($Event['ASSOCIATED_ENTITY_CLASS_NAME'],['TASKS','CRM_MEETING','CRM_EMAIL','VOXIMPLANT_CALL']) && !$Event["COMMENT"])
		    return false;
        return $arrResult;
    }

}