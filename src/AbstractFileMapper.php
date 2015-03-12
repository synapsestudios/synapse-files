<?php

namespace Synapse\File;

use Rhumsaa\Uuid\Uuid;
use Synapse\Entity\AbstractEntity;
use Synapse\Mapper;

/**
 * File mapper
 */
abstract class AbstractFileMapper extends Mapper\AbstractMapper
{
    /**
     * Use all mapper traits, making this a general purpose mapper
     */
    use Mapper\InserterTrait {
        insert as parentInsert;
    }
    use Mapper\FinderTrait {
        findBy as parentFindBy;
    }
    use Mapper\UpdaterTrait;
    use Mapper\DeleterTrait {
        delete as parentDelete;
    }

    /**
     * @var AbstractFileEntity
     */
    protected $prototype;

    /**
     * @inheritdoc
     */
    protected $tableName = 'files';

    /**
     * Insert the given entity into the database
     *
     * @param  AbstractFileEntity $entity
     *
     * @return AbstractEntity         Entity with ID populated
     */
    public function insert(AbstractFileEntity $entity)
    {
        $values = [
            'id'          => Uuid::uuid4()->toString(),
            'record_type' => $entity->getRecordType(),
        ];
        $entity->exchangeArray($values);

        return $this->parentInsert($entity);
    }

    public function delete(AbstractFileEntity $entity)
    {
        return $this->deleteWhere([
            'id'          => $entity->getId(),
            'record_type' => $entity->getRecordType(),
        ]);
    }

    /**
     * Automatically inject record_type for searching records
     *
     * @param array $wheres
     * @param array $options
     *
     * @return bool|AbstractEntity
     */
    public function findBy(array $wheres, array $options = [])
    {
        $wheres['record_type'] = $this->prototype->getRecordType();

        return $this->parentFindBy($wheres, $options);
    }

    /**
     * Find file by path and file name
     *
     * @param  string $path
     * @param  string $fileName
     *
     * @return AbstractFileEntity
     */
    public function findByPathAndFileName($path, $fileName)
    {
        $entity = $this->findBy(
            [
                'path'      => $path,
                'file_name' => $fileName
            ]
        );

        return $entity;
    }

    public function findByFullPath($fullPath)
    {
        $info     = pathinfo($fullPath);
        $path     = $info['dirname'];
        $fileName = $info['basename'];

        return $this->findByPathAndFileName($path, $fileName);
    }
}
