<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\Version;

use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Renders the full name of the author of the version and adds context
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorProperty extends FieldProperty
{
    /** @var UserManager */
    protected $userManager;

    /** @var string[] */
    protected $userCachedResults;

    /**
     * @param TranslatorInterface $translator
     * @param UserManager         $userManager
     */
    public function __construct(TranslatorInterface $translator, UserManager $userManager)
    {
        parent::__construct($translator);

        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRawValue(ResultRecordInterface $record)
    {
        $author = parent::getRawValue($record);

        try {
            $context = $record->getValue('context');
        } catch (\LogicException $e) {
            $context = null;
        }

        return [
            'author' => $author,
            'context' => $context,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        if (!isset($this->userCachedResults[$value['author']])) {
            $user = $this->userManager->findUserByUsername($value['author']);

            if (null === $user) {
                $userName = sprintf('%s - %s', $value['author'], $this->translator->trans('pim_user.user.removed_user'));
            } else {
                $userName = sprintf('%s %s - %s', $user->getFirstName(), $user->getLastName(), $user->getEmail());
            }

            $this->userCachedResults[$value['author']] = $userName;
        }

        $result = $this->userCachedResults[$value['author']];
        if (!empty($value['context'])) {
            $result .= sprintf(' (%s)', $value['context']);
        }

        return $result;
    }
}
