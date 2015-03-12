<?php
namespace Test\Synapse\File;

use Synapse\File\S3FileService;
use Aws\S3\S3Client;

/**
 * Class S3FileServiceTest
 *
 * @package Test\Synapse\File
 *
 * @group   aws
 * @group   integration
 */
class S3FileServiceTestWithAWSIntegrationTest extends \PHPUnit_Framework_TestCase
{
    const TEST_BASE_PATH = 's3fileservicetest';
    /**
     * @var S3FileService
     */
    protected $fs;
    /**
     * @var array
     */
    protected $cfg;

    private static $bucket;

    public static function setupBeforeClass()
    {
        $cfg = require realpath(__DIR__ . '/../../../../config') . '/aws.php';
        if (empty($cfg['secret']) || empty($cfg['key'])) {
            return;
        } else {
            $s3           = S3Client::factory($cfg);
            self::$bucket = sha1(microtime());
            while ($s3->doesBucketExist(self::$bucket)) {
                self::$bucket = sha1(microtime());
            }
            $s3->createBucket(['Bucket' => self::$bucket]);
        }
    }

    public function setUp()
    {
        parent::setUp();
        $this->cfg = require realpath(__DIR__ . '/../../../../config') . '/aws.php';
        if (empty($this->cfg['secret']) || empty($this->cfg['key'])) {
            $this->markTestSkipped('AWS credentials not set up');
        }
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        $cfg = require realpath(__DIR__ . '/../../../../config') . '/aws.php';
        if (empty($cfg['secret']) || empty($cfg['key'])) {
            return;
        } else {
            $s3 = S3Client::factory($cfg);
            $s3->clearBucket(self::$bucket);
            $s3->deleteBucket(['Bucket' => self::$bucket]);
        }
    }

    protected function setupFileService($cfg)
    {
        $this->fs = new S3FileService(['bucket' => self::$bucket], S3Client::factory($cfg));
    }

    public function testCanCreate()
    {
        $fs = new S3FileService(['bucket' => self::$bucket], S3Client::factory($this->cfg));
        $this->assertInstanceOf('\Synapse\File\S3FileService', $fs, 'Did not correctly instantiate file service');
    }


    public function testCanSaveFileFromResource()
    {
        $this->setupFileService($this->cfg);
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
        $this->setupFileService($this->cfg);
        $data = self::TEST_BASE_PATH;

        $path     = 'test';
        $fileName = 'test.txt';

        $success = $this->fs->save($path, $fileName, $data);

        $this->assertNotEmpty($success, 'Could not save file');
    }

    public function testCanReadFile()
    {
        $this->setupFileService($this->cfg);
        $data = self::TEST_BASE_PATH;

        $path     = 'test';
        $fileName = 'test.txt';

        $this->fs->save($path, $fileName, $data);

        $filePath = $path . DIRECTORY_SEPARATOR . $fileName;

        $file = $this->fs->load($filePath);

        $this->assertInternalType('resource', $file, 'Loading file did not return resource');

        $fileData = stream_get_contents($file);
        $this->assertEquals(self::TEST_BASE_PATH, $fileData, 'Incorrect data saved to file');
    }

    public function testCanDeleteFile()
    {
        $this->setupFileService($this->cfg);
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

    public function testCanUpdateFile()
    {
        $this->setupFileService($this->cfg);
        $data = self::TEST_BASE_PATH;

        $path     = 'test';
        $fileName = 'test.txt';

        $this->fs->save($path, $fileName, $data);

        $success = $this->fs->update($path . DIRECTORY_SEPARATOR . $fileName, $data . '1');
        $this->assertTrue($success, 'Unable to update file');
        $res = $this->fs->load($path . DIRECTORY_SEPARATOR . $fileName);

        $this->assertEquals(
            self::TEST_BASE_PATH . '1',
            stream_get_contents($res),
            'Updated file did not contain correct string'
        );
    }

    public function testGetMimeType()
    {
        $this->setupFileService($this->cfg);
        $data = self::TEST_BASE_PATH;

        $path     = 'test';
        $fileName = 'test.txt';

        $this->fs->save($path, $fileName, $data, 'text/plain');

        $type = $this->fs->getContentType($path . DIRECTORY_SEPARATOR . $fileName);

        $this->assertEquals('text/plain', $type, 'Did not detect correct mime type');
    }

    public function testGetFileSize()
    {
        $this->setupFileService($this->cfg);
        $data = self::TEST_BASE_PATH;

        $path     = 'test';
        $fileName = 'test.txt';

        $this->fs->save($path, $fileName, $data);

        $size = $this->fs->getFileSize($path . DIRECTORY_SEPARATOR . $fileName);
        $this->assertEquals(strlen(self::TEST_BASE_PATH), $size, 'Did not return expected file size');
    }
}
