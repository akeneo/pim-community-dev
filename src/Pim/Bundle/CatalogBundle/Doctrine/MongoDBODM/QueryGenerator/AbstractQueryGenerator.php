<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
* Abstract query generator
*/
class AbstractQueryGenerator implements NormalizedDataQueryGenerator
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $channelClass;

    /** @var string */
    protected $localeClass;

    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $entityClass;

    /**
     * @var string
     */
    protected $field;

    /**
     * @param ManagerRegistry     $registry
     * @param string              $channelClass
     * @param string              $localeClass
     * @param string              $attributeClass
     */
    public function __construct(
        ManagerRegistry $registry,
        $channelClass,
        $localeClass,
        $attributeClass,
        $entityClass,
        $field = ''
    ) {
        $this->registry       = $registry;
        $this->channelClass   = $channelClass;
        $this->localeClass    = $localeClass;
        $this->attributeClass = $attributeClass;
        $this->entityClass    = $entityClass;
        $this->field          = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity, $field)
    {
        return $entity instanceof $this->entityClass && $field === $this->field;
    }

    /**
     * Get scopable attributes
     *
     * @return array
     */
    protected function getScopableAttributes()
    {
        $attributeManager    = $this->registry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $attributes = $attributeRepository->findBy(['scopable' => true]);

        return $attributes;
    }

    /**
     * Get scopable attributes
     *
     * @return array
     */
    protected function getLocalizableAttributes()
    {
        $attributeManager    = $this->registry->getManagerForClass($this->attributeClass);
        $attributeRepository = $attributeManager->getRepository($this->attributeClass);

        $attributes = $attributeRepository->findBy(['localizable' => true]);

        return $attributes;
    }

    /**
     * Get possible attribute codes
     *
     * @return array
     */
    protected function getPossibleAttributeCodes(AbstractAttribute $attribute, $prefix = '')
    {
        $localeSuffixes  = $this->getLocaleSuffixes($attribute);
        $channelSuffixes = $this->getChannelSuffixes($attribute);

        $attributeCodes = [($prefix !== '' ? $prefix : '') .$attribute->getCode()];

        $attributeCodes = $this->appendSuffixes($attributeCodes, $localeSuffixes);
        $attributeCodes = $this->appendSuffixes($attributeCodes, $channelSuffixes);

        return $attributeCodes;
    }

    /**
     * Append given suffixes to codes
     * @param  array $codes
     * @param  array $suffixes
     *
     * @return array
     */
    protected function appendSuffixes($codes, $suffixes) {
        $result = $codes;

        if (count($suffixes) > 0) {
            $result = [];

            foreach ($codes as $key => $code) {
                foreach ($suffixes as $suffix) {
                    $result[] = $code . $suffix;
                }
            }
        }

        return $result;
    }

    /**
     * Get all locale prefixes
     *
     * @return array
     */
    protected function getLocaleSuffixes(AbstractAttribute $attribute)
    {
        $localeSuffixes = [];

        if ($attribute->isLocalizable()) {
            foreach ($this->getActivatedLocales() as $locale) {
                $localeSuffixes[] = sprintf('-%s', $locale->getCode());
            }
        }

        return $localeSuffixes;
    }

    /**
     * Get all channel prefixes
     *
     * @return array
     */
    protected function getChannelSuffixes(AbstractAttribute $attribute)
    {
        $channelSuffixes = [];

        if ($attribute->isScopable()) {
            $objectManager      = $this->registry->getManagerForClass($this->channelClass);
            $channelRepository  = $objectManager->getRepository($this->channelClass);

            foreach ($channelRepository->findAll() as $channel) {
                $channelSuffixes[] = sprintf('-%s', $channel->getCode());
            }
        }

        return $channelSuffixes;
    }

    /**
     * Get all activated locale
     *
     * @return array
     */
    protected function getActivatedLocales()
    {
        $objectManager     = $this->registry->getManagerForClass($this->localeClass);
            $localeRepository  = $objectManager->getRepository($this->localeClass);

        return $localeRepository->getActivatedLocales();
    }
}