<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 24.07.2019
 * Time: 21:25
 */

namespace local\Domain\Factory;

use local\Domain\Entity\ResrequestsProductId;
use InvalidArgumentException;

class ResrequestsProductIdFactory
{
    /**
     * @param array $params
     * @return ResrequestsProductId
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $params)
    {
        $el = new ResrequestsProductId();

        if($params['id'])
            $el->setId($params['id']);

        if($params['ProductId'])
            $el->setProductId($params['ProductId']);

        if($params['RoleProject'])
            $el->setRoleProject($params['RoleProject']);

        if($params['Product'])
            $el->setProduct($params['Product']);

        if($params['Model'])
            $el->setModel($params['Model']);

        if($params['Grade'])
            $el->setGrade($params['Grade']);

        if($params['YearsExperience'])
            $el->setYearsExperience($params['YearsExperience']);

        if($params['PreferredLocatio'])
            $el->setPreferredLocatio($params['PreferredLocatio']);

        if($params['AmountOverhead'])
            $el->setAmountOverhead($params['AmountOverhead']);

        if($params['ExecutorName'])
            $el->setExecutorName($params['ExecutorName']);

        if($params['ExecutorId'])
            $el->setExecutorId($params['ExecutorId']);

        if($params['StartWork'])
            $el->setStartWork($params['StartWork']);

        if($params['EndWork'])
            $el->setEndWork($params['EndWork']);

        if($params['RatePerHour'])
            $el->setRatePerHour($params['RatePerHour']);

        if($params['Vacancy'])
            $el->setVacancy($params['Vacancy']);


        return $el;
    }

    /**
     * @param array $records
     * @throws InvalidArgumentException
     * @return ResrequestsProductId[]
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