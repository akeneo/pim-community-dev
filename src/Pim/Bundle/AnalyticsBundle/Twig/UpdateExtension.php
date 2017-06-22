<?php

namespace Pim\Bundle\AnalyticsBundle\Twig;

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

    /**
     * @param ConfigManager $configManager
     * @param string        $updateServerUrl
     */
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
            new \Twig_SimpleFunction('is_last_patch_enabled', [$this, 'isLastPatchEnabled']),
            new \Twig_SimpleFunction('get_update_server_url', [$this, 'getUpdateServerUrl']),
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
