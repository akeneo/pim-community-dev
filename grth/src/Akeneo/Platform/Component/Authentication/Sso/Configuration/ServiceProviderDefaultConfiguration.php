<?php
declare(strict_types=1);

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration;

interface ServiceProviderDefaultConfiguration
{
    public function getServiceProvider(): ServiceProvider;
}
