<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\Channel;

interface FindActivatedLocalesPerChannelsInterface
{
    /**
     * Returns a list of channels with associated activated locales:
     * [
     *      'ecommerce' => ['fr_FR', 'en_US']
     *      'mobile' => ['en_US']
     * ]
     */
    public function findAll(): array;
}
