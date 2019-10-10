<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 24.07.2019
 * Time: 21:14
 */

namespace local\Domain\Entity;

use JsonSerializable;
use Exception;
use DateTime;
use Bitrix\Main\Loader;

class ResrequestsProductId implements JsonSerializable
{

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function jsonSerialize() {
        $result = array(
            'id' => $this->id,
            'ProductId' => $this->ProductId,
            'RoleProject' => $this->RoleProject,
            'Product' => $this->Product,
            'Model' => $this->Model,
            'Grade' => $this->Grade,
            'YearsExperience' => $this->YearsExperience,
            'PreferredLocatio' => $this->PreferredLocatio,
            'AmountOverhead' => $this->AmountOverhead,
            'ExecutorName' => $this->ExecutorName,
            'ExecutorId' => $this->ExecutorId,
            'rate_per_hour' => $this->rate_per_hour,
            'vacancy' => $this->vacancy
        );
        if($this->StartWork)
            $result['StartWork']=$this->StartWork;
        if($this->EndWork)
            $result['EndWork']=$this->EndWork;
        return $result;
    }


    /**
     * @var int $id
     */
    private $id;

    /**
     * @var int $ProductId
     */
    private $ProductId;

    /**
     * @var int $RoleProject
     */
    private $RoleProject;

    /**
     * @var int $Product
     */
    private $Product;

    /**
     * @var int $Model
     */
    private $Model;

    /**
     * @var int $Grade
     */
    private $Grade;

    /**
     * @var int $YearsExperience
     */
    private $YearsExperience;

    /**
     * @var int $PreferredLocatio
     */
    private $PreferredLocatio;

    /**
     * @var float $AmountOverhead
     */
    private $AmountOverhead;

    /**
     * @var DateTime $StartWork
     */
    private $StartWork;

    /**
     * @var DateTime $EndWork
     */
    private $EndWork;

    /**
     * @var string $ExecutorName
     */
    private $ExecutorName;

    /**
     * @var int $ExecutorId
     */
    private $ExecutorId;

    /**
     * @var float $rate_per_hour
     */
    private $rate_per_hour;

    /**
     * @var int $vacancy
     */
    private $vacancy;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->ProductId;
    }

    /**
     * @param int $ProductId
     */
    public function setProductId($ProductId)
    {
        $this->ProductId = $ProductId;
    }

    /**
     * @return int
     */
    public function getRoleProject()
    {
        return $this->RoleProject;
    }

    /**
     * @param int $RoleProject
     */
    public function setRoleProject($RoleProject)
    {
        $this->RoleProject = $RoleProject;
    }

    /**
     * @return int
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * @param int $Product
     */
    public function setProduct($Product)
    {
        $this->Product = $Product;
    }

    /**
     * @return int
     */
    public function getModel()
    {
        return $this->Model;
    }

    /**
     * @param int $Model
     */
    public function setModel($Model)
    {
        $this->Model = $Model;
    }

    /**
     * @return int
     */
    public function getGrade()
    {
        return $this->Grade;
    }

    /**
     * @param int $Grade
     */
    public function setGrade($Grade)
    {
        $this->Grade = $Grade;
    }

    /**
     * @return int
     */
    public function getYearsExperience()
    {
        return $this->YearsExperience;
    }

    /**
     * @param int $YearsExperience
     */
    public function setYearsExperience($YearsExperience)
    {
        $this->YearsExperience = $YearsExperience;
    }

    /**
     * @return int
     */
    public function getPreferredLocatio()
    {
        return $this->PreferredLocatio;
    }

    /**
     * @param int $PreferredLocatio
     */
    public function setPreferredLocatio($PreferredLocatio)
    {
        $this->PreferredLocatio = $PreferredLocatio;
    }

    /**
     * @return float
     */
    public function getAmountOverhead()
    {
        return $this->AmountOverhead;
    }

    /**
     * @param float $AmountOverhead
     */
    public function setAmountOverhead($AmountOverhead)
    {
        $this->AmountOverhead = $AmountOverhead;
    }

    /**
     * @return DateTime
     */
    public function getStartWork()
    {
        return $this->StartWork;
    }

    /**
     * @param DateTime $StartWork
     */
    public function setStartWork($StartWork)
    {
        $this->StartWork = $StartWork;
    }

    /**
     * @return DateTime
     */
    public function getEndWork()
    {
        return $this->EndWork;
    }

    /**
     * @param DateTime $EndWork
     */
    public function setEndWork($EndWork)
    {
        $this->EndWork = $EndWork;
    }

    /**
     * @return string
     */
    public function getExecutorName()
    {
        return $this->ExecutorName;
    }

    /**
     * @param string $ExecutorName
     */
    public function setExecutorName($ExecutorName)
    {
        $this->ExecutorName = $ExecutorName;
    }

    /**
     * @return int
     */
    public function getExecutorId()
    {
        return $this->ExecutorId;
    }

    /**
     * @param int $ExecutorId
     */
    public function setExecutorId($ExecutorId)
    {
        $this->ExecutorId = $ExecutorId;
    }

    /**
     * @return float
     */
    public function getRatePerHour()
    {
        return $this->rate_per_hour;
    }

    /**
     * @param float $rate_per_hour
     */
    public function setRatePerHour($rate_per_hour)
    {
        $this->rate_per_hour = $rate_per_hour;
    }

    /**
     * @return int
     */
    public function getVacancy()
    {
        return $this->vacancy;
    }

    /**
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public function getNameVacancy()
    {
        $vacancy="";
        if(Loader::includeModule("iblock")){
            $res = \CIBlockElement::GetByID($_GET["PID"]);
            if($ar_res = $res->GetNext())
                $vacancy=$ar_res['NAME'];
        }
        return $vacancy;
    }

    /**
     * @param int $vacancy
     */
    public function setVacancy($vacancy)
    {
        $this->vacancy=$vacancy;
    }

}