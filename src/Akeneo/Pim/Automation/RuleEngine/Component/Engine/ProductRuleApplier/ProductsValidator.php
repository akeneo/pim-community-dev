<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SkippedSubjectRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validates products when apply a rule
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductsValidator
{
    /** @var ValidatorInterface */
    protected $productValidator;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /**
     * @param ValidatorInterface       $productValidator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ObjectDetacherInterface  $objectDetacher
     */
    public function __construct(
        ValidatorInterface $productValidator,
        EventDispatcherInterface $eventDispatcher,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->productValidator = $productValidator;
        $this->eventDispatcher = $eventDispatcher;
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * @param RuleInterface      $rule     Applied rule
     * @param ProductInterface[] $products Products to validate
     *
     * @return ProductInterface[] Valid products
     */
    public function validate(RuleInterface $rule, array $products)
    {
        $invalidProductIdx = [];
        foreach ($products as $index => $product) {
            $violations = $this->productValidator->validate($product);
            if ($violations->count() > 0) {
                $invalidProductIdx[] = $index;
                $this->objectDetacher->detach($product);
                $reasons = [];
                foreach ($violations as $violation) {
                    $propertyPath = $violation->getPropertyPath();
                    $reasons[] = sprintf(
                        '%s%s',
                        $propertyPath === '' ? '' : $propertyPath . ': ',
                        $violation->getMessage()
                    );
                }
                $this->eventDispatcher->dispatch(
                    new SkippedSubjectRuleEvent($rule, $product, $reasons),
                    RuleEvents::SKIP
                );
            }
        }

        foreach ($invalidProductIdx as $index) {
            unset($products[$index]);
        }

        return $products;
    }
}
