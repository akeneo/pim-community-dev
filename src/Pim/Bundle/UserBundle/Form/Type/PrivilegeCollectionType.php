<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Oro\Bundle\SecurityBundle\Form\Type\PrivilegeCollectionType as OroPrivilegeCollectionType;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

/**
 * Overriden PrivilegeCollectionType to remove ACLs for disabled locales
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PrivilegeCollectionType extends OroPrivilegeCollectionType
{
    /**
     * @staticvar string
     */
    const LOCALE_ACL_PATTERN = 'action:pim_enrich_locale_';

    /**
     * @var array
     */
    protected $excludedAclPatterns = [
        'entity:Oro\Bundle\EmailBundle',
        'entity:Oro\Bundle\OrganizationBundle',
        'entity:Oro\Bundle\TagBundle',
        'action:oro_dataaudit',
        'action:oro_entityconfig',
        'action:oro_search',
        'action:oro_tag',
    ];

    /**
     * @var LocaleManager $localeManager
     */
    protected $localeManager;

    /**
     * Constructor
     *
     * @param LocaleManager $localeManager
     */
    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $activeLocaleCodes = $this->localeManager->getActiveCodes();

        foreach ($form->all() as $index => $subForm) {
            $id = $subForm->get('identity')->get('id')->getData();
            if (!$this->isDisplayable($id, $activeLocaleCodes)) {
                $form->remove($index);
            }
        }
    }

    /**
     * Check if the provided ACL id should be displayed
     * Filters out inactive locales and unused Oro ACLs
     *
     * @param string $aclId
     * @param array  $activeLocaleCodes
     *
     * @return boolean
     */
    protected function isDisplayable($aclId, array $activeLocaleCodes)
    {
        if (strpos($aclId, self::LOCALE_ACL_PATTERN) === 0) {
            $localeCode = str_replace(self::LOCALE_ACL_PATTERN, '', $aclId);
            if ($localeCode !== 'index' && !in_array($localeCode, $activeLocaleCodes)) {
                return false;
            }
        }

        foreach ($this->excludedAclPatterns as $pattern) {
            if (strpos($aclId, $pattern) === 0) {
                return false;
            }
        }

        return true;
    }
}
