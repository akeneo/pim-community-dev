<?php

namespace Pim\Bundle\AnalyticsBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Pim\Bundle\AnalyticsBundle\UrlGenerator\UpdateUrlGeneratorInterface;

/**
 * Twig extension to detect if update notification is enabled
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateExtension extends \Twig_Extension
{
    /** @var ConfigManager */
    protected $configManager;

    /** @var UpdateUrlGeneratorInterface */
    protected $urlGenerator;

    /**
     * @param ConfigManager               $configManager
     * @param UpdateUrlGeneratorInterface $urlGenerator
     */
    public function __construct(ConfigManager $configManager, UpdateUrlGeneratorInterface $urlGenerator)
    {
        $this->configManager = $configManager;
        $this->urlGenerator  = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_last_patch_enabled', [$this, 'isLastPatchEnabled']),
            new \Twig_SimpleFunction('get_last_patch_url', [$this, 'getLastPatchUrl']),
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
    public function getLastPatchUrl()
    {
        return $this->urlGenerator->generateAvailablePatchsUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_notification_update_extension';
    }
}
