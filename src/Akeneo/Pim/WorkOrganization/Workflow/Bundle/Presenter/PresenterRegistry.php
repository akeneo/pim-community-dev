<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Twig extension to present product draft changes
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class PresenterRegistry
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    protected TranslatorInterface $translator;

    /** @var PresenterInterface[] */
    protected $presenters = [];

    /** @var ValueFactory */
    protected $valueFactory;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        TranslatorInterface $translator,
        ValueFactory $valueFactory
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->translator = $translator;
        $this->valueFactory = $valueFactory;
    }

    /**
     * Present an attribute change
     *
     * @param EntityWithValuesDraftInterface $productDraft
     * @param array                                $change
     * @param string                               $attributeCode
     *
     * @return string
     *@throws \LogicException
     *
     * @throws \InvalidArgumentException
     */
    public function presentChange(EntityWithValuesDraftInterface $productDraft, array $change, string $attributeCode)
    {
        $formerValue = $productDraft
            ->getEntityWithValue()
            ->getValue($attributeCode, $change['locale'], $change['scope']);
        $formerData = (null !== $formerValue) ? $formerValue->getData() : null;

        return $this->present($attributeCode, $formerData, $change);
    }

    /**
     * Add a presenter
     *
     * @param PresenterInterface $presenter
     * @param int                $priority
     */
    public function addPresenter(PresenterInterface $presenter, $priority)
    {
        $this->presenters[$priority][] = $presenter;
    }

    /**
     * Get the registered presenters
     *
     * @return PresenterInterface[]
     */
    public function getPresenters()
    {
        krsort($this->presenters);
        $presenters = [];
        foreach ($this->presenters as $groupedPresenters) {
            $presenters = array_merge($presenters, $groupedPresenters);
        }

        return $presenters;
    }

    protected function present(string $attributeCode, $data, array $change)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        foreach ($this->getPresenters() as $presenter) {
            if ($presenter->supports($attribute->getType(), $attribute->getReferenceDataName())) {
                if ($presenter instanceof TranslatorAwareInterface) {
                    $presenter->setTranslator($this->translator);
                }

                return $presenter->present($data, array_merge($change, [
                    'attribute' => $attributeCode,
                    'reference_data_name' => $attribute->getReferenceDataName()
                ]));
            }
        }

        throw new \LogicException(
            sprintf(
                'No presenter supports the provided change with key(s) "%s"',
                implode(', ', array_keys($change))
            )
        );
    }
}
