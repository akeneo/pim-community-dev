<?php

namespace Akeneo\Platform\TailoredExport\Infrastructure\Normalizer\Standard;

use Akeneo\Channel\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\ColumnCollectionHydrator;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform a job instance entity into a array with permission
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    protected NormalizerInterface $normalizer;
    protected GetAllViewableLocalesForUserInterface $getAllViewableLocales;
    protected GetViewableAttributeCodesForUserInterface $getViewableAttributes;
    private TokenStorageInterface $tokenStorage;
    private GetAttributes $getAttributes;
    private GetAssociationTypesInterface $getAssociationTypes;
    private ColumnCollectionHydrator $columnCollectionHydrator;

    /**
     * @param NormalizerInterface                   $normalizer
     * @param GetAllViewableLocalesForUserInterface $getAllViewableLocales
     * @param GetViewableAttributeCodesForUserInterface $getViewableAttributes
     * @param TokenStorageInterface $tokenStorage
     * @param GetAttributes $getAttributes
     * @param GetAssociationTypesInterface $getAssociationTypes
     * @param ColumnCollectionHydrator $columnCollectionHydrator
     */
    public function __construct(
        NormalizerInterface $normalizer,
        GetAllViewableLocalesForUserInterface $getAllViewableLocales,
        GetViewableAttributeCodesForUserInterface $getViewableAttributes,
        TokenStorageInterface $tokenStorage,
        GetAttributes $getAttributes,
        GetAssociationTypesInterface $getAssociationTypes,
        ColumnCollectionHydrator $columnCollectionHydrator
    ) {
        $this->normalizer = $normalizer;
        $this->getAllViewableLocales = $getAllViewableLocales;
        $this->getViewableAttributes = $getViewableAttributes;
        $this->tokenStorage = $tokenStorage;
        $this->getAttributes = $getAttributes;
        $this->getAssociationTypes = $getAssociationTypes;
        $this->columnCollectionHydrator = $columnCollectionHydrator;
    }

    /**
     * {@inheritdoc}
     *
     * @param JobInstance $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedJobInstance = $this->normalizer->normalize($object, $format, $context);

        $normalizedJobInstance['permissions'] = array_merge(
            $normalizedJobInstance['permissions'] ?? [],
            [
                'edit_tailored_export' => $this->canEditTailoredExport($object)
            ]
        );

        return $normalizedJobInstance;
    }

    /** this could be extracted to a dedicated service */
    private function canEditTailoredExport(JobInstance $jobInstance): bool
    {
        $userId = $this->getUserId();
        if (null === $this->getUserId()) {
            return false;
        }

        $columns = $jobInstance->getRawParameters()['columns'];
        [
            [
                [
                    'code'
                    'selection' => [
                        'locale' => 'en_US'
                    ]
                ]
            ]
        ]

        // $columns = $jobInstance->getRawParameters()['columns'];
        // $indexedAttributes = $this->getIndexedAttributes($columns);
        // $indexedAssociationTypes = $this->getIndexedAssociationTypes($columns);

        // $columnCollection = $this->columnCollectionHydrator->hydrate($columns, $indexedAttributes, $indexedAssociationTypes);

        // $jobAttributeCodes = $columnCollection->getAllAttributeCodes();
        // $viewableAttributes = $this->getViewableAttributes->forAttributeCodes($jobAttributeCodes, $userId);
        // $canEditAllAttributes = array_intersect($viewableAttributes, $jobAttributeCodes) === $jobAttributeCodes;

        // $jobLocaleCodes = $columnCollection->getAllLocaleCodes();
        // $viewableLocales = $this->getAllViewableLocales->fetchAll($userId);
        // $canEditAllLocales = array_intersect($jobLocaleCodes, $viewableLocales) === $viewableLocales;

        // return $canEditAllAttributes && $canEditAllLocales;
    }

    private function getIndexedAttributes(array $columns): array
    {
        $attributeCodes = [];
        foreach ($columns as $column) {
            foreach ($column['sources'] as $source) {
                if (AttributeSource::TYPE === $source['type']) {
                    $attributeCodes[] = $source['code'];
                }
            }
        }

        return array_filter($this->getAttributes->forCodes(array_unique($attributeCodes)));
    }

    private function getIndexedAssociationTypes(array $columns): array
    {
        $associationTypeCodes = [];
        foreach ($columns as $column) {
            foreach ($column['sources'] as $source) {
                if (AssociationTypeSource::TYPE === $source['type']) {
                    $associationTypeCodes[] = $source['code'];
                }
            }
        }

        $indexedAssociationTypes = $this->getAssociationTypes->forCodes(array_unique($associationTypeCodes));

        return array_filter($indexedAssociationTypes);
    }

    private function getUserId(): ?int
    {
        if ($this->tokenStorage->getToken() && $user = $this->tokenStorage->getToken()->getUser()) {
            return $user->getId();
        }

        return null;
    }

    /**
     * If we are in EE, we override the normalizer with the wone with permissions
     */
    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->normalizer->supportsNormalization($data, $format); // add filter on job type (tailored export)
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->normalizer instanceof CacheableSupportsMethodInterface
            && $this->normalizer->hasCacheableSupportsMethod();
    }
}
