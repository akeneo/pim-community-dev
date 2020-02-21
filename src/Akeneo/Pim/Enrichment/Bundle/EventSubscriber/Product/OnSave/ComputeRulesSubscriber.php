<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyCopierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class ComputeRulesSubscriber implements EventSubscriberInterface
{
    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var PropertySetterInterface */
    private $propertySetter;

    /** @var PropertyAdderInterface */
    private $propertyAdder;

    /** @var PropertyCopierInterface */
    private $propertyCopier;

    /** @var Connection */
    private $connection;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        PropertySetterInterface $propertySetter,
        PropertyAdderInterface $propertyAdder,
        PropertyCopierInterface $propertyCopier,
        Connection $connection
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->propertySetter = $propertySetter;
        $this->propertyAdder = $propertyAdder;
        $this->propertyCopier = $propertyCopier;
        $this->connection = $connection;
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

        $membefore = memory_get_usage();
        $timebefore = microtime(true);
        $rules = $this->getRules();


//        $rules = $this->ruleDefinitionRepository->findAll();
        gc_collect_cycles();
        $memafter = memory_get_usage();
        $timeafter = microtime(true);

        foreach ($products as $product) {
            /** @var $product ProductInterface */
            $categoryCodes = $product->getCategoryCodes();
            $categoryCodes = array_flip($categoryCodes);
            foreach ($rules as $rule) {
                /** @var $rule RuleDefinitionInterface */
//                $content = $rule->getContent();
                $content = $rule;
                $conditions = $content['conditions'];
//                var_dump('Match ' . json_encode($conditions) . ' ? ');
                if ($this->matchConditions($conditions, $product, $categoryCodes)) {
//                    var_dump('oui');
                    $actions = $content['actions'];
                    $this->executeActions($actions, $product);
                } else {
//                    var_dump('non');
                }
            }
        }

        $timeafterafter = microtime(true);


        var_dump([
            'membefore' => $membefore,
            'memafter' => $memafter,
            'timebefore' => $timebefore,
            'timeafter' => $timeafter,
            'timeafterafter' => $timeafterafter,
            'diff1' => ($membefore - $memafter) . ' octet',
            'diff2' => ($timeafter - $timebefore),
            'diff3' => ($timeafterafter - $timeafter),
        ]);
    }

    private function matchConditions(array $conditions, ProductInterface $product, $categoryCodes): bool
    {
        foreach ($conditions as $condition) {
            $field = $condition['field'];
            $operator = $condition['operator'];
            $value = isset($condition['value']) ? $condition['value'] : null;
            $scope = isset($condition['scope']) ? $condition['scope'] : null;
            $locale = isset($condition['locale']) ? $condition['locale'] : null;
            if ($field === 'family') {
                if ($operator === Operators::IN_LIST) {
                    if (null !== $product->getFamily() && !in_array($product->getFamily()->getCode(), $value)) {
//                        var_dump(json_encode('family IN'));
                        return false;
                    }
                } else if ($operator === Operators::EQUALS) {
                    if (!(null === $product->getFamily() || $product->getFamily()->getCode() !== $value)) {
                        return false;
                    }
                } else {
                    var_dump(sprintf('Operator %s not found for family', $operator));
                }
            } else if ($field === 'categories') {
                if (Operators::IN_LIST === $operator) {
                    if (empty(array_intersect_key($categoryCodes, array_flip($value)))) {
                        return false;
                    }
                } elseif (Operators::UNCLASSIFIED === $operator) {
                    if (!empty($categoryCodes)) {
                        return false;
                    }
                } elseif (Operators::NOT_IN_LIST === $operator) {
                    if (!empty(array_intersect_key($categoryCodes, array_flip($value)))) {
                        return false;
                    }
                } elseif (Operators::IN_LIST_OR_UNCLASSIFIED === $operator) {
                    if (!empty($categoryCodes) && empty(array_intersect_key($categoryCodes, array_flip($value)))) {
                        return false;
                    }
                } else {
                    var_dump(sprintf('Operator %s not found for categories', $operator));
                }
            } else {
                $productValue = $product->getValue($field, $locale, $scope);
                if ($operator === Operators::IS_EMPTY) {
                    if ($productValue !== null && $productValue->getData() !== '' && $productValue->getData() !== []) {
                        return false;
                    }
                } else if ($operator === Operators::CONTAINS) {
                    /** @var $productValue ScalarValue */
                    if (null === $productValue || strpos($productValue->getData(), $value) === -1) {
                        return false;
                    }
                } else if ($operator === Operators::NOT_IN_LIST) {
                    /** @var $productValue OptionValue */
                    if (null !== $productValue && in_array($productValue->getData(), $value)) {
                        return false;
                    }
                } else if ($operator === Operators::NOT_EQUAL) {
                    if (($productValue ? $productValue->getData() : null) === $value) {
//                        var_dump(json_encode('!='));
                        return false;
                    }
                } else if ($operator === Operators::IS_NOT_EMPTY) {
                    if ($productValue === null || $productValue->getData() === '' || $productValue->getData() === []) {
                        return false;
                    }
                } else if ($operator === Operators::IN_LIST) {
                    if (null === $productValue) {
                        return false;
                    }
                } else if ($operator === Operators::EQUALS) {
                    if (!(($productValue === null && $value === '') || ($productValue !== null && $productValue->getData() === $value))) {
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
                $this->propertyCopier->copyData($product, $product, $action['from_field'], $action['to_field'], [
                    'from_locale' => isset($action['from_locale']) ? $action['from_locale'] : null,
                    'from_scope' => isset($action['from_scope']) ? $action['from_scope'] : null,
                    'to_locale' => isset($action['to_locale']) ? $action['to_locale'] : null,
                    'to_scope' => isset($action['to_scope']) ? $action['to_scope'] : null
                ]);
            } elseif ($action['type'] === 'set') {
                $this->propertySetter->setData($product, $action['field'], $action['value'], [
                    'locale' => isset($action['locale']) ? $action['locale'] : null,
                    'scope' => isset($action['scope']) ? $action['scope'] : null
                ]);
            } else if ($action['type'] === 'add') {
                $this->propertyAdder->addData($product, $action['field'], $action['items'], [
                    'locale' => isset($action['locale']) ? $action['locale'] : null,
                    'scope' => isset($action['scope']) ? $action['scope'] : null
                ]);
            } else {
                var_dump(sprintf('Operation type %s not found', $action['type']));
            }
        }
    }

    private function getRules()
    {
        if (file_exists('./rules.yolo')) {
            return unserialize(file_get_contents('./rules.yolo'));
        }

        $results = $this->connection->executeQuery(
            'SELECT content FROM akeneo_rule_engine_rule_definition'
        );
        $rules = [];
        foreach($results as $line) {
            $rules[] = json_decode($line['content'], true);
        }

        file_put_contents('./rules.yolo', serialize($rules));

        return $rules;
    }
}
