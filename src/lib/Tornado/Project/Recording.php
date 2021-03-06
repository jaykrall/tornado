<?php

namespace Tornado\Project;

use Tornado\DataMapper\DataObjectInterface;
use DataSift\Pylon\SubscriptionInterface;

use DataSift_Pylon;

/**
 * Models a Tornado Project's Recording
 *
 * LICENSE: This software is the intellectual property of MediaSift Ltd.,
 * and is covered by retained intellectual property rights, including
 * copyright. Distribution of this software is strictly forbidden under
 * the terms of this license.
 *
 * @category    Applications
 * @package     \Tornado\Project
 * @copyright   2015-2016 MediaSift Ltd.
 * @license     http://mediasift.com/licenses/internal MediaSift Internal License
 * @link        https://github.com/datasift/tornado
 */
class Recording implements DataObjectInterface, SubscriptionInterface
{
    const STATUS_STOPPED = 'stopped';
    const STATUS_STARTED = 'started';

    const OUTSIDE_SOURCE_CSDL = '// not created in Tornado';

    protected static $STATUS_MAP = [self::STATUS_STOPPED, self::STATUS_STARTED];

    /**
     * The id of this Recording
     *
     * @var integer
     */
    protected $id;

    /**
     * The id of the Brand this Recording belongs to
     *
     * @var integer
     */
    protected $brandId;

    /**
     * The id of Project to which this Recording belongs to
     *
     * @var integer
     */
    protected $projectId;

    /**
     * This Recording DataSift Recording ID (CSDL hash for now)
     *
     * @var string
     */
    protected $datasiftRecordingId;

    /**
     * This Recording DataSift CSDL hash
     *
     * @var string
     */
    protected $hash;

    /**
     * The name of this Recording
     *
     * @var string
     */
    protected $name;

    /**
     * This Recording status. Can be:
     *  - stopped
     *  - started
     *
     * @var string
     */
    protected $status = self::STATUS_STARTED;

    /**
     * This Recording related plain CSDL query
     *
     * @var string
     */
    protected $csdl;

    /**
     * Determines if CSDL has been generated by the VQB or CSDL editor.
     *
     * The VQB always can be loaded into the CSDL editor but not in the opposite way. Some of the editor output
     * cannot be loaded into the VQB.
     *
     * @var boolean
     */
    protected $vqbGenerated = false;

    /**
     * The creation date of this Recording
     *
     * @var integer
     */
    protected $createdAt;

    /**
     * The volume associated with this Recording
     *
     * @var integer
     */
    protected $volume = 0;

    /**
     * Gets this Recording's Id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets this Recording's Id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Gets the id of this Recording's Brand
     *
     * @return integer
     */
    public function getBrandId()
    {
        return $this->brandId;
    }

    /**
     * Sets the id of this Recording's Brand
     *
     * @param integer $brandId
     */
    public function setBrandId($brandId)
    {
        $this->brandId = $brandId;
    }

    /**
     * Sets the id of this Recording's Project
     *
     * @param $projectId
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * Gets the id of this Recording's Project
     *
     * @return int
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Gets the Recording DataSift CSDL hash
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Sets the DataSift Recording CSDL hash
     *
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        $this->datasiftRecordingId = $hash;
    }

    /**
     * Sets the DataSift Recording ID for this Recording
     *
     * @param $datasiftRecordingId
     */
    public function setDatasiftRecordingId($datasiftRecordingId)
    {
        $this->hash = $datasiftRecordingId;
        $this->datasiftRecordingId = $datasiftRecordingId;
    }

    /**
     * Gets the DataSift Recording ID for this Recording
     *
     * @return string
     */
    public function getDatasiftRecordingId()
    {
        return $this->datasiftRecordingId;
    }

    /**
     * Returns this Recording's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets this Recording's name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets this Recording status
     *
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets this Recording status
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        if (!in_array($status, self::$STATUS_MAP)) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" is not a valid status in "%s"',
                $status,
                __METHOD__
            ));
        }

        $this->status = $status;
    }

    /**
     * Sets this Recording plain CSDL
     *
     * @param string $csdl
     */
    public function setCsdl($csdl)
    {
        $this->csdl = $csdl;
    }

    /**
     * Gets this Recording plain CSDL
     *
     * @return string
     */
    public function getCsdl()
    {
        return $this->csdl;
    }

    /**
     * Marks if CSDL has been generated by VQB or not
     *
     * @param bool $generated
     */
    public function setVqbGenerated($generated)
    {
        $this->vqbGenerated = (int)$generated;
    }

    /**
     * Gets info if CSDL has been generated by VQB or not
     *
     * @return boolean
     */
    public function isVqbGenerated()
    {
        return (bool)$this->vqbGenerated;
    }

    /**
     * Sets this Recording creation date
     *
     * @param integer $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Gets this Recording creation date
     *
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Gets the volume of this Recording
     *
     * @return integer
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getPrimaryKeyName()
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptionHash()
    {
        return $this->getHash();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptionId()
    {
        return $this->getDatasiftRecordingId();
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromArray(array $data)
    {
        $map = [
            'id' => 'setId',
            'brand_id' => 'setBrandId',
            'project_id' => 'setProjectId',
            'datasift_recording_id' => 'setDatasiftRecordingId',
            'hash' => 'setHash',
            'name' => 'setName',
            'status' => 'setStatus',
            'csdl' => 'setCsdl',
            'vqb_generated' => 'setVqbGenerated',
            'created_at' => 'setCreatedAt'
        ];

        foreach ($map as $key => $setter) {
            if (array_key_exists($key, $data)) {
                $this->$setter($data[$key]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $map = [
            'id' => 'getId',
            'brand_id' => 'getBrandId',
            'project_id' => 'getProjectId',
            'datasift_recording_id' => 'getDatasiftRecordingId',
            'hash' => 'getHash',
            'name' => 'getName',
            'status' => 'getStatus',
            'csdl' => 'getCsdl',
            'vqb_generated' => 'isVqbGenerated',
            'created_at' => 'getCreatedAt'
        ];

        $ret = [];
        foreach ($map as $key => $getter) {
            $ret[$key] = $this->$getter();
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $data = $this->toArray();

        // Layer in non-Tornado information
        $data['volume'] = $this->volume;

        return $data;
    }

    /**
     * Decorates this Recording with information from the associated PYLON
     * subscription
     *
     * @param \DataSift_Pylon $pylon
     * @param boolean $overrideExisting
     */
    public function fromDataSiftRecording(DataSift_Pylon $pylon, $overrideExisting = false)
    {
        $this->volume = $pylon->getVolume();
        $this->setStatus(
            ($pylon->getStatus() == 'running')
            ? static::STATUS_STARTED
            : static::STATUS_STOPPED
        );

        if ($overrideExisting) {
            $this->setName($pylon->getName());
            $this->setCreatedAt($pylon->getStart());
        }
    }
}
