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
                'access_token' => 'ahn4uPhie1xoph8Ero3Shutaigh8Eoh0'
            ]
        ];
    }

    protected function registerFrontendControllers()
    {
        $config = $this->getConfig();

        // TODO: add content controller
    }
}