<?php


namespace local\Services\Alt;

use Bitrix\Crm\Timeline\Entity\TimelineBindingTable;
use Bitrix\Crm\Timeline\Entity\TimelineTable;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use CCrmTimelineComponent;
use Bitrix\Crm;


class CCrmTimelineComponentAlt extends CCrmTimelineComponent
{

    public function loadHistoryItems($offsetTime, &$nextOffsetTime, $offsetID, &$nextOffsetID, array $params = array())
    {
        if($this->entityID <= 0)
        {
            return array();
        }

        $limit = isset($params['limit']) ? (int)$params['limit'] : 0;
        $onlyFixed = isset($params['onlyFixed']) && $params['onlyFixed'] == true;
        $filter = isset($params['filter']) && is_array($params['filter']) ? $params['filter'] : array();

        //Permissions are already checked
        $query = new Query(TimelineTable::getEntity());
        $query->addSelect('*');

        $bindingQuery = new Query(TimelineBindingTable::getEntity());
        $bindingQuery->addSelect('OWNER_ID');
        $bindingQuery->addFilter('=ENTITY_TYPE_ID', $this->entityTypeID);
        $bindingQuery->addFilter('=ENTITY_ID', $this->entityID);

        if ($onlyFixed)
        {
            $bindingQuery->addFilter('=IS_FIXED', 'Y');
        }

        $bindingQuery->addSelect('IS_FIXED');
        $query->addSelect('bind.IS_FIXED', 'IS_FIXED');

        $query->registerRuntimeField('',
            new ReferenceField('bind',
                Base::getInstanceByQuery($bindingQuery),
                array('=this.ID' => 'ref.OWNER_ID'),
                array('join_type' => 'INNER')
            )
        );

        if(isset($filter['CREATED_to']))
        {
            $filter['CREATED_to'] = Main\Type\DateTime::tryParse($filter['CREATED_to']);
        }

        if(isset($filter['CREATED_from']))
        {
            $filter['CREATED_from'] = Main\Type\DateTime::tryParse($filter['CREATED_from']);
        }

        if($offsetTime instanceof DateTime && (!isset($filter['CREATED_to']) || $offsetTime < $filter['CREATED_to']))
        {
            $filter['CREATED_to'] = $offsetTime;
        }

        if(!empty($filter))
        {
            //Crm\Filter\TimelineDataProvider::prepareQuery($query, $filter);
            TimelineDataProviderAlt::prepareQuery($query, $filter);
        }

        $query->whereNotIn(
            'ASSOCIATED_ENTITY_TYPE_ID',
            Crm\Timeline\TimelineManager::getIgnoredEntityTypeIDs()
        );

        $query->setOrder(array('CREATED' => 'DESC', 'ID' => 'DESC'));
        if($limit > 0)
        {
            $query->setLimit($limit);
        }

        $items = array();
        $itemIDs = array();
        $offsetIndex = -1;
        $dbResult = $query->exec();
       // print_r($query->getQuery());
        while($fields = $dbResult->fetch())
        {
            $itemID = (int)$fields['ID'];
            $items[] = $fields;
            $itemIDs[] = $itemID;

            if($offsetID > 0 && $itemID === $offsetID)
            {
                $offsetIndex = count($itemIDs) - 1;
            }
        }
        if($offsetIndex >= 0)
        {
            $itemIDs = array_slice($itemIDs, $offsetIndex + 1);
            $items = array_splice($items, $offsetIndex + 1);
        }

        $nextOffsetTime = null;
        if(!empty($items))
        {
            $item = $items[count($items) - 1];
            if(isset($item['CREATED']) && $item['CREATED'] instanceof DateTime)
            {
                $nextOffsetTime = $item['CREATED'];
                $nextOffsetID = (int)$item['ID'];
            }
        }

        $itemsMap = array_combine($itemIDs, $items);
        \Bitrix\Crm\Timeline\TimelineManager::prepareDisplayData($itemsMap, $this->userID, $this->userPermissions);
        return array_values($itemsMap);
    }

