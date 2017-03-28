<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Locale controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleController
{
    /**
     * List locales
     *
     * @Template
     * @AclAncestor("pim_enrich_locale_index")
     *
     * @return array
     */
    public function indexAction()
    {
        return [];
    }
}
