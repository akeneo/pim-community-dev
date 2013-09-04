<?php

namespace Oro\Bundle\ConfigBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\NavigationBundle\Annotation\TitleTemplate;
use Oro\Bundle\ConfigBundle\Provider\SystemConfigurationFormProvider;

class ConfigurationController extends Controller
{
    /**
     * @Route(
     *      "/system/{activeGroup}/{activeSubGroup}",
     *      name="oro_config_configuration_system",
     *      defaults={"activeGroup" = null, "activeSubGroup" = null}
     * )
     * @Template()
     * @TitleTemplate("System configuration")
     */
    public function systemAction($activeGroup = null, $activeSubGroup = null)
    {
        $provider = $this->container->get('oro_config.provider.system_configuration.form_provider');

        list($activeGroup, $activeSubGroup) = $provider->chooseActiveGroups($activeGroup, $activeSubGroup);

        return array(
            'data'           => $provider->getTreeData(SystemConfigurationFormProvider::TREE_NAME),
            'activeGroup'    => $activeGroup,
            'activeSubGroup' => $activeSubGroup,
        );
    }

    /**
     * @Route("/renderTab/{groupName}")
     * @Template()
     */
    public function renderGroupAction($groupName)
    {
        $provider = $this->container->get('oro_config.provider.system_configuration.form_provider');

        return array(
            'form'       => $provider->getForm($groupName)->createView(),
            'formAction' => $this->generateUrl('oro_config_configuration_rendergroup', array('groupName' => $groupName))
        );
    }
}
