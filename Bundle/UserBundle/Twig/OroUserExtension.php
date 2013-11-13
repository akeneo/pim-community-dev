<?php

namespace Oro\Bundle\UserBundle\Twig;

use Oro\Bundle\UserBundle\Provider\GenderProvider;

class OroUserExtension extends \Twig_Extension
{
    /**
     * @var GenderProvider
     */
    protected $genderProvider;

    /**
     * @param GenderProvider $genderProvider
     */
    public function __construct(GenderProvider $genderProvider)
    {
        $this->genderProvider = $genderProvider;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_gender' => new \Twig_Function_Method($this, 'getGenderLabel'),
        );
    }

    /**
     * @param string $name
     * @return string
     */
    public function getGenderLabel($name)
    {
        if (!$name) {
            return null;
        }

        return $this->genderProvider->getLabelByName($name);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'user_extension';
    }
}