    public function prepareHistoryFilter()
    {
        $this->arResult['HISTORY_FILTER_ID'] = $this->historyFilterID = strtolower($this->entityTypeName).'_'.$this->entityID.'_timeline_history';
        $this->arResult['HISTORY_FILTER_PRESET_ID'] = strtolower($this->entityTypeName).'_timeline_history';
        $this->arResult['HISTORY_FILTER_PRESETS'] = array(
            'communications' => array(
                'name' => Loc::getMessage('CRM_TIMELINE_FILTER_PRESET_COMMUNICATIONS'),
                'fields' => array(
                    'ENTRY_CATEGORY_ID' => array(
                        Crm\Filter\TimelineEntryCategory::SMS,
                        Crm\Filter\TimelineEntryCategory::ACTIVITY_CALL,
                        Crm\Filter\TimelineEntryCategory::ACTIVITY_VISIT,
                        Crm\Filter\TimelineEntryCategory::ACTIVITY_MEETING,
                        Crm\Filter\TimelineEntryCategory::ACTIVITY_EMAIL,
                        Crm\Filter\TimelineEntryCategory::WEB_FORM,
                        Crm\Filter\TimelineEntryCategory::CHAT
                    )
                )
            ),
            'comments' => array(
                'name' => Loc::getMessage('CRM_TIMELINE_FILTER_PRESET_COMMENTS'),
                'fields' => array(
                    'ENTRY_CATEGORY_ID' => array(
                        Crm\Filter\TimelineEntryCategory::COMMENT,
                        Crm\Filter\TimelineEntryCategory::WAITING
                    )
                )
            ),
            'documents' => array(
                'name' => Loc::getMessage('CRM_TIMELINE_FILTER_PRESET_DOCUMENTS'),
                'fields' => array(
                    'ENTRY_CATEGORY_ID' => array(
                        Crm\Filter\TimelineEntryCategory::DOCUMENT
                    )
                )
            ),
            'tasks' => array(
                'name' => Loc::getMessage('CRM_TIMELINE_FILTER_PRESET_TASKS'),
                'fields' => array(
                    'ENTRY_CATEGORY_ID' => array(
                        Crm\Filter\TimelineEntryCategory::ACTIVITY_TASK
                    )
                )
            ),
            'business_processes' => array(
                'name' => Loc::getMessage('CRM_TIMELINE_FILTER_PRESET_BUSINESS_PROCESSES'),
                'fields' => array(
                    'ENTRY_CATEGORY_ID' => array(
                        Crm\Filter\TimelineEntryCategory::ACTIVITY_REQUEST,
                        Crm\Filter\TimelineEntryCategory::BIZ_PROCESS
                    )
                )
            ),
            'system_events' => array(
                'name' => Loc::getMessage('CRM_TIMELINE_FILTER_PRESET_SYSTEM_EVENTS'),
                'fields' => array(
                    'ENTRY_CATEGORY_ID' => array(
                        Crm\Filter\TimelineEntryCategory::CREATION,
                        Crm\Filter\TimelineEntryCategory::MODIFICATION,
                        Crm\Filter\TimelineEntryCategory::CONVERSION
                    )
                )
            ),
            'applications' => array(
                'name' => Loc::getMessage('CRM_TIMELINE_FILTER_PRESET_APPLICATIONS'),
                'fields' => array(
                    'ENTRY_CATEGORY_ID' => array(
                        Crm\Filter\TimelineEntryCategory::APPLICATION
                    )
                )
            )
        );

        $this->arResult['HISTORY_FILTER'] = array();
        $filterOptions = new \Bitrix\Main\UI\Filter\Options($this->historyFilterID, $this->arResult['HISTORY_FILTER_PRESETS']);
        $filter = new Crm\Filter\Filter(
            $this->historyFilterID,
            new TimelineDataProviderAlt(
                new Crm\Filter\TimelineSettings(array('ID' => $this->historyFilterID))
            )
        );

        $effectiveFilterFieldIDs = $filterOptions->getUsedFields();
        if(empty($effectiveFilterFieldIDs))
        {
            $effectiveFilterFieldIDs = $filter->getDefaultFieldIDs();
        }

        foreach($effectiveFilterFieldIDs as $filterFieldID)
        {
            $filterField = $filter->getField($filterFieldID);
            if($filterField)
            {
                $this->arResult['HISTORY_FILTER'][] = $filterField->toArray();
            }
        }

        $this->historyFilter = $filterOptions->getFilter($this->arResult['HISTORY_FILTER']);
        $this->arResult['IS_HISTORY_FILTER_APPLIED'] = isset($this->historyFilter['FILTER_APPLIED'])
            && $this->historyFilter['FILTER_APPLIED'];

        return $this->historyFilter;
    }

}