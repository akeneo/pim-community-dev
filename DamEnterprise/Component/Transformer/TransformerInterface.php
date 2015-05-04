<?php
namespace DamEnterprise\Component\Transformer;

interface TransformerInterface
{
    public function transform(\SplFileInfo $file, array $rawTransformations);
}
