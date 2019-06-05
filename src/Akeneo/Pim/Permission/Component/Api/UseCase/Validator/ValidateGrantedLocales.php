<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Api\UseCase\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\Validator\ValidateGrantedLocalesInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Api\Exception\InvalidQueryException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Webmozart\Assert\Assert;

final class ValidateGrantedLocales implements ValidateGrantedLocalesInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(
        IdentifiableObjectRepositoryInterface $localeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->localeRepository = $localeRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForLocaleCodes(?array $localeCodes): void
    {
        if (null === $localeCodes || empty($localeCodes)) {
            return;
        }

        $notGrantedLocaleCodes = [];
        foreach ($localeCodes as $localeCode) {
            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            Assert::notNull($locale);

            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
                $notGrantedLocaleCodes[] = $localeCode;
            }
        }

        if (!empty($notGrantedLocaleCodes)) {
            $plural = count($notGrantedLocaleCodes) > 1 ?
                'Locales "%s" do not exist or are not activated.' : 'Locale "%s" does not exist or is not activated.';
            throw new InvalidQueryException(sprintf($plural, implode(', ', $notGrantedLocaleCodes)));
        }
    }
}
