<?php

namespace Oro\Bundle\WindowsBundle\Twig;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Twig_Environment;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\WindowsBundle\Entity\WindowsState;

class WindowsExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'oro_windows';

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Protect extension from infinite loop
     *
     * @var bool
     */
    protected $rendered = false;

    /**
     * @param SecurityContextInterface $securityContext
     * @param EntityManager $em
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        EntityManager $em
    ) {
        $this->securityContext = $securityContext;
        $this->em = $em;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_windows_restore' => new \Twig_Function_Method(
                $this,
                'render',
                array(
                    'is_safe' => array('html'),
                    'needs_environment' => true
                )
            )
        );
    }

    /**
     * Renders a menu with the specified renderer.
     *
     * @param \Twig_Environment $environment
     * @param array $options
     *
     * @return string
     */
    public function render(Twig_Environment $environment, array $options = array())
    {
        if (!($user = $this->getUser()) || $this->rendered) {
            return '';
        }
        $this->rendered = true;

        $states = array();
        $windowsList = $this->em->getRepository('OroWindowsBundle:WindowsState')->findBy(array('user' => $user));
        /** @var $windowState WindowsState */
        foreach ($windowsList as $windowState) {
            $data = $windowState->getData();
            if (!$data) {
                $this->em->remove($windowState);
                $this->em->flush();
            } elseif (array_key_exists('cleanUrl', $data) && array_key_exists('type', $data)) {
                $data['cleanUrl'] = $this->getUrlWithContainer($data['cleanUrl'], $data['type']);
                $states[$windowState->getId()] = $data;
            }
        }

        return $environment->render(
            "OroWindowsBundle::states.html.twig",
            array("states" => $states)
        );
    }

    /**
     * Get a user from the Security Context
     *
     * @return null|mixed
     * @throws \LogicException If SecurityBundle is not available
     * @see Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
     */
    public function getUser()
    {
        /** @var $token TokenInterface */
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    protected function getUrlWithContainer($url, $container)
    {
        if (strpos($url, '_widgetContainer=') === false) {
            $parts = parse_url($url);
            $widgetPart = '_widgetContainer=' . $container;
            if (array_key_exists('query', $parts)) {
                $separator = $parts['query'] ? '&' : '';
                $newQuery = $parts['query'] . $separator . $widgetPart;
                $url = str_replace($parts['query'], $newQuery, $url);
            } else {
                $url .= '?' . $widgetPart;
            }
        }
        return $url;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }
}
