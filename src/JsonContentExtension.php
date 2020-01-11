<?php

namespace Bolt\Extension\Zillingen\JsonContent;

use Bolt\Extension\SimpleExtension;

class JsonContentExtension extends SimpleExtension
{
    /**
     * {@inheritDoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'path' => '/api/content',
            'auth' => [
                'enabled' => true,
                'access_token' => '__TOKEN__'
            ]
        ];
    }

    /**
     * {@inheritDoc}
     * @return array
     */
    protected function registerFrontendControllers()
    {
        $config = $this->getConfig();

        return [
            $config['path'] => new Controller\ContentController($config),
        ];
    }
}