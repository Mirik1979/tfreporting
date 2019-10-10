<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 14.08.2019
 * Time: 1:47
 */

namespace local\Domain\Entity;

use JsonSerializable;
use Exception;

class CrmEventDealTimfors implements JsonSerializable
{

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function jsonSerialize() {
        $result = array(
            'id' => $this->id,
            'TimelineId' => $this->TimelineId,
            'DealId' => $this->DealId,
            'StageId' => $this->StageId,
            'StageName' => $this->StageName,
            'Type' => $this->Type,
        );
        return $result;
    }

    /**
     * @var int $id
     */
    private $id;

    /**
     * @var int $TimelineId
     */
    private $TimelineId;

    /**
     * @var int $DealId
     */
    private $DealId;

    /**
     * @var string $StageId
     */
    private $StageId;

    /**
     * @var string $StageName
     */
    private $StageName;

    /**
     * @var string $Type
     */
    private $Type;

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
    public function getTimelineId()
    {
        return $this->TimelineId;
    }

    /**
     * @param int $TimelineId
     */
    public function setTimelineId($TimelineId)
    {
        $this->TimelineId = $TimelineId;
    }

    /**
     * @return int
     */
    public function getDealId()
    {
        return $this->DealId;
    }

    /**
     * @param int $DealId
     */
    public function setDealId($DealId)
    {
        $this->DealId = $DealId;
    }

    /**
     * @return string
     */
    public function getStageId()
    {
        return $this->StageId;
    }

    /**
     * @param string $StageId
     */
    public function setStageId($StageId)
    {
        $this->StageId = $StageId;
    }

    /**
     * @return string
     */
    public function getStageName()
    {
        return $this->StageName;
    }

    /**
     * @param string $StageName
     */
    public function setStageName($StageName)
    {
        $this->StageName = $StageName;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * @param string $Type
     */
    public function setType($Type)
    {
        $this->Type = $Type;
    }

}