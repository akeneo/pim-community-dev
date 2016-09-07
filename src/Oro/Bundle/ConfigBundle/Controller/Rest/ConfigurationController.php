<?php

namespace Oro\Bundle\ConfigBundle\Controller\Rest;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Configuration rest controller in charge of the system configuration managements
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationController
{
    /** @var ConfigManager */
    protected $configManager;

    /** @var string */
    protected $loadingMessagesFile;

    /** @var array */
    protected $options;

    /**
     * @param ConfigManager $configManager
     * @param string        $loadingMessagesFile
     * @param array         $options
     */
    public function __construct(ConfigManager $configManager, $loadingMessagesFile, array $options = [])
    {
        $this->configManager = $configManager;
        $this->options = $options;
        $this->loadingMessagesFile = $loadingMessagesFile;
    }

    /**
     * Get the current configuration
     *
     * @AclAncestor("oro_config_system")
     *
     * @return JsonResponse
     */
    public function getAction()
    {
        $data = [];

        foreach ($this->options as $option) {
            $viewKey = $option['section'] . ConfigManager::SECTION_VIEW_SEPARATOR . $option['name'];
            $modelKey = $option['section'] . ConfigManager::SECTION_MODEL_SEPARATOR . $option['name'];
            $value    = $option['name'] === 'loading_messages' ?
                        file_get_contents($this->loadingMessagesFile) :
                        $this->configManager->get($modelKey);

            $data[$viewKey] = [
                'value'                  => $value,
                'scope'                  => 'app',
                'use_parent_scope_value' => false
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * Set the current configuration
     *
     * @AclAncestor("oro_config_system")
     *
     * @return JsonResponse
     */
    public function postAction(Request $request)
    {
        $this->configManager->save(json_decode($request->getContent(), true));

        $data = json_decode($request->getContent(), true);
        file_put_contents($this->loadingMessagesFile, $data['pim_ui___loading_messages']['value']);

        return $this->getAction();
    }
}
