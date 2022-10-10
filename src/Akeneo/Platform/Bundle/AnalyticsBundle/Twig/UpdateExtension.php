<?php

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Twig;

use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension to detect if update notification is enabled and to provide the url to fetch the last patch
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateExtension extends AbstractExtension
{
    /** @var ConfigManager */
    protected $configManager;

    /** @var string */
    protected $updateServerUrl;

    public function __construct(ConfigManager $configManager, $updateServerUrl)
    {
        $this->configManager = $configManager;
        $this->updateServerUrl = $updateServerUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('is_last_patch_enabled', [$this, 'isLastPatchEnabled']),
            new TwigFunction('get_update_server_url', [$this, 'getUpdateServerUrl']),
        ];
    }

    /**
     * Indicates if the last patch should be fetched from update server
     *
     * @return bool
     */
    public function isLastPatchEnabled()
    {
        return $this->configManager->get('pim_analytics.version_update');
    }

    /**
     * @return string
     */
    public function getUpdateServerUrl()
    {
        return $this->updateServerUrl;
    }
}
