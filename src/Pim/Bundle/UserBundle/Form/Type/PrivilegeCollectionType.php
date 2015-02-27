<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Oro\Bundle\SecurityBundle\Form\Type\PrivilegeCollectionType as OroPrivilegeCollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Overriden PrivilegeCollectionType to remove unused ACLs
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PrivilegeCollectionType extends OroPrivilegeCollectionType
{
    /** @var string[] */
    protected $excludedAclPatterns = [
        'action:oro_entityconfig',
    ];

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        foreach ($form->all() as $index => $subForm) {
            $id = $subForm->get('identity')->get('id')->getData();
            if (!$this->isDisplayable($id)) {
                $form->remove($index);
            }
        }
    }

    /**
     * Check if the provided ACL id should be displayed
     * Filters out unused Oro ACLs
     *
     * @param string $aclId
     *
     * @return boolean
     */
    protected function isDisplayable($aclId)
    {
        foreach ($this->excludedAclPatterns as $pattern) {
            if (strpos($aclId, $pattern) === 0) {
                return false;
            }
        }

        return true;
    }
}
