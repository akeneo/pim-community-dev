<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Model\Completeness;
use Akeneo\Pim\Enrichment\Component\Product\Model\CompletenessInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Simple object version of the completeness generator.
 *
 * In this implementation, methods that generate missing completenesses do NOT save the products.
 * Complenesses are only added to the products in memory. The save of the products (and of the compltenesses)
 * should be handled by the a Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface service.
 *
 * @author    Julien Janvier (j.janvier@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CompletenessGenerator implements CompletenessGeneratorInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var CompletenessCalculatorInterface */
    protected $completenessCalculator;

    /** @var GetProductCompletenesses */
    private $getProductCompletenesses;

    /** @var IdentifiableObjectRepositoryInterface */
    private $channelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /**
     * TODO Remove the 3 last params (just temporary)
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CompletenessCalculatorInterface $completenessCalculator,
        GetProductCompletenesses $getProductCompletenesses,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->completenessCalculator = $completenessCalculator;
        $this->getProductCompletenesses = $getProductCompletenesses;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function generateMissingForProduct(ProductInterface $product)
    {
        $this->calculateProductCompletenesses($product);
    }

    /**
     * Calculates current product completenesses.
     * Completenesses are updated for the existing ones, others are added/removed.
     *
     * @param ProductInterface $product
     */
    protected function calculateProductCompletenesses(ProductInterface $product)
    {
        // TODO Remove getCompleteness
        $completenessCollection = $product->getCompletenesses();

        // TODO This block is to transform the "new" completeness to the "old way"
        $newCompletenessesV3 = $this->completenessCalculator->calculate($product);
        $newCompletenessesV2 = array_map(function ($newCompletenessTmp) use ($product) {
            $missingAttributeCodes = new ArrayCollection();
            foreach ($newCompletenessTmp->missingAttributeCodes() as $attributeCode) {
                $missingAttributeCodes->add($this->attributeRepository->findOneByIdentifier($attributeCode));
            }

            return new Completeness(
                $product,
                $this->channelRepository->findOneByIdentifier($newCompletenessTmp->channelCode()),
                $this->localeRepository->findOneByIdentifier($newCompletenessTmp->localeCode()),
                $missingAttributeCodes,
                count($newCompletenessTmp->missingAttributeCodes()),
                $newCompletenessTmp->requiredCount()
            );
        }, $newCompletenessesV3);

        $this->updateExistingCompletenesses($completenessCollection, $newCompletenessesV2);

        $completenessLocaleAndChannelCodes = [];
        foreach ($completenessCollection as $updatedCompleteness) {
            $completenessLocaleAndChannelCodes[] =
                $updatedCompleteness->getLocale()->getId().'/'.$updatedCompleteness->getChannel()->getId();
        }

        $newLocalesChannels = [];
        foreach ($newCompletenessesV2 as $newCompleteness) {
            $newLocalesChannels[] = $newCompleteness->getLocale()->getId().'/'.$newCompleteness->getChannel()->getId();
        }

        $localeAndChannelCodesOfCompletenessesToAdd = array_diff(
            $newLocalesChannels,
            $completenessLocaleAndChannelCodes
        );
        $this->addNewCompletenesses(
            $completenessCollection,
            $newCompletenessesV2,
            $localeAndChannelCodesOfCompletenessesToAdd
        );

        $localeAndChannelCodesOfCompletenessesToRemove = array_diff(
            $completenessLocaleAndChannelCodes,
            $newLocalesChannels
        );
        $this->removeOutdatedCompletenesses($completenessCollection, $localeAndChannelCodesOfCompletenessesToRemove);
    }

    /**
     * @param Collection              $completenessCollection
     * @param CompletenessInterface[] $newCompletenesses
     */
    private function updateExistingCompletenesses(Collection $completenessCollection, array $newCompletenesses)
    {
        foreach ($completenessCollection as $currentCompleteness) {
            foreach ($newCompletenesses as $newCompleteness) {
                if ($newCompleteness->getLocale()->getId() === $currentCompleteness->getLocale()->getId() &&
                    $newCompleteness->getChannel()->getId() === $currentCompleteness->getChannel()->getId()
                ) {
                    $currentCompleteness->setRatio($newCompleteness->getRatio());
                    $currentCompleteness->setMissingCount($newCompleteness->getMissingCount());
                    $currentCompleteness->setRequiredCount($newCompleteness->getRequiredCount());
                    $this->updateMissingAttributes(
                        $currentCompleteness->getMissingAttributes(),
                        $newCompleteness->getMissingAttributes()
                    );
                }
            }
        }
    }

    /**
     * @param Collection $currentMissingAttributes
     * @param Collection $newMissingAttributes
     */
    private function updateMissingAttributes(
        Collection $currentMissingAttributes,
        Collection $newMissingAttributes
    ): void {
        foreach ($currentMissingAttributes as $currentMissingAttribute) {
            if (!$newMissingAttributes->contains($currentMissingAttribute)) {
                $currentMissingAttributes->removeElement($currentMissingAttribute);
            }
        }

        foreach ($newMissingAttributes as $newMissingAttribute) {
            if (!$currentMissingAttributes->contains($newMissingAttribute)) {
                $currentMissingAttributes->add($newMissingAttribute);
            }
        }
    }

    /**
     * @param Collection              $completenessCollection
     * @param CompletenessInterface[] $newCompletenesses
     * @param string[]                $localeAndChannelCodesOfCompletenessesToAdd
     */
    private function addNewCompletenesses(
        Collection $completenessCollection,
        array $newCompletenesses,
        array $localeAndChannelCodesOfCompletenessesToAdd
    ) {
        foreach ($localeAndChannelCodesOfCompletenessesToAdd as $completenessLocaleAndChannel) {
            [$localeCode, $channelCode] = explode('/', $completenessLocaleAndChannel);

            foreach ($newCompletenesses as $newCompleteness) {
                if ($newCompleteness->getLocale()->getId() === (int) $localeCode
                    && $newCompleteness->getChannel()->getId() === (int) $channelCode
                ) {
                    $completenessCollection->add($newCompleteness);
                }
            }
        }
    }

    /**
     * @param Collection              $completenessCollection
     * @param CompletenessInterface[] $localeAndChannelCodesOfCompletenessesToRemove
     */
    private function removeOutdatedCompletenesses(
        Collection $completenessCollection,
        array $localeAndChannelCodesOfCompletenessesToRemove
    ) {
        foreach ($localeAndChannelCodesOfCompletenessesToRemove as $completenessLocaleAndChannel) {
            [$localeCode, $channelCode] = explode('/', $completenessLocaleAndChannel);

            foreach ($completenessCollection as $currentCompleteness) {
                if ($currentCompleteness->getLocale()->getId() === (int) $localeCode
                    && $currentCompleteness->getChannel()->getId() === (int) $channelCode
                ) {
                    $completenessCollection->removeElement($currentCompleteness);
                }
            }
        }
    }
}
