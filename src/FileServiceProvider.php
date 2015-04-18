<?php

namespace Synapse\File;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Application\AwsCredentials\AwsCredentialsService;

/**
 * @codeCoverageIgnore
 */
class FileServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['file.service'] = $app->share(
            function (Application $app) {

                $awsCredentialsService = $app['aws-credentials.service'];
                $config                = $app['config']->load('file');

                if ($config['filesystem'] === 'local') {
                    if (!isset($config['base_path'])) {
                        throw new \RuntimeException('Invalid filesystem (file) configuration - missing base_path');
                    }

                    return new LocalFileService($config['base_path']);
                } elseif ($config['filesystem'] === 's3') {
                    if (array_key_exists('bucket', $config) && $awsCredentialsService->checkS3Credentials()) {
                        $s3Client = $app['aws']->get('s3');

                        return new S3FileService($config, $s3Client);
                    } else {
                        throw new \RuntimeException('Invalid file system configuration (s3)');
                    }
                }

                throw new \RuntimeException('Invalid filesystem configuration');
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        // Noop
    }
}
