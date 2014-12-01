<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Component\Resource\Model\RemoverInterface;
use Pim\Component\Resource\Model\SaverInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResourceInterface;

/**
 * Class RuleLinkedResourceManager
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleLinkedResourceManager implements SaverInterface, RemoverInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param EntityManager       $entityManager
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(EntityManager $entityManager, AttributeRepository $attributeRepository)
    {
        $this->entityManager       = $entityManager;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        if (!$object instanceof RuleLinkedResourceInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResourceInterface,
                    "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->entityManager->persist($object);

        if (true === $options['flush']) {
            $this->entityManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        if (!$object instanceof RuleLinkedResourceInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResourceInterface,
                    "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $this->entityManager->remove($object);
    }

    /**
     * Returns all impacted attributes
     *
     * @param array $actions
     *
     * @return array
     */
    public function getImpactedAttributes(array $actions)
    {
        $fields = [];
        foreach ($actions as $action) {
            if (array_key_exists('field', $action)) {
                $fields[] = $action['field'];
            }
            if (array_key_exists('to_field', $action)) {
                $fields[] = $action['to_field'];
            }
        }

        $impactedAttributes = [];
        foreach ($fields as $field) {
            $impactedAttributes[] = $this->attributeRepository->findByReference($field);
        }

        $impactedAttributes = array_unique($impactedAttributes);
        $impactedAttributes = array_filter($impactedAttributes);

        return $impactedAttributes;
    }
}
