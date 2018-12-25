<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Validation;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidationException;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidatorInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class IdentifiersMappingValidator implements ValidatorInterface
{
    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepo;

    /**
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepo
     */
    public function __construct(IdentifiersMappingRepositoryInterface $identifiersMappingRepo)
    {
        $this->identifiersMappingRepo = $identifiersMappingRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value): void
    {
        if ($this->identifiersMappingRepo->find()->isEmpty()) {
            throw new ValidationException('Identifiers mapping is empty');
        }
    }
}
