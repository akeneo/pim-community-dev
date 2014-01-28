<?php

namespace Pim\Bundle\NavigationBundle\Provider;

use Symfony\Component\Routing\Route;
use Oro\Bundle\NavigationBundle\Provider\TitleService as OroTitleService;

/**
 * Overriding TitleService to remove breadcrumb in createTitle method
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TitleService extends OroTitleService
{
    /**
     * {@inheritdoc}
     */
    protected function createTile($route, $title)
    {
        if (!($title instanceof Route)) {
            $titleData = array();

            if ($title) {
                $titleData[] = $title;
            }

            if ($globalTitleSuffix = $this->userConfigManager->get('oro_navigation.title_suffix')) {
                $titleData[] = $globalTitleSuffix;
            }

            return implode(' ' . $this->userConfigManager->get('oro_navigation.title_delimiter') . ' ', $titleData);
        }

        return false;
    }
}
