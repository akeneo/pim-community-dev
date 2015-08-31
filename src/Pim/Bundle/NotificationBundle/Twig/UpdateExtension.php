<?php

namespace Pim\Bundle\NotificationBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

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

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_last_patch_enabled', [$this, 'isLastPatchEnabled'])
        ];
    }

    /**
     * Indicates if the last patch should be fetched from update server
     *
     * @return bool
     */
    public function isLastPatchEnabled()
    {
        return $this->configManager->get('pim_notification.version_update');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_notification_update_extension';
    }
}
