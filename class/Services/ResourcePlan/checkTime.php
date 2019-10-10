<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 24.07.2019
 * Time: 16:10
 */

namespace local\Services\ResourcePlan;

use Bitrix\Main\Loader;
use CSocNetGroup;
use Bitrix\Main\Data\Cache;

class checkTime
{

    /**
     * checkTime constructor.
     */
    public function __construct()
    {
        Loader::includeModule('socialnetwork');
    }

    /**
     * @param int $GroupId
     * @param string $StartWork
     * @param string $StopWork
     * @return array
     */
    public function checkStartWork($GroupId,$StartWork,$StopWork){
        $result=[
            "DATE_START"=>[
                "VALUE"=>$StartWork,
                "CHANGE"=>"N",
            ],
            "DATE_FINISH"=>[
                "VALUE"=>$StopWork,
                "CHANGE"=>"N",
            ],
        ];
        if($GroupId>0){
            $CSocNetGroup=CSocNetGroup::getById($GroupId,false);
            if($CSocNetGroup){
                $PROJECT_DATE_START=$CSocNetGroup["PROJECT_DATE_START"];
                $PROJECT_DATE_FINISH=$CSocNetGroup["PROJECT_DATE_FINISH"];
                if(strlen($StartWork)>0 && strlen($PROJECT_DATE_START)>0)
                {
                    $dateStartWork=new \DateTime($StartWork);
                    $datePROJECT_DATE_START=new \DateTime($PROJECT_DATE_START);
                    if($dateStartWork<$datePROJECT_DATE_START){
                        $result["DATE_START"]["CHANGE"]="Y";
                        $result["DATE_START"]["VALUE"]=$datePROJECT_DATE_START->format('d.m.Y H:i:s');
                    }
                }
                if(strlen($StopWork)>0 && strlen($PROJECT_DATE_FINISH)>0)
                {
                    $dateStopWork=new \DateTime($StopWork);
                    $datePROJECT_DATE_FINISH=new \DateTime($PROJECT_DATE_FINISH);
                    if($dateStopWork>$datePROJECT_DATE_FINISH){
                        $result["DATE_FINISH"]["CHANGE"]="Y";
                        $result["DATE_FINISH"]["VALUE"]=$datePROJECT_DATE_FINISH->format('d.m.Y H:i:s');
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $StartWork
     * @param $StopWork
     * @return int
     * @throws \Exception
     */
    public function getQuantity($StartWork,$StopWork){
        $count=0;
        if(strlen($StartWork)>0 && strlen($StopWork)>0)
        {
            $dateStartWork=new \DateTime($StartWork);
            $dateStopWork=new \DateTime($StopWork);
            $arHolidays=$this->getHoliday((int)$dateStartWork->format('Y'));
            if((int)$dateStartWork->format('Y')!=(int)$dateStopWork->format('Y'))
                $arHolidays=array_merge($arHolidays,$this->getHoliday((int)$dateStopWork->format('Y')));
            $count=$this->getWorkingDays($dateStartWork,$dateStopWork,$arHolidays);
        }
        return $count;
    }


    /**
     * @param int $year
     * @return array
     */
    private function getHoliday($year){
        $arHolidays=[];
        $cache = Cache::createInstance();
        $cacheTime = 30*60;
        $cacheId = 'arHolidays'.$year;
        $cacheDir = 'arHolidays';
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $arHolidays = $cache->getVars();
        }elseif ($cache->startDataCache()) {
            if($year>=(int)date('Y')){
                $calendar = simplexml_load_file('http://xmlcalendar.ru/data/ru/'.$year.'/calendar.xml');
                $calendar = $calendar->days->day;
                foreach( $calendar as $day ){
                    $d = (array)$day->attributes()->d;
                    $d = $d[0];
                    $d = substr($d, 3, 2).'.'.substr($d, 0, 2).'.'.date('Y');
                    //не считая короткие дни
                    if( $day->attributes()->t == 1 ) $arHolidays[] = $d;
                }
            }
        }
        return $arHolidays;
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param array $holidays
     * @return int
     * @throws \Exception
     */
    private function getWorkingDays($startDate,$endDate,$holidays){

        $h=0;

        $days = array();

        $firstDate=clone $startDate;

        while ($endDate>$firstDate)
        {
            $days[]=$firstDate->format('d.m.Y');
            $firstDate->add(new \DateInterval('P1D'));
        }

        if(count($days)==1 && !in_array($startDate->format('d.m.Y'),$holidays)){
            $firstDate=clone $startDate;
            while ($endDate>$firstDate)
            {
                $newH=(int)$firstDate->format('G');
                if(($newH>=9 && $newH<=12) || ($newH>=14 && $newH<18))
                    $h++;
                $firstDate->add(new \DateInterval('PT1H'));
            }
        }elseif(count($days)>=2){

            $firstDate=clone $startDate;
            $toDate=new \DateTime($firstDate->format('d.m.Y')." 23:59:59");
            if(!in_array($startDate->format('d.m.Y'),$holidays)){
                while ($toDate>$firstDate)
                {
                    $newH=(int)$firstDate->format('G');
                    if(($newH>=9 && $newH<=12) || ($newH>=14 && $newH<18))
                        $h++;
                    $firstDate->add(new \DateInterval('PT1H'));
                }
            }

            $firstDate=new \DateTime($endDate->format('d.m.Y')." 00:00:00");
            $toDate=clone $endDate;
            if(!in_array($endDate->format('d.m.Y'),$holidays)){
                while ($toDate>$firstDate)
                {
                    $newH=(int)$firstDate->format('G');
                    if(($newH>=9 && $newH<=12) || ($newH>=14 && $newH<18))
                        $h++;
                    $firstDate->add(new \DateInterval('PT1H'));
                }
            }

            if (count($days)>2){
                for ($i=1;$i<count($days)-1;$i++){
                    if(!in_array($days[$i],$holidays))
                        $h+=8;
                }
            }
        }
        return $h;
    }

}