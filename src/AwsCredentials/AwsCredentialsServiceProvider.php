<?php

namespace Synapse\File\AwsCredentials;

use Silex\ServiceProviderInterface;
use Silex\Application;

/**
 * @codeCoverageIgnore
 */
class AwsCredentialsServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['aws-credentials.service'] = $app->share(function ($app) {
            return new AwsCredentialsService($_SERVER);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        // Noop
    }
}
