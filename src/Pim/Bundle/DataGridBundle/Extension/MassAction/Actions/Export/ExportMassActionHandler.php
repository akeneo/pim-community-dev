<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Export;

use Symfony\Component\Serializer\SerializerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMediatorInterface;

/**
 * Export action handler
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportMassActionHandler implements MassActionHandlerInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(MassActionMediatorInterface $mediator)
    {
        $qb = $mediator->getResults()->getSource();

        $results = $qb->getQuery()->execute();

        $context = array(
            'withHeader'    => true,
            'heterogeneous' => true
        );

        $entities = array_map(
            function ($result) {
                return $result[0];
            },
            $results
        );

        return $this->serializer->serialize($entities, 'csv', $context);
    }
}
