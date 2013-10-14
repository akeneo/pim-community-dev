<?php

namespace Oro\Bundle\HelpBundle\Twig;

use Oro\Bundle\HelpBundle\Model\HelpLinkProvider;

class HelpExtension extends \Twig_Extension
{
    const NAME = 'oro_help';

    /**
     * @var HelpLinkProvider
     */
    protected $linkProvider;

    public function __construct(HelpLinkProvider $linkProvider)
    {
        $this->linkProvider = $linkProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('get_help_link', array($this, 'getHelpLinkUrl')),
        );
    }

    /**
     * Get help link
     *
     * @return bool
     */
    public function getHelpLinkUrl()
    {
        return $this->linkProvider->getHelpLinkUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
