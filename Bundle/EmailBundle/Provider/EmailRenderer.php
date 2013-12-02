<?php

namespace Oro\Bundle\EmailBundle\Provider;

use Doctrine\Common\Cache\Cache;

use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EmailBundle\Model\EmailTemplateInterface;

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

        foreach ($this->configProvider->getIds() as $entityConfigId) {
            $className = $entityConfigId->getClassName();
            $fields    = $this->configProvider->filter(
                function (ConfigInterface $fieldConfig) {
                    return $fieldConfig->is('available_in_template');
                },
                $className
            );

            if (count($fields)) {
                $configuration[$className] = array();
                foreach ($fields as $fieldConfig) {
                    $configuration[$className][] = 'get' . strtolower($fieldConfig->getId()->getFieldName());
                }
            }
        }

        return $configuration;
    }

    /**
     * Compile email message
     *
     * @param EmailTemplateInterface $template
     * @param array                  $templateParams
     *
     * @return array first element is email subject, second - message
     */
    public function compileMessage(EmailTemplateInterface $template, array $templateParams = array())
    {
        // ensure we have no html tags in txt template
        $content = $template->getContent();
        $content = $template->getType() == 'txt' ? strip_tags($content) : $content;

        $templateParams['user'] = $this->user;

        $templateRendered = $this->render($content, $templateParams);
        $subjectRendered  = $this->render($template->getSubject(), $templateParams);

        return array($subjectRendered, $templateRendered);
    }

    /**
     * Compile preview content
     *
     * @param EmailTemplate $entity
     *
     * @return string
     */
    public function compilePreview(EmailTemplate $entity)
    {
        // ensure we have no html tags in txt template
        $content = $entity->getContent();
        $content = $entity->getType() == 'txt' ? strip_tags($content) : $content;

        $templateParams['user'] = $this->user;

        $templateRendered = $this->render('{% verbatim %}' . $content . '{% endverbatim %}', $templateParams);

        return $templateRendered;
    }
}
