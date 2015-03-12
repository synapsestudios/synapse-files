<?php
namespace Test\Synapse\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;
use Synapse\TestHelper\MapperTestCase;

class AbstractFileServiceTest extends MapperTestCase
{
    const TEST_GET_FILE = '/tmp/abstract_file_service_test_get';
    const TEST_GET_PATH = '/tmp';
    const TEST_GET_FILENAME = 'abstract_file_service_test_get';

    protected $mockAdapter;
    /**
     * @var GenericFileMapper
     */
    protected $mapper;
    /**
     * @var GenericFileService
     */
    protected $service;

    protected function getUploadRequest()
    {
        $request = new Request(
            [],
            ['path' => 'asdf/fdsa'],
            [],
            [],
            []
        );

        return $request;
    }

    public function setUp()
    {
        parent::setUp();
        $this->mapper = $this->getMockBuilder('\Test\Synapse\File\GenericFileMapper')
                             ->disableOriginalConstructor()
                             ->getMock();
        $this->mapper->expects($this->any())
            ->method('getPrototype')
            ->willReturn(new GenericFileEntity());

        $this->service = new GenericFileService();

        if (!file_exists(self::TEST_GET_FILE)) {
            touch(self::TEST_GET_FILE);
        }
    }

    public function tearDown()
    {
        if (file_exists(self::TEST_GET_FILE)) {
            unlink(self::TEST_GET_FILE);
        }
    }

    public function withFileInserted()
    {
        $this->mapper->expects($this->once())
            ->method('insert')
            ->willReturnArgument(0);
    }

    public function getUploadedFile($error = false)
    {
        return new UploadedFile(self::TEST_GET_FILE, self::TEST_GET_FILENAME, 'text/plain', 300, $error ? 1 : 0, true);
    }
    /**
     * @expectedException \Synapse\File\FileException
     */
    public function testNoUploadedFileThrowsException()
    {
        $req     = $this->getUploadRequest();
        $this->service->create($req, new GenericFileMapper($this->mockAdapter, new GenericFileEntity()));
    }

    /**
     * @throws \Synapse\File\FileException
     * @expectedException \Synapse\File\FileException
     */
    public function testPostMultipleFilesThrowsException()
    {
        $request = $this->getUploadRequest();
        $request->files = new FileBag([[], []]);

        $this->service->create($request, $this->mapper);
    }

    public function testPostFileInsertsAndSaves()
    {
        $this->withFileInserted();

        $request = $this->getUploadRequest();
        $request->files = new FileBag();
        $request->files->add([$this->getUploadedFile()]);

        $file = $this->service->create($request, $this->mapper);
        $this->assertInstanceOf('\Test\Synapse\File\GenericFileEntity', $file);
    }

    /**
     * @expectedException \Exception
     */
    public function testFailedUploadThrowsException()
    {
        $request = $this->getUploadRequest();
        $request->files = new FileBag();
        $request->files->add([$this->getUploadedFile(true)]);
        $this->service->setSaveThrowsException(true);

        $this->service->create($request, $this->mapper);
    }

    /**
     * @expectedException \Exception
     */
    public function testFailedSaveThrowsException()
    {
        $this->withFileInserted();
        $this->service->setSaveThrowsException(true);

        $request = $this->getUploadRequest();
        $request->files = new FileBag();
        $request->files->add([$this->getUploadedFile()]);

        $this->service->create($request, $this->mapper);
    }
}
