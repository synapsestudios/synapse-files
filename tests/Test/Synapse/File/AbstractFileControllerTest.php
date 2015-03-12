<?php
namespace Test\Synapse\File;

use Synapse\File\AbstractFileController;
use Synapse\File\AbstractFileService;
use Synapse\File\FileException;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Synapse\TestHelper\ControllerTestCase;

class AbstractFileControllerTest extends ControllerTestCase
{
    const FILE_ID            = 'some_file_id';
    const TEST_FILE_PATH     = '/tmp/test_abstract_filecontroller';
    const TEST_FILE_CONTENTS = 'test';

    /**
     * @var GenericFileMapper
     */
    protected $mockMapper;

    /**
     * @var AbstractFileService
     */
    protected $mockFileService;

    /**
     * @var Logger
     */
    protected $mockLogger;

    /**
     * @var GenericFileController
     */
    protected $controller;

    public function setUp()
    {
        if (!file_exists(self::TEST_FILE_PATH)) {
            touch(self::TEST_FILE_PATH);
            file_put_contents(self::TEST_FILE_PATH, self::TEST_FILE_CONTENTS);
        }
        $this->setupMockFileMapper();
        $this->setupMockFileService();
        $this->setUpMockLogger();
        $this->controller = new GenericFileController(
            $this->mockFileService,
            $this->mockMapper
        );
        $this->controller->setLogger($this->mockLogger);
    }

    public function tearDown()
    {
        if (file_exists(self::TEST_FILE_PATH)) {
            unlink(self::TEST_FILE_PATH);
        }
    }

    public function setUpMockLogger()
    {
        $this->mockLogger = $this
            ->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setupMockFileMapper()
    {
        $this->mockMapper = $this
            ->getMockBuilder('\Test\Synapse\File\GenericFileMapper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setupMockFileService()
    {
        $this->mockFileService = $this
            ->getMockBuilder('\Synapse\File\LocalFileService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getUploadRequest()
    {
        $request = new Request(
            [],
            ['path' => 'asdf/fdsa'],
            [],
            [],
            [
                'a_file' => new UploadedFile(
                    self::TEST_FILE_PATH,
                    'test.txt',
                    'text/plain',
                    100,
                    UPLOAD_ERR_OK,
                    true
                )
            ]
        );

        return $request;
    }

    protected function withCreateEntity()
    {
        $this->mockFileService
            ->expects($this->once())
            ->method('create')
            ->withAnyParameters()
            ->willReturn(
                new GenericFileEntity(['id' => self::FILE_ID])
            );
    }

    protected function withUploadException()
    {
        $this->mockFileService
            ->expects($this->once())
            ->method('create')
            ->withAnyParameters()
            ->will(
                $this->throwException(new FileException('Upload failed'))
            );
    }

    protected function withLogError()
    {
        $this->mockLogger
            ->expects($this->once())
            ->method('error')
            ->withAnyParameters()
            ->willReturn(true);
    }

    protected function withFileResource()
    {
        $resource = fopen('php://memory', 'rw');
        fwrite($resource, self::TEST_FILE_CONTENTS);
        rewind($resource);

        $this->mockFileService->expects($this->once())
            ->method('loadFile')
            ->willReturn($resource);
    }

    protected function getFileEntity()
    {
        return new GenericFileEntity([
            'file_name' => 'Test.txt',
            'size' => 300,
            'content_type' => 'text/plain',
        ]);
    }

    public function testPostReturns201AndReturnsIdOnFileSaved()
    {
        $request = $this->getUploadRequest();
        $this->withCreateEntity();
        $response = $this->controller->post($request);

        $this->assertEquals(201, $response->getStatusCode());

        $contents = json_decode($response->getContent());
        $this->assertEquals(self::FILE_ID, $contents->id);
    }

    public function testUploadWithoutFileReturns400()
    {
        $request = new Request();

        $response = $this->controller->post($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            AbstractFileController::ERROR_NO_FILE_UPLOADED,
            json_decode($response->getContent())->message
        );
    }

    public function testUploadWithInvalidFileSizeReturns400()
    {
        $request = $this->getUploadRequest();
        $this->controller->setSizeRestriction(1);

        $response = $this->controller->post($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            AbstractFileController::ERROR_FILE_SIZE_EXCEEDED,
            json_decode($response->getContent())->message
        );
    }

    public function testUploadWithInvalidFileTypeReturns400()
    {
        $request = $this->getUploadRequest();
        $this->controller->setFileTypeRestrictions(['image/jpeg']);

        $response = $this->controller->post($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testPostUploadFailedWillReturn500()
    {
        $request = $this->getUploadRequest();
        $this->withUploadException();
        $this->withLogError();
        $response = $this->controller->post($request);

        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testGetNoFileWillReturn404()
    {
        $request = new Request();

        $response = $this->controller->get($request, false);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGetFileReturnsStreamedResponse()
    {
        $this->withFileResource();

        $request = new Request();
        $file    = $this->getFileEntity();

        /**
         * @var StreamedResponse
         */
        $response = $this->controller->get($request, $file);

        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\StreamedResponse', $response);
        $this->assertEquals('text/plain', $response->headers->get('content-type'));
        $this->assertEquals('inline; filename=Test.txt', $response->headers->get('content-disposition'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(self::TEST_FILE_CONTENTS, $output);
    }

    public function testDownloadFileReturnsProperDisposition()
    {
        $this->withFileResource();

        $request = new Request(['download' => 1]);
        $file    = $this->getFileEntity();

        /**
         * @var StreamedResponse
         */
        $response = $this->controller->get($request, $file);
        $this->assertEquals('attachment; filename=Test.txt', $response->headers->get('content-disposition'));
    }
}
