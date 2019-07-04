<?php

namespace Oro\Bundle\ConfigBundle\Controller\Rest;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Config\FileLocator;

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

    /** @var FileLocator */
    protected $fileLocator;

    /** @var string */
    protected $loadingMessagesFile;

    /** @var array */
    protected $options;

    /**
     * @param ConfigManager $configManager
     * @param FileLocator   $fileLocator
     * @param string        $loadingMessagesFile
     * @param array         $options
     */
    public function __construct(
        ConfigManager $configManager,
        FileLocator $fileLocator,
        $loadingMessagesFile,
        array $options = []
    ) {
        $this->configManager = $configManager;
        $this->fileLocator = $fileLocator;
        $this->loadingMessagesFile = $loadingMessagesFile;
        $this->options = $options;
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
            $value = $this->configManager->get($modelKey);

            if ($option['name'] === 'loading_messages' && $value === null) {
                $value = file_get_contents($this->getMessagesFilePath());
            }

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

        return $this->getAction();
    }

    /**
     * Returns messages file path
     *
     * @return string
     */
    protected function getMessagesFilePath()
    {
        return $this->fileLocator->locate($this->loadingMessagesFile);
    }
}
