<?php

namespace local\Services\Alt;

use Bitrix\Crm\Filter\Field;
use Bitrix\Crm\Filter\TimelineDataProvider;
use Bitrix\Crm\Filter\TimelineEntryCategory;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Crm\Timeline\Entity\TimelineTable;
use Bitrix\Main\Entity\Event;
use CCrmDeal;
use CCrmStatus;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use local\Domain\Repository\CrmEventDealTimforsRepository;
use local\Domain\Factory\CrmEventDealTimforsFactory;
use Bitrix\Main\Context;
use Bitrix\Highloadblock as HL;

class TimelineDataProviderAlt extends TimelineDataProvider
{


    /**
     * @param Main\Entity\Query $query
     * @param array $filter
     */
    public static function prepareQuery($query, $filter)
    {
        parent::prepareQuery($query, $filter);
        if(($filter["STAGE_ID"] || $filter["STAGE_SALE_ID"]) && Loader::includeModule("highloadblock")){
            $hlblock = HL\HighloadBlockTable::getById(HIGHLOAD_CRMEVENTDEALTIMFORS)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();
            $query
                ->registerRuntimeField('STAGE', [
                        'data_type' => $entity_data_class,
                        'reference' => [
                            '=this.ID' => 'ref.UF_TIMELINE_ID',
                        ],
                    ]
                )
                ->registerRuntimeField('STAGE_ID', [
                    'data_type'=>'string',
                    'expression' => ['%s', 'STAGE.UF_STAGE_ID']
                ]);
            if(isset($filter['STAGE_ID']))
            {
                if(is_array($filter['STAGE_ID']))
                {
                    $query->whereIn('STAGE_ID', $filter['STAGE_ID']);
                }
                else
                {
                    $query->where('STAGE_ID', $filter['STAGE_ID']);
                }
            }
            if(isset($filter['STAGE_SALE_ID']))
            {
                if(is_array($filter['STAGE_SALE_ID']))
                {
                    $query->whereIn('STAGE_ID', $filter['STAGE_SALE_ID']);
                }
                else
                {
                    $query->where('STAGE_ID', $filter['STAGE_SALE_ID']);
                }
            }
        }
        //print_r($filter);
    }




    /**
     * Prepare field list.
     * @return Field[]
     * @throws Main\ArgumentException
     */
    public function prepareFields()
    {
        $result=parent::prepareFields();
        if (check_bitrix_sessid()){
            $request = Context::getCurrent()->getRequest();
            $filter_id=trim($request->get("filter_id"));
            $pos = strpos($filter_id, 'deal_');
            if ($pos !== false) {
                $id=(int)str_replace('deal_','',$filter_id);
                if($id>0){
                    $res = CCrmDeal::GetListEx([],[
                        'CHECK_PERMISSIONS'=> 'N',
                        'ID'=>$id,
                    ],false,['nTopCount'=>1],['ID','CATEGORY_ID']);
                    if($arr=$res->GetNext()) {
                        switch ($arr["CATEGORY_ID"]) {
                            case 1:
                                $result['STAGE_ID']=$this->createField(
                                    'STAGE_ID',
                                    array(
                                        'name' => 'Стадия активности',
                                        'type' => 'list',
                                        'default' => true,
                                        'partial' => true
                                    )
                                );
                                break;
                            default:
                                $result['STAGE_SALE_ID']=$this->createField(
                                    'STAGE_SALE_ID',
                                    array(
                                        'name' => 'Стадия активности',
                                        'type' => 'list',
                                        'default' => true,
                                        'partial' => true
                                    )
                                );
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Prepare complete field data for specified field.
     * @param string $fieldID Field ID.
     * @return array|null
     */
    public function prepareFieldData($fieldID)
    {
        switch ($fieldID) {
            case "STAGE_SALE_ID":
                $arr=[];
                $STAGE = CCrmStatus::GetList(["SORT"=>"ASC"], [
                    'ENTITY_ID' => 'DEAL_STAGE'
                ]);
                while ($el=$STAGE->GetNext()){
                    $arr[$el["STATUS_ID"]]=[
                        "NAME"=>$el["NAME"],
                        "VALUE"=>$el["STATUS_ID"],
                    ];
                }
                return array(
                    'params' => array('multiple' => 'Y'),
                    'items' => $arr
                );
                break;
            case "STAGE_ID":
                $arr=[];
                $STAGE = CCrmStatus::GetList(["SORT"=>"ASC"], [
                    'ENTITY_ID' => 'DEAL_STAGE_1'
                ]);
                while ($el=$STAGE->GetNext()){
                    $arr[$el["STATUS_ID"]]=[
                        "NAME"=>$el["NAME"],
                        "VALUE"=>$el["STATUS_ID"],
                    ];
                }
                return array(
                    'params' => array('multiple' => 'Y'),
                    'items' => $arr
                );
                break;
            default:
                return parent::prepareFieldData($fieldID);
        }
    }
}