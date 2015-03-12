<?php

namespace Synapse\File\AwsCredentials;

class AwsCredentialsService
{
    protected $credentialVariables = array(
        'AWS_ACCESS_KEY_ID',
        'AWS_SECRET_ACCESS_KEY',
    );

    protected $kmsVariables = array(
        'AWS_MASTER_KEY_ID',
        'AWS_MASTER_KEY_REGION',
    );

    /**
     * @var array
     */
    protected $environment;

    public function __construct(array $environment)
    {
        $this->environment = $environment;
    }

    public function checkKmsCredentials()
    {
        $variables = array_merge($this->kmsVariables, $this->credentialVariables);
        return $this->checkCredentials($variables);
    }

    public function checkS3Credentials()
    {
        return $this->checkCredentials($this->credentialVariables);
    }

    protected function checkCredentials($credentialVariables)
    {
        $missing = false;
        for ($i = 0; $i < count($credentialVariables); $i++) {
            if (! isset($this->environment[$credentialVariables[$i]])) {
                $missing = true;
            }
        }

        return ! $missing;
    }
}
