<?php

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Twig;

use Akeneo\Platform\VersionProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

/**
 * Twig extension to detect if update notification is enabled and to provide the url to fetch the last patch
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateExtension extends \Twig_Extension
{
    /** @var ConfigManager */
    protected $configManager;

    /** @var string */
    protected $updateServerUrl;

    /** @var VersionProviderInterface */
    private $versionProvider;

    public function __construct(ConfigManager $configManager, VersionProviderInterface $versionProvider, string $updateServerUrl)
    {
        $this->configManager = $configManager;
        $this->versionProvider = $versionProvider;
        $this->updateServerUrl = $updateServerUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('is_last_patch_enabled', fn() => $this->isLastPatchEnabled()),
            new \Twig_SimpleFunction('get_update_server_url', fn() => $this->getUpdateServerUrl()),
        ];
    }

    /**
     * Indicates if the last patch should be fetched from update server
     */
    public function isLastPatchEnabled(): bool
    {
        return !$this->versionProvider->isSaaSVersion() && $this->configManager->get('pim_analytics.version_update');
    }

    public function getUpdateServerUrl(): string
    {
        return $this->updateServerUrl;
    }
}
