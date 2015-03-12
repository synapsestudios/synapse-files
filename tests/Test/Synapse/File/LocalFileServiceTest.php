<?php
namespace Test\Synapse\File;

use Synapse\File\LocalFileService;

class LocalFileServiceTest extends \PHPUnit_Framework_TestCase
{
    const TEST_BASE_PATH = '/tmp/test/localfileservicetest';
    /**
     * @var LocalFileService
     */
    private $fs;

    /**
     * Just a helper method to clean up
     *
     * @param $dir
     */
    private function rrmdir($dir)
    {
        if (file_exists($dir) && is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object) == "dir") {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function setUp()
    {
        parent::setUp();
        $this->rrmdir(self::TEST_BASE_PATH);
        $this->setupFileService();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->rrmdir(self::TEST_BASE_PATH);
    }

    protected function setupFileService()
    {
        $this->fs = new LocalFileService(self::TEST_BASE_PATH);
    }

    public function testCanCreate()
    {
        $fs = new LocalFileService(self::TEST_BASE_PATH);
        $this->assertInstanceOf(
            '\Synapse\File\LocalFileService',
            $fs,
            'Did not correctly instantiate file service'
        );
    }

    /**
     * @expectedException \Synapse\File\FileException
     */
    public function testThrowsExceptionForUnwritablePath()
    {
        $fs = new LocalFileService('/');
    }

    public function testCanSaveFileFromResource()
    {
        $file1 = tmpfile();
        fwrite($file1, self::TEST_BASE_PATH);
        rewind($file1);

        $path = 'test/test.txt';

        $success = $this->fs->save($path, $file1);

        $this->assertTrue($success, 'Could not save file');
        $fullPath = self::TEST_BASE_PATH . DIRECTORY_SEPARATOR . $path;
        $this->assertFileExists($fullPath, 'File was not created');
    }

    public function testCanSaveFileFromString()
    {
        $data = self::TEST_BASE_PATH;

        $path = 'test/test.txt';

        $success = $this->fs->save($path, $data);

        $this->assertTrue($success, 'Could not save file');
        $fullPath = self::TEST_BASE_PATH . DIRECTORY_SEPARATOR . $path;
        $this->assertFileExists($fullPath, 'File was not created');
    }

    /**
     * @expectedException \Synapse\File\FileException
     */
    public function testSavingFileWithPathGivenAsExistingFileThrowsFileException()
    {
        $data = self::TEST_BASE_PATH;

        $path = 'test/test.txt';

        $success = $this->fs->save($path, $data);

        $this->assertTrue($success, 'Could not save file');

        // now save over file path
        $path = 'test/test.txt/test.txt';
        $this->fs->save($path, $data);
    }

    public function testCanReadFile()
    {
        $data = self::TEST_BASE_PATH;

        $path = 'test/test.txt';

        $this->fs->save($path, $data);

        $file = $this->fs->load($path);

        $this->assertInternalType('resource', $file, 'Loading file did not return resource');

        $fileData = stream_get_contents($file);
        $this->assertEquals(self::TEST_BASE_PATH, $fileData, 'Incorrect data saved to file');
    }

    public function testCanDeleteFile()
    {
        $data = self::TEST_BASE_PATH;

        $path = 'test/test.txt';

        $this->fs->save($path, $data);

        $success = $this->fs->delete($path);

        $this->assertTrue($success, 'Unable to delete file');

        $this->assertFileNotExists(
            self::TEST_BASE_PATH . DIRECTORY_SEPARATOR . $path,
            'Actual file was not removed.'
        );
    }

    public function testCanUpdateFile()
    {
        $data = self::TEST_BASE_PATH;

        $path = 'test/test.txt';

        $this->fs->save($path, $data);

        $success = $this->fs->update($path, $data . '1');
        $this->assertTrue($success, 'Unable to update file');
        $res = $this->fs->load($path);

        $this->assertEquals(
            self::TEST_BASE_PATH . '1',
            stream_get_contents($res),
            'Updated file did not contain correct string'
        );
    }

    public function testCanUpdateFileWithResource()
    {
        $data = fopen('php://memory', 'rw');

        $path     = 'test/test.txt';

        $this->fs->save($path, '');
        fwrite($data, self::TEST_BASE_PATH);
        rewind($data);

        $success = $this->fs->update($path, $data);
        $this->assertTrue($success, 'Unable to update file');
        $res = $this->fs->load($path);

        $this->assertEquals(
            self::TEST_BASE_PATH,
            stream_get_contents($res),
            'Updated file did not contain correct string'
        );
    }

    public function testGetMimeType()
    {
        $data = self::TEST_BASE_PATH;

        $path = 'test/test.txt';

        $this->fs->save($path, $data);

        $type = $this->fs->getContentType($path);

        $this->assertEquals('text/plain', $type, 'Did not detect correct mime type');
    }

    public function testGetFileSize()
    {
        $data = self::TEST_BASE_PATH;

        $path = 'test/test.txt';

        $this->fs->save($path, $data);

        $size = $this->fs->getFileSize($path);
        $this->assertEquals(strlen(self::TEST_BASE_PATH), $size, 'Did not return expected file size');
    }

    /**
     * @throws \Synapse\File\FileException
     * @throws \Synapse\File\FileExistsException
     * @expectedException \Synapse\File\FileExistsException
     */
    public function testSaveNonexistantFileThrowsException()
    {
        touch(self::TEST_BASE_PATH.'/test.txt');

        $this->fs->save('', 'test.txt', self::TEST_BASE_PATH);
    }

    /**
     * @throws \Synapse\File\FileException
     * @throws \Synapse\File\FileExistsException
     * @expectedException \Synapse\File\FileException
     */
    public function testSaveToFileAsPathThrowsException()
    {
        touch(self::TEST_BASE_PATH.'/test');

        $this->fs->save('test', 'test.txt', self::TEST_BASE_PATH);
    }
}
