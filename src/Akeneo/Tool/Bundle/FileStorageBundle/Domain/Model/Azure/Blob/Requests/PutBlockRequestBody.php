<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\Requests;

final class PutBlockRequestBody
{
    /**
     * @param Block[] $blocks
     */
    public function __construct(
        public array $blocks,
    ) {
    }

    public function toXml(): \SimpleXMLElement
    {
        $xml = new \SimpleXMLElement("<BlockList></BlockList>");

        foreach ($this->blocks as $block) {
            $xml->addChild($block->type->value, base64_encode($block->id));
        }

        return $xml;
    }
}
