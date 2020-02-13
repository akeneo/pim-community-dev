<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class ComputeRulesSubscriber implements EventSubscriberInterface
{
    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var PropertySetterInterface */
    private $propertySetter;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        PropertySetterInterface $propertySetter
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->propertySetter = $propertySetter;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => [['handleSingleProduct', 100]],
            StorageEvents::PRE_SAVE_ALL => [['handleMultipleProducts', 100]],
        ];
    }

    public function handleSingleProduct(GenericEvent $event): void
    {
        if (false === ($event->getArguments()['unitary'] ?? false)) {
            return;
        }
        $this->handleMultipleProducts(new GenericEvent([$event->getSubject()], $event->getArguments()));
    }

    public function handleMultipleProducts(GenericEvent $event): void
    {
        $products = $event->getSubject();
        if (!is_array($products)) {
            return;
        }

        $products = array_filter(
            $products,
            function ($product): bool {
                return $product instanceof ProductInterface
                    // TODO TIP-987 Remove this when decoupling PublishedProduct from Enrichment
                    && get_class($product) !== 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct';
            }
        );

        $productIdentifiers = array_map(
            function (ProductInterface $product): string {
                return $product->getIdentifier();
            },
            $products
        );

        if (empty($productIdentifiers)) {
            return;
        }

        // TODO Do something with rules
        $rules = $this->ruleDefinitionRepository->findAll();
        foreach ($products as $product) {
            /** @var $product ProductInterface */
            foreach ($rules as $rule) {
                /** @var $rule RuleDefinitionInterface */
                $content = $rule->getContent();
                $conditions = $content['conditions'];
//                var_dump('Match ' . json_encode($conditions) . ' ? ');
                if ($this->matchConditions($conditions, $product)) {
//                    var_dump('oui');
                    $actions = $content['actions'];
                    $this->executeActions($actions, $product);
                } else {
//                    var_dump('non');
                }
            }
        }
    }

    private function matchConditions(array $conditions, ProductInterface $product): bool
    {
        foreach ($conditions as $condition) {
            $field = $condition['field'];
            $operator = $condition['operator'];
            $value = $condition['value'];
            $scope = isset($condition['scope']) ? $condition['scope'] : null;
            $locale = isset($condition['locale']) ? $condition['locale'] : null;
            if ($field === 'family') {
                if ($operator === 'IN') {
                    if (!in_array($product->getFamily()->getCode(), $value)) {
                        var_dump(json_encode('family IN'));
                        return false;
                    }
                } else {
                    var_dump(sprintf('Operator %s not found for family', $operator));
                }
            } else {
                $productValue = $product->getValue($field, $locale, $scope);
                if ($operator === 'EMPTY') {
                    if ($productValue !== null && $productValue->getData() !== '' && $productValue->getData() !== []) {
                        return false;
                    }
                } else if ($operator === 'CONTAINS') {
                    /** @var $productValue ScalarValue */
                    if (null === $productValue || strpos($productValue->getData(), $value) === -1) {
                        var_dump(json_encode('CONTAINS'));
                        return false;
                    }
                } else if ($operator === 'NOT IN') {
                    /** @var $productValue OptionValue */
                    if (null !== $productValue && in_array($productValue->getData(), $value)) {
                        return false;
                    }
                } else if ($operator === '!=') {
                    if (($productValue ? $productValue->getData() : null) === $value) {
//                        var_dump(json_encode('!='));
                        return false;
                    }
                } else if ($operator === 'NOT EMPTY') {
                    if ($productValue === null || $productValue->getData() === '' || $productValue->getData() === []) {
                        return false;
                    }
                } else if ($operator === 'IN') {
                    if (null === $productValue) {
                        return false;
                    }
                } else {
                    var_dump(sprintf('Operator %s not found for %s', $operator, $field));
                }
            }
        }

        return true;
    }

    private function executeActions(array $actions, ProductInterface $product)
    {
        foreach ($actions as $action) {
            if ($action['type'] === 'copy') {
                $fromField = $action['from_field'];
                $toField = $action['to_field'];
                $values = $product->getValues();
                $valueToCopy = $values->getByCodes(
                    $fromField,
                    isset($action['from_scope']) ? $action['from_scope'] : null,
                    isset($action['from_locale']) ? $action['from_locale'] : null
                );
                $this->propertySetter->setData($product, $toField, $valueToCopy ? $valueToCopy->getData() : null, [
                    'locale' => isset($action['to_locale']) ? $action['to_locale'] : null,
                    'scope' => isset($action['to_scope']) ? $action['to_scope'] : null
                ]);
            } elseif ($action['type'] === 'set') {
                $this->propertySetter->setData($product, $action['field'], $action['value'], [
                    'locale' => isset($action['locale']) ? $action['locale'] : null,
                    'scope' => isset($action['scope']) ? $action['scope'] : null
                ]);
            } else {
                var_dump(sprintf('Operation type %s not found', $action['type']));
            }
        }
    }
}
