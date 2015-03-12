<?php
namespace Test\Synapse\File;

use Synapse\TestHelper\MapperTestCase;

class AbstractFileMapperTest extends MapperTestCase
{
    protected $mockAdapter;
    /** @var GenericFileMapper */
    protected $mapper;
    protected $mockSqlFactory;

    public function setUp()
    {
        parent::setUp();

        $this->mapper = new GenericFileMapper(
            $this->mockAdapter,
            new GenericFileEntity()
        );
        $this->mapper->setSqlFactory($this->mockSqlFactory);
    }

    public function testEnsureRecordTypeFilterOnFindBy()
    {
        $this->mapper->findById('asdf');

        $sql = $this->getSqlString();

        $this->assertRegExp('/WHERE `id` = \'asdf\' AND `record_type` = \'generic\'/', $sql);
    }

    public function testEnsureRecordTypeFilterOnFindByFullPath()
    {
        $this->mapper->findByFullPath('asdf/abcd/fdsa.txt');
        $sql = $this->getSqlString();

        $regex = '/`path` = \'asdf\/abcd\'/';
        $this->assertRegExp($regex, $sql, 'Path not set in insert');
        $regex = '/`record_type` = \'generic\'/';
        $this->assertRegExp($regex, $sql, 'Record type not set in insert');
    }

    public function testUuidGeneratedForInsert()
    {
        $data   = [
            'file_name'    => 'asdf.txt',
            'path'         => 'asdf/fdsa',
            'content_type' => 'text/plain',
            'size'         => 123123,
        ];
        $entity = new GenericFileEntity($data);
        $this->mapper->insert($entity);

        $sql   = $this->getSqlString(0);

        $this->assertRegExp('/INSERT INTO `files`/', $sql, 'Not an insert statement');
        $this->assertRegExp('/\'[\d\w-]{36}\'/', $sql, 'No UUID found in insert');
    }

    public function testDeleteIncludesRecordType()
    {
        $data   = [
            'id'           => 'a-unique-id',
            'file_name'    => 'asdf.txt',
            'path'         => 'asdf/fdsa',
            'content_type' => 'text/plain',
            'size'         => 123123,
        ];
        $entity = new GenericFileEntity($data);
        $this->mapper->delete($entity);

        $sql = $this->getSqlString();

        $this->assertRegExp('/`id` = \'a-unique-id\'/', $sql, 'ID filtered in delete statement');
        $this->assertRegExp('/`record_type` = \'generic\'/', $sql, 'Record Type not filtered in delete statement');
    }
}
