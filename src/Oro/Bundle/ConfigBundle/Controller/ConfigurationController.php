<?php

namespace Oro\Bundle\ConfigBundle\Controller;

use Oro\Bundle\ConfigBundle\Provider\SystemConfigurationFormProvider;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ConfigurationController extends Controller
{
    /**
     * @Template()
     * @AclAncestor("oro_config_system")
     */
    public function systemAction()
    {
        return [];
    }
}
