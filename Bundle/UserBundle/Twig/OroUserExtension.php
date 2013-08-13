<?php

namespace Oro\Bundle\UserBundle\Twig;

use Symfony\Component\Form\FormView;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\UserBundle\Acl\ManagerInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\UserBundle\Provider\GenderProvider;

class OroUserExtension extends \Twig_Extension
{
    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @var GenderProvider
     */
    protected $genderProvider;

    /**
     * @param ManagerInterface $manager
     * @param GenderProvider $genderProvider
     */
    public function __construct(ManagerInterface $manager, GenderProvider $genderProvider)
    {
        $this->manager = $manager;
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
            'resource_granted' => new \Twig_Function_Method($this, 'checkResourceIsGranted'),
            'oro_gender'       => new \Twig_Function_Method($this, 'getGenderLabel'),
        );
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            'is_flexible' => new \Twig_Filter_Method($this, 'isFlexible'),
        );
    }

    /**
     * Check if ACL resource is grant for current user
     *
     * @param string $aclId ACL Resource id
     *
     * @return bool
     */
    public function checkResourceIsGranted($aclId)
    {
        return $this->manager->isResourceGranted($aclId);
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
     * Check if FormView is instance of FlexibleBundle
     *
     * @param $form
     *
     * @return bool
     */
    public function isFlexible($form)
    {
        if ($form instanceof FormView && isset($form->vars['value'])) {
            return (
                $form->vars['value'] instanceof Collection
                    && $form->vars['value']->first() instanceof FlexibleValueInterface
            ) || ($form->vars['value'] instanceof FlexibleValueInterface && !$form->offsetExists('collection'));
        }

        return false;
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
