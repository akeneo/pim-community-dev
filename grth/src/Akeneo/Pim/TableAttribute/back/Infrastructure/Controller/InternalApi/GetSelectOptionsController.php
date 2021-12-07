<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Controller\InternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\TableConfigurationFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GetSelectOptionsController
{
    private AttributeRepositoryInterface $attributeRepository;
    private TableConfigurationFactory $tableConfigurationFactory;
    private SelectOptionCollectionRepository $optionCollectionRepository;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        TableConfigurationFactory $tableConfigurationFactory,
        SelectOptionCollectionRepository $optionCollectionRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->tableConfigurationFactory = $tableConfigurationFactory;
        $this->optionCollectionRepository = $optionCollectionRepository;
    }

    public function __invoke(Request $request, string $attributeCode, string $columnCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        if (null === $attribute) {
            throw new NotFoundHttpException(
                sprintf('The "%s" attribute is not found', $attributeCode)
            );
        }

        if ($attribute->getType() !== AttributeTypes::TABLE || $attribute->getRawTableConfiguration() === null) {
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => sprintf('The "%s" attribute is not a table attribute', $attributeCode),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tableConfiguration = $this->tableConfigurationFactory->createFromNormalized(
            $attribute->getRawTableConfiguration()
        );

        $column = $tableConfiguration->getColumnByCode(ColumnCode::fromString($columnCode));
        if (null === $column) {
            throw new NotFoundHttpException(
                sprintf('The "%s" column is not found', $columnCode)
            );
        }

        if ($column->dataType()->asString() !== SelectColumn::DATATYPE) {
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => sprintf('The "%s" column is not a select', $columnCode),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $optionCollection = $this->optionCollectionRepository->getByColumn(
            $attributeCode,
            ColumnCode::fromString($columnCode)
        );

        return new JsonResponse($optionCollection->normalize());
    }
}
