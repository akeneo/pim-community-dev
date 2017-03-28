<?php

namespace Pim\Component\Catalog\Completeness;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

class CompletenessGenerator implements CompletenessGeneratorInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var CompletenessCalculatorInterface */
    protected $calculator;

    /** @var SaverInterface */
    protected $saver;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param CompletenessCalculatorInterface     $calculator
     * @param SaverInterface                      $saver
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CompletenessCalculatorInterface $calculator,
        SaverInterface $saver
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->calculator = $calculator;
        $this->saver = $saver;
    }

    public function generateMissingForProduct(ProductInterface $product)
    {
        $this->calculator->calculate($product);
        $this->saver->save($product);
    }

    public function generateMissingForChannel(ChannelInterface $channel)
    {
        $products = $this->createProductQueryBuilder($channel)->execute();
        foreach ($products as $product) {
            $this->calculator->calculate($product);
            $this->saver->save($product);
        }
    }

    public function generateMissing()
    {
        $products = $this->createProductQueryBuilder()->execute();
        foreach ($products as $product) {
            $this->calculator->calculate($product);
            $this->saver->save($product);
        }
    }

    public function schedule(ProductInterface $product)
    {
        $product->getCompletenesses()->clear();
        $this->saver->save($product);
    }

    public function scheduleForFamily(FamilyInterface $family)
    {
        $familyFilter = ['field' => 'family', 'operator' => Operators::IN_LIST, 'value' => $family->getCode()];
        $products = $this->createProductQueryBuilder(null, null, $familyFilter)->execute();

        foreach ($products as $product) {
            $product->getCompletenesses()->clear();
            $this->saver->save($product);
        }
    }

    public function scheduleForChannelAndLocale(ChannelInterface $channel, LocaleInterface $locale)
    {
        $products = $this->createProductQueryBuilder($channel, $locale)->execute();

        foreach ($products as $product) {
            $product->getCompletenesses()->clear();
            $this->saver->save($product);
        }
    }

    /**
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     * @param array            $filters
     *
     * @return ProductQueryBuilderInterface
     */
    protected function createProductQueryBuilder(
        ChannelInterface $channel = null,
        LocaleInterface $locale = null,
        array $filters = []
    ) {
        $filters = array_merge(
            $filters,
            ['field' => 'completeness', 'operator' => Operators::IS_EMPTY, 'value' => null]
        );

        $options = [
            'filters' => $filters
        ];

        if (null !== $channel) {
            $options['default_scope'] = $channel->getCode();
        }
        if (null !== $locale) {
            $options['default_locale'] = $locale->getCode();
        }

        return $this->pqbFactory->create($options);
    }
}
