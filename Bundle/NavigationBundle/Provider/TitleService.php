<?php

namespace Oro\Bundle\NavigationBundle\Provider;

use JMS\Serializer\Exception\RuntimeException;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Routing\Route;
use JMS\Serializer\Serializer;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\NavigationBundle\Entity\Title;
use Oro\Bundle\NavigationBundle\Title\TitleReader\ConfigReader;
use Oro\Bundle\NavigationBundle\Title\TitleReader\AnnotationsReader;
use Oro\Bundle\NavigationBundle\Title\StoredTitle;
use Oro\Bundle\NavigationBundle\Menu\BreadcrumbManager;
use Oro\Bundle\ConfigBundle\Config\UserConfigManager;

use Doctrine\ORM\EntityRepository;

class TitleService implements TitleServiceInterface
{
    /**
     * Title template
     *
     * @var string
     */
    private $template;

    /**
     * Title data readers
     *
     * @var array
     */
    private $readers = array();

    /**
     * Current title template params
     *
     * @var array
     */
    private $params = array();

    /**
     * Current title suffix
     *
     * @var array
     */
    private $suffix = null;

    /**
     * Current title prefix
     *
     * @var array
     */
    private $prefix = null;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var Serializer
     */
    protected $serializer = null;

    /** @var array */
    protected $titles = null;

    /**
     * @var BreadcrumbManager
     */
    protected $breadcrumbManager;

    /**
     * @var UserConfigManager
     */
    protected $userConfigManager;

    public function __construct(
        AnnotationsReader $reader,
        ConfigReader $configReader,
        Translator $translator,
        ObjectManager $em,
        Serializer $serializer,
        UserConfigManager $userConfigManager,
        BreadcrumbManager $breadcrumbManager
    ) {
        $this->readers = array($reader, $configReader);
        $this->translator = $translator;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->userConfigManager = $userConfigManager;
        $this->breadcrumbManager = $breadcrumbManager;
    }

    /**
     * Return rendered translated title
     *
     * @param array  $params
     * @param string $title
     * @param string $prefix
     * @param string $suffix
     * @param bool   $isJSON
     * @return $this
     */
    public function render($params = array(), $title = null, $prefix = null, $suffix = null, $isJSON = false)
    {
        if (!is_null($title) && $isJSON) {
            try {
                /** @var $data \Oro\Bundle\NavigationBundle\Title\StoredTitle */
                $data =  $this->serializer->deserialize($title, 'Oro\Bundle\NavigationBundle\Title\StoredTitle', 'json');

                $params = $data->getParams();
                $title = $data->getTemplate();
                $prefix = $data->getPrefix();
                $suffix = $data->getSuffix();
            } catch (RuntimeException $e) {
                // wrong json string - ignore title
                $params = array();
                $title  = 'Untitled';
                $prefix = '';
                $suffix = '';
            }
        }

        if (is_null($title)) {
            $title = $this->getTemplate();
        }
        if (is_null($prefix)) {
            $prefix = $this->prefix;
        }
        if (is_null($suffix)) {
            $suffix = $this->suffix;
        }
        if (empty($params)) {
            $params = $this->getParams();
        }

        $trans = $this->translator;

        $translatedTemplate = $trans->trans($title, $params);

        $translatedTemplate = $trans->trans($prefix, $params) . $translatedTemplate . $trans->trans($suffix, $params);

        return $translatedTemplate;
    }

    /**
     * Set properties from array
     *
     * @param array $values
     * @return $this
     */
    public function setData(array $values)
    {
        if (isset($values['titleTemplate'])
            && ($this->getTemplate() == null
            || (isset($values['force']) && $values['force']))
        ) {
            $this->setTemplate($values['titleTemplate']);
        }
        if (isset($values['params'])) {
            $this->setParams($values['params']);
        }
        if (isset($values['prefix'])) {
            $this->setPrefix($values['prefix']);
        }
        if (isset($values['suffix'])) {
            $this->setSuffix($values['suffix']);
        }

        return $this;
    }

    /**
     * Set string suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Set string prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Set template string
     *
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template string
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Return params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Setter for params
     *
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Load title template from database, fallback to config values
     *
     * @param string $route
     */
    public function loadByRoute($route)
    {
        /** @var $bdData Title */
        $bdData = $this->getStoredTitlesRepository()->findOneBy(
            array('route' => $route)
        );

        if ($bdData) {
            $this->setTemplate($bdData->getTitle());
        } elseif (isset($this->titles[$route])) {
            $this->setTemplate($this->titles[$route]);
        }
    }

    /**
     * Return stored titles repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getStoredTitlesRepository()
    {
        return $this->em->getRepository('Oro\Bundle\NavigationBundle\Entity\Title');
    }

    /**
     * Updates title index
     *
     * @param array $routes
     */
    public function update($routes)
    {
        $data = $routes;

        foreach ($this->readers as $reader) {
            /** @var $reader  \Oro\Bundle\NavigationBundle\Title\TitleReader\Reader */
            $data = array_merge($data, $reader->getData($routes));
        }

        $bdData = $this->getStoredTitlesRepository()->findAll();

        foreach ($bdData as $entity) {
            /** @var $entity Title */

            if (!array_key_exists($entity->getRoute(), $data)) {
                // remove not existing entries
                $this->em->remove($entity);

                continue;
            }

            $route = $entity->getRoute();
            $title = '';

            if (!$data[$route] instanceof Route) {
                $title = $data[$route];
            }

            // update existing system titles
            if ($entity->getIsSystem()) {
                $title = $this->createTile($route, $title);
                if (!$title) {
                    $title = '';
                }
                $entity->setTitle($title);
                $this->em->persist($entity);
            }

            unset($data[$route]);
        }

        // create title items for new routes
        foreach ($data as $route => $title) {
            if ($title = $this->createTile($route, $title)) {
                $entity = new Title();
                $entity->setTitle($title);
                $entity->setRoute($route);
                $entity->setIsSystem(true);

                $this->em->persist($entity);
            }
        }

        $this->em->flush();
    }

    protected function createTile($route, $title)
    {
        if (!($title instanceof Route)) {
            $titleData = array();

            if ($title) {
                $titleData[] = $title;
            }

            $breadcrumbLabels = $this->breadcrumbManager->getBreadcrumbLabels(
                $this->userConfigManager->get('oro_navigation.breadcrumb_menu'),
                $route
            );
            if (count($breadcrumbLabels)) {
                $titleData = array_merge($titleData, $breadcrumbLabels);
            }

            if ($globalTitleSuffix = $this->userConfigManager->get('oro_navigation.title_suffix')) {
                $titleData[] = $globalTitleSuffix;
            }

            return implode(' ' . $this->userConfigManager->get('oro_navigation.title_delimiter') . ' ', $titleData);
        }

        return false;
    }

    /**
     * Return serialized title data
     *
     * @return string
     */
    public function getSerialized()
    {
        $storedTitle = new StoredTitle();
        $storedTitle
            ->setTemplate($this->getTemplate())
            ->setParams($this->getParams())
            ->setPrefix($this->prefix)
            ->setSuffix($this->suffix);

        return $this->serializer->serialize($storedTitle, 'json');
    }

    /**
     * Inject titles from config
     *
     * @param $titles
     */
    public function setTitles($titles)
    {
        $this->titles = $titles;
    }
}
