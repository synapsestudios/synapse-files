<?php
namespace Test\Synapse\File;

use Aws\S3\S3Client;

class MockS3Client extends S3Client
{
    public function __construct()
    {

    }

    public function putObject()
    {
        return true;
    }

    public function headObject()
    {
        return new MockObjectArray();
    }

    public function deleteObject()
    {
        return true;
    }
}
