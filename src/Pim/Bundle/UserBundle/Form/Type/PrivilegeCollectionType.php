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

        $enabledCodes = $this->localeManager->getActiveCodes();

        foreach ($form->all() as $index => $subForm) {
            $id = $subForm->get('identity')->get('id')->getData();
            if (strpos($id, self::LOCALE_ACL_PATTERN) === 0) {
                $localeCode = str_replace(self::LOCALE_ACL_PATTERN, '', $id);
                if ($localeCode !== 'index' && !in_array($localeCode, $enabledCodes)) {
                    $form->remove($index);
                }
            }
        }
    }
}
