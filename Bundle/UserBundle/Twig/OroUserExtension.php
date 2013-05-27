<?php

namespace Oro\Bundle\UserBundle\Twig;

use Symfony\Component\Form\FormView;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\UserBundle\Acl\ManagerInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

class OroUserExtension extends \Twig_Extension
{
    /**
     * @var \Oro\Bundle\UserBundle\Acl\ManagerInterface
     */
    private $manager;

    public function __construct(ManagerInterface $manager)
    {
        $this->manager = $manager;
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
