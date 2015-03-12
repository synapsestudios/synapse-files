<?php
namespace Test\Synapse\File;

use Synapse\File\S3FileService;
use Aws\S3\S3Client;
use Prophecy\Prophecy\ProphecyInterface;

/**
 * Class S3FileServiceTest
 *
 * @package Test\Synapse\File
 *
 */
class S3FileServiceTest extends \PHPUnit_Framework_TestCase
{
    const TEST_BASE_PATH = 's3fileservicetest';
    /**
     * @var S3FileService
     */
    protected $fs;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MockS3Client|S3Client
     */
    protected $s3Client;
    protected static $bucket;

    protected function setupFileService()
    {
        $this->s3Client = $this->getMock(
            'Test\Synapse\File\MockS3Client',
            ['putObject', 'headObject', 'deleteObject']
        );
        $this->fs       = new S3FileService(['bucket' => self::$bucket], $this->s3Client);
        $wrappers       = stream_get_wrappers();
        if (in_array('s3', $wrappers)) {
            stream_wrapper_unregister('s3');
        }
        stream_wrapper_register('s3', '\Test\Synapse\File\MockStreamWrapper');
    }

    protected function withPutObjectReturning()
    {
        $this->s3Client
            ->expects($this->once())
            ->method('putObject')
            ->willReturn(true);
    }

    protected function withHeadObjectReturning()
    {
        $this->s3Client
            ->expects($this->once())
            ->method('headObject')
            ->willReturn(new MockObjectArray());
    }

    protected function withDeleteObjectReturning()
    {
        $this->s3Client
            ->expects($this->once())
            ->method('deleteObject')
            ->willReturn(true);
    }

    protected function withPutObjectThrowingException()
    {
        $this->s3Client
            ->expects($this->once())
            ->method('putObject')
            ->willThrowException(new \Exception());
    }

    protected function withDeleteObjectThrowingException()
    {
        $this->s3Client
            ->expects($this->once())
            ->method('deleteObject')
            ->willThrowException(new \Exception());
    }

    protected function withHeadObjectThrowingException()
    {
        $this->s3Client
            ->expects($this->once())
            ->method('headObject')
            ->willThrowException(new \Exception());
    }

    public function testCanSaveFileFromResource()
    {
        $this->setupFileService();
        $this->withPutObjectReturning();
        $file1 = tmpfile();
        fwrite($file1, self::TEST_BASE_PATH);
        rewind($file1);

        $path     = 'test';
        $fileName = 'test.txt';

        $success = $this->fs->save($path, $fileName, $file1);

        $this->assertNotEmpty($success, 'Could not save file');
    }

    public function testCanSaveFileFromString()
    {
        $this->setupFileService();
        $this->withPutObjectReturning();
        $data = self::TEST_BASE_PATH;

        $path     = 'test';
        $fileName = 'test.txt';

        $success = $this->fs->save($path, $fileName, $data);

        $this->assertNotEmpty($success, 'Could not save file');
    }

    public function testCanReadFile()
    {
        $this->setupFileService();

        $path     = 'test';
        $fileName = 'test.txt';

        $filePath = $path . DIRECTORY_SEPARATOR . $fileName;

        $file = $this->fs->load($filePath);

        $this->assertInternalType('resource', $file, 'Loading file did not return resource');
    }

    public function testCanDeleteFile()
    {
        $this->setupFileService();
        $this->withDeleteObjectReturning();

        $data = self::TEST_BASE_PATH;

        $path     = 'test';
        $fileName = 'test.txt';

        $this->fs->save($path, $fileName, $data);

        $success = $this->fs->delete($path . DIRECTORY_SEPARATOR . $fileName);

        $this->assertTrue($success, 'Unable to delete file');

        $this->assertFileNotExists(
            self::TEST_BASE_PATH . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $fileName,
            'Actual file was not removed.'
        );
    }

    public function testCanUpdateFileFromString()
    {
        $this->setupFileService();
        $this->withPutObjectReturning();

        $data = self::TEST_BASE_PATH;

        $path     = 'test';
        $fileName = 'test.txt';

        $this->fs->save($path, $fileName, $data);

        $success = $this->fs->update($path . DIRECTORY_SEPARATOR . $fileName, $data . '1');
        $this->assertTrue($success, 'Unable to update file');
    }

    public function testCanUpdateFileFromResource()
    {
        $this->setupFileService();
        $this->withPutObjectReturning();
        $file1 = tmpfile();
        fwrite($file1, self::TEST_BASE_PATH);
        rewind($file1);

        $data = self::TEST_BASE_PATH;
        $path     = 'test';
        $fileName = 'test.txt';

        $this->fs->save($path, $fileName, $data);

        $success = $this->fs->update($path, $file1);

        $this->assertNotEmpty($success, 'Could not save file');
    }

    public function testGetMimeType()
    {
        $this->setupFileService();
        $this->withHeadObjectReturning();
        $data = self::TEST_BASE_PATH;

        $path     = 'test';
        $fileName = 'test.txt';

        $this->fs->save($path, $fileName, $data, 'text/plain');

        $type = $this->fs->getContentType($path . DIRECTORY_SEPARATOR . $fileName);

        $this->assertEquals('text/plain', $type, 'Did not detect correct mime type');
    }

    public function testGetFileSize()
    {
        $this->setupFileService();
        $this->withHeadObjectReturning();
        $data = self::TEST_BASE_PATH;

        $path     = 'test';
        $fileName = 'test.txt';

        $this->fs->save($path, $fileName, $data);

        $size = $this->fs->getFileSize($path . DIRECTORY_SEPARATOR . $fileName);
        $this->assertEquals(strlen(self::TEST_BASE_PATH), $size, 'Did not return expected file size');
    }

    /**
     * @throws \Synapse\File\FileException
     * @expectedException \Synapse\File\FileException
     */
    public function testSaveWithS3ThrowingExceptionThrowsFileException()
    {
        $this->setupFileService();
        $this->withPutObjectThrowingException();

        $data = self::TEST_BASE_PATH;

        $path     = 'test';
        $fileName = 'test.txt';

        $this->fs->save($path, $fileName, $data);
    }

    /**
     * @throws \Synapse\File\FileException
     * @expectedException \Synapse\File\FileException
     */
    public function testDeleteWithS3ThrowingExceptionThrowsFileException()
    {
        $this->setupFileService();
        $this->withDeleteObjectThrowingException();

        $path     = 'test';

        $this->fs->delete($path);
    }

    /**
     * @throws \Synapse\File\FileException
     * @expectedException \Synapse\File\FileException
     */
    public function testGetMetaDataWithS3ThrowingExceptionThrowsFileException()
    {
        $this->setupFileService();
        $this->withHeadObjectThrowingException();

        $path     = 'test';

        $this->fs->getFileSize($path);
    }
}
