<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Tag processor
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TagProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /** @var ValidatorInterface */
    protected $validator;

    /** @var string */
    protected $tagClass;

    /**
     * @param string $tagClass
     */
    public function __construct(ValidatorInterface $validator, $tagClass)
    {
        $this->validator = $validator;
        $this->tagClass  = $tagClass;
    }

    /**
     * {@inheritdoc}
     */
    public function process($items)
    {
        $tags = [];
        foreach ($items as $item) {
            $tag = new $this->tagClass();
            $tag->setCode($item['code']);
            $violations = $this->validator->validate($tag);

            $this->manageViolations($violations, $item);

            $tags[] = $tag;
        }

        return $tags;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param array                            $item
     *
     * @throws InvalidItemException
     */
    protected function manageViolations(ConstraintViolationListInterface $violations, array $item)
    {
        if ($violations->count() > 0) {
            $violationsMessage = '';
            foreach ($violations as $violation) {
                $violationsMessage .= PHP_EOL . $violation->getMessage();
            }

            $errorMessage = sprintf('Tag with code "%s" can\'t be process :%s', $item['code'], $violationsMessage);
            throw new InvalidItemException($errorMessage, $item);
        }
    }
}
