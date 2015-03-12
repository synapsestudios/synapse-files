<?php
namespace Test\Synapse\File;

use Synapse\File\AbstractFileMapper;

trait FileControllerTestTrait
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|GenericFileService
     */
    protected $mockFileService;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AbstractFileMapper
     */
    protected $mockFileMapper;

    protected $fileId = '97dc5a93-b64a-4cf5-8b8d-0adfd5c58896';

    protected $fileEntityClass;

    /**
     * @param string $mapperClass
     * @param string $entityClass
     */
    protected function setupFileControllerMocks($mapperClass, $entityClass)
    {
        $this->fileEntityClass = $entityClass;

        $this->mockFileService = $this
            ->getMockBuilder('\Test\Synapse\File\GenericFileService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockFileMapper = $this
            ->getMockBuilder($mapperClass)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockFileMapper->expects($this->any())
            ->method('getPrototype')
            ->willReturn(new $entityClass());
    }

    protected function withFileServiceCreateReturningFile($id = false)
    {
        if (!$id) {
            $id = $this->fileId;
        }

        $this->mockFileService->expects($this->once())
            ->method('create')
            ->willReturn(new $this->fileEntityClass(['id' => $id]));
    }

    protected function withFileMapperFindByIdReturningFile($id = false)
    {
        if (!$id) {
            $id = $this->fileId;
        }

        $this->mockFileMapper->expects($this->once())
            ->method('findById')
            ->willReturn(new $this->fileEntityClass([
                'id' => $id
            ]));
    }
}
