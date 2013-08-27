<?php

namespace Oro\Bundle\EmailBundle\Provider;

use Doctrine\Common\Cache\Cache;

use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;

class EmailRenderer extends \Twig_Environment
{
    /** @var  Cache|null */
    protected $sandBoxConfigCache;

    /** @var  ConfigProvider */
    protected $configProvider;

    /** @var  string */
    protected $cacheKey;

    /** @var User */
    protected $user;

    public function __construct(
        \Twig_LoaderInterface $loader,
        $options,
        ConfigProvider $configProvider,
        Cache $cache,
        $cacheKey,
        SecurityContextInterface $securityContext,
        \Twig_Extension_Sandbox $sandbox
    ) {
        parent::__construct($loader, $options);

        $this->configProvider = $configProvider;
        $this->sandBoxConfigCache = $cache;
        $this->cacheKey = $cacheKey;
        $this->user = $securityContext->getToken() && !is_string($securityContext->getToken()->getUser())
            ? $securityContext->getToken()->getUser() : false;

        $this->addExtension($sandbox);
        $this->configureSandbox();
    }

    /**
     * Configure sandbox form config data
     *
     */
    protected function configureSandbox()
    {
        $allowedData = $this->sandBoxConfigCache->fetch($this->cacheKey);

        if (false === $allowedData) {
            $allowedData = $this->prepareConfiguration();
            $this->sandBoxConfigCache->save($this->cacheKey, serialize($allowedData));
        } else {
            $allowedData = unserialize($allowedData);
        }

        /** @var \Twig_Extension_Sandbox $sandbox */
        $sandbox = $this->getExtension('sandbox');
        /** @var \Twig_Sandbox_SecurityPolicy $security */
        $security = $sandbox->getSecurityPolicy();
        $security->setAllowedMethods($allowedData);
    }

    /**
     * Prepare configuration from entity config
     *
     * @return array
     */
    private function prepareConfiguration()
    {
        $configuration = array();

        /**
         * @TODO Change when new code of entity config will be merged
         */
        foreach ($this->configProvider->getAllConfigurableEntityNames() as $className) {
            $config = $this->configProvider->getConfig($className);
            $fields = $config->getFields(
                function (FieldConfig $field) {
                    return $field->is('available_in_template');
                }
            );

            if (!$fields->isEmpty()) {
                $configuration[$className] = array();
                foreach ($fields as $field) {
                    $configuration[$className][] = 'get' . strtolower($field->getCode());
                }
            }
        }

        return $configuration;
    }

    /**
     * Compile email message
     *
     * @param EmailTemplate $entity
     * @param array $templateParams
     *
     * @return array first element is email subject, second - message
     */
    public function compileMessage(EmailTemplate $entity, array $templateParams = array())
    {
        // ensure we have no html tags in txt template
        $content = $entity->getContent();
        $content = $entity->getType() == 'txt' ? strip_tags($content) : $content;

        $templateParams['user'] = $this->user;

        $templateRendered = $this->render('{% verbatim %}'.$content.'{% endverbatim %}', $templateParams);
        $subjectRendered  = $this->render('{% verbatim %}'.$entity->getSubject().'{% endverbatim %}', $templateParams);

        return array($subjectRendered, $templateRendered);
    }
}
