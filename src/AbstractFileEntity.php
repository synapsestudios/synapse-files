<?php
namespace Synapse\File;

use Synapse\Entity\AbstractEntity;

abstract class AbstractFileEntity extends AbstractEntity
{
    /**
     * @var array
     */
    protected $object = [
        'id'           => null,
        'record_type'  => null,
        'file_name'    => null,
        'content_type' => null,
        'size'         => null,
    ];

    /**
     * @var string Should be overridden by concrete class
     */
    protected $recordType = 'file';

    public function __construct(array $data = [])
    {
        $data['record_type'] = $this->recordType;

        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getRecordType()
    {
        return $this->recordType;
    }

    public function getPath()
    {
        return $this->getRecordType() . DIRECTORY_SEPARATOR . $this->getId();
    }
}
