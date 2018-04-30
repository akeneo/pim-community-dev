<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\EventSubscriber;

use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\FamilyInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateAttributeRequirementsSubscriberIntegration extends TestCase
{
    public function testOnlyCreateAttributeRequirementForIdentifierAttribute()
    {
        $channelCode = 'new_cannel';
        $this->createChannel($channelCode);
        $families = $this->get('pim_catalog.repository.family')->findAll();

        /** @var FamilyInterface $family */
        foreach ($families as $family) {
            $requirements = $this->getRequirementsForChannel($family, $channelCode);
            $this->assertCount(1, $requirements);
            $this->assertSame(AttributeTypes::IDENTIFIER, $requirements[0]->getAttribute()->getType());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param FamilyInterface $family
     * @param string $channelCode
     *
     * @return AttributeRequirementInterface[]
     */
    private function getRequirementsForChannel(FamilyInterface $family, string $channelCode): array
    {
        $requirements = [];
        foreach ($family->getAttributeRequirements() as $requirement) {
            if ($requirement->getChannelCode() === $channelCode) {
                $requirements[] = $requirement;
            }
        }

        return $requirements;
    }

    /**
     * @param string $code
     */
    private function createChannel(string $code): void
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();
        $this->get('pim_catalog.updater.channel')->update(
            $channel,
            [
                'code' => $code,
                'currencies' => ['USD', 'EUR'],
                'locales' => ['en_US'],
                'category_tree' => 'master',
            ]
        );
        $this->get('pim_catalog.saver.channel')->save($channel);
    }
}
