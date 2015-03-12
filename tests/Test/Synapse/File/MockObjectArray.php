<?php
namespace Test\Synapse\File;

class MockObjectArray
{
    public function toArray()
    {
        return [
            'ContentType' => 'text/plain',
            'ContentLength' => strlen(S3FileServiceTest::TEST_BASE_PATH)
        ];
    }
}
