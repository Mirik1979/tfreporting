<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 14.08.2019
 * Time: 1:50
 */

namespace local\Domain\Factory;

use local\Domain\Entity\CrmEventDealTimfors;
use InvalidArgumentException;

class CrmEventDealTimforsFactory
{
    /**
     * @param array $params
     * @return CrmEventDealTimfors
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $params)
    {
        $el = new CrmEventDealTimfors();

        if($params['id'])
            $el->setId($params['id']);

        if($params['TimelineId'])
            $el->setTimelineId($params['TimelineId']);

        if($params['DealId'])
            $el->setDealId($params['DealId']);

        if($params['StageId'])
            $el->setStageId($params['StageId']);

        if($params['StageName'])
            $el->setStageName($params['StageName']);

        if($params['Type'])
            $el->setType($params['Type']);

        return $el;
    }

    /**
     * @param array $records
     * @throws InvalidArgumentException
     * @return CrmEventDealTimfors[]
     */
    public static function createFromCollection(array $records)
    {
        $output = [];
        array_map(function ($item) use (&$output) {
            $output[] = self::createFromArray($item);
        }, $records);
        return $output;
    }
}