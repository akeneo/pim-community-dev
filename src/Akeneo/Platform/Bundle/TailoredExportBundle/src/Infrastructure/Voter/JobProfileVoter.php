<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Voter;

use Akeneo\Channel\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\SourceInterface;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\ColumnCollectionHydrator;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Job profile voter, allows to know if a job profile can be executed or edited by
 * a user depending on the attributes and locales in the sources of the job
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class JobProfileVoter extends Voter implements VoterInterface
{
    protected GetAllViewableLocalesForUserInterface $getAllViewableLocales;
    protected GetViewableAttributeCodesForUserInterface $getViewableAttributes;
    private TokenStorageInterface $tokenStorage;
    private GetAttributes $getAttributes;
    private GetAssociationTypesInterface $getAssociationTypes;
    private ColumnCollectionHydrator $columnCollectionHydrator;

    /**
     * @param GetAllViewableLocalesForUserInterface $getAllViewableLocales
     * @param GetViewableAttributeCodesForUserInterface $getViewableAttributes
     * @param TokenStorageInterface $tokenStorage
     * @param GetAttributes $getAttributes
     * @param GetAssociationTypesInterface $getAssociationTypes
     * @param ColumnCollectionHydrator $columnCollectionHydrator
     */
    public function __construct(
        GetAllViewableLocalesForUserInterface $getAllViewableLocales,
        GetViewableAttributeCodesForUserInterface $getViewableAttributes,
        TokenStorageInterface $tokenStorage,
        GetAttributes $getAttributes,
        GetAssociationTypesInterface $getAssociationTypes,
        ColumnCollectionHydrator $columnCollectionHydrator
    ) {
        $this->getAllViewableLocales = $getAllViewableLocales;
        $this->getViewableAttributes = $getViewableAttributes;
        $this->tokenStorage = $tokenStorage;
        $this->getAttributes = $getAttributes;
        $this->getAssociationTypes = $getAssociationTypes;
        $this->columnCollectionHydrator = $columnCollectionHydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if (!$object instanceof JobInstance) {
            return $result;
        }

        if ('xlsx_tailored_product_export' !== $object->getJobName()) return;

        foreach ($attributes as $attribute) {
            if ($this->supports($attribute, $object)) {
                $result = VoterInterface::ACCESS_DENIED;

                if ($this->voteOnAttribute($attribute, $object, $token)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [Attributes::EXECUTE, Attributes::EDIT]) &&
            $subject instanceof JobInstance && 'xlsx_tailored_product_export' === $subject->getJobName();
    }

    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        return $this->canEditTailoredExport($object, $token);
    }

    private function canEditTailoredExport(JobInstance $jobInstance, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            throw new \InvalidArgumentException('Invalid user type');
        }

        $userId = $user->getId();
        if (null === $user->getId()) {
            return false;
        }

        if (!isset($jobInstance->getRawParameters()['columns'])) return false;

        $columns = $jobInstance->getRawParameters()['columns'];

        $indexedAttributes = $this->getIndexedAttributes($columns);
        $indexedAssociationTypes = $this->getIndexedAssociationTypes($columns);

        $columnCollection = $this->columnCollectionHydrator->hydrate($columns, $indexedAttributes, $indexedAssociationTypes);

        $attributeSources = array_filter(iterator_to_array($columnCollection->getAllSources()->getIterator()), static fn (SourceInterface $source) => $source instanceof AttributeSource);

        $jobAttributeCodes = array_unique(array_map(static fn (AttributeSource $source) => $source->getCode(), $attributeSources));
        $viewableAttributes = $this->getViewableAttributes->forAttributeCodes($jobAttributeCodes, $userId);
        $canEditAllAttributes = array_intersect($viewableAttributes, $jobAttributeCodes) === $jobAttributeCodes;

        $jobLocaleCodes = array_unique(array_filter(array_map(static fn (AttributeSource $source) => $source->getLocale(), $attributeSources)));
        $viewableLocales = $this->getAllViewableLocales->fetchAll($userId);
        $canEditAllLocales = array_intersect($jobLocaleCodes, $viewableLocales) === $viewableLocales;

        return $canEditAllAttributes && $canEditAllLocales;
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
}
