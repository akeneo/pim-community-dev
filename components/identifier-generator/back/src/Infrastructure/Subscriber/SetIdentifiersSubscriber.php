<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Subscriber;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Product;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SetIdentifiersSubscriber implements EventSubscriberInterface
{
    /** @var IdentifierGenerator[]|null */
    private ?array $identifierGenerators = null;

    public function __construct(
        private IdentifierGeneratorRepository $identifierGeneratorRepository,
        private ValidatorInterface $validator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'setIdentifier'
            // TODO This should work with PRE_SAVE_ALL too.
        ];
    }

    public function setIdentifier(GenericEvent $event)
    {
        $object = $event->getSubject();
        if (!$object instanceof ProductInterface) return;

        $product = new Product($object->getIdentifier());
        foreach ($this->getIdentifierGenerators() as $identifierGenerator) {
            if ($identifierGenerator->match($product)) {
                try {
                    $this->setGeneratedIdentifier($identifierGenerator, $object);
                } catch (\Exception $e) {
                }
            }
        }
    }

    /**
     * @return IdentifierGenerator[]
     */
    private function getIdentifierGenerators(): array
    {
        if (null === $this->identifierGenerators) {
            $this->identifierGenerators = $this->identifierGeneratorRepository->getAll();
        }

        return $this->identifierGenerators;
    }

    private function setGeneratedIdentifier(
        IdentifierGenerator $identifierGenerator,
        ProductInterface $product
    ): void {
        $newIdentifier = $identifierGenerator->generate();
        $value = ScalarValue::value($identifierGenerator->target()->asString(), $newIdentifier);
        $product->addValue($value);
        // TODO This seems not working as I don't have an issue with duplicate identifiers.
        $violations = $this->validator->validate($product);
        if (count($violations) > 0) {
            $product->setIdentifier(null);
            // TODO Better exception
            throw new \Exception();
        }
    }
}
