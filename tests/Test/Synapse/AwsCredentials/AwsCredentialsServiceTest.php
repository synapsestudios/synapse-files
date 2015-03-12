<?php

namespace Test\Synapse\File\AwsCredentials;

use PHPUnit_Framework_TestCase;
use Synapse\File\AwsCredentials\AwsCredentialsService;

class AwsCredentialsServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AwsCredentialsService
     */
    protected $awsCredentialsService;

    public function setUp()
    {

    }

    public function withAllCredentials()
    {
        $this->awsCredentialsService = new AwsCredentialsService(array(
            'AWS_ACCESS_KEY_ID'     => 'TESTCREDENTIAL',
            'AWS_SECRET_ACCESS_KEY' => 'TESTCREDENTIAL',
            'AWS_MASTER_KEY_ID'     => 'TESTCREDENTIAL',
            'AWS_MASTER_KEY_REGION' => 'TESTCREDENTIAL',
        ));
    }

    public function withMissingKmsCredentials()
    {
        $this->awsCredentialsService = new AwsCredentialsService(array(
            'AWS_ACCESS_KEY_ID'     => 'TESTCREDENTIAL',
            'AWS_SECRET_ACCESS_KEY' => 'TESTCREDENTIAL',
            'AWS_MASTER_KEY_ID'     => 'TESTCREDENTIAL',
        ));
    }

    public function withMissingS3Credentials()
    {
        $this->awsCredentialsService = new AwsCredentialsService(array(
            'AWS_ACCESS_KEY_ID'     => 'TESTCREDENTIAL',
        ));
    }

    public function testKmsCredentialsSuccess()
    {
        $this->withAllCredentials();
        $this->assertTrue($this->awsCredentialsService->checkKmsCredentials());
    }

    public function testKmsCredentialsFail()
    {
        $this->withMissingKmsCredentials();
        $this->assertFalse($this->awsCredentialsService->checkKmsCredentials());
    }

    public function testS3CredentialsSuccess()
    {
        $this->withAllCredentials();
        $this->assertTrue($this->awsCredentialsService->checkS3Credentials());
    }

    public function testS3CredentialsFail()
    {
        $this->withMissingS3Credentials();
        $this->assertFalse($this->awsCredentialsService->checkS3Credentials());
    }
}
