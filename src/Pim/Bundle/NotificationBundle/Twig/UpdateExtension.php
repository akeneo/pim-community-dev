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
     * Constructor
     *
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
            new \Twig_SimpleFunction('last_patch_is_enabled', [$this, 'lastPatchIsEnabled'])
        ];
    }

    /**
     * Indicates if the last patch should be fetched from update server
     *
     * @return bool
     */
    public function lastPatchIsEnabled()
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
