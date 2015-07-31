<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use Pim\Bundle\TransformBundle\Exception\PropertyTransformerException;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Media attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaTransformer implements PropertyTransformerInterface, EntityUpdaterInterface
{
    /** @var RawFileStorerInterface */
    protected $storer;

    /**
     * @param RawFileStorerInterface $storer
     */
    public function __construct(RawFileStorerInterface $storer)
    {
        $this->storer = $storer;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        $value = trim($value);

        if (empty($value) || is_dir($value)) {
            return;
        }

        try {
            $rawFile = new File($value);
            $file = $this->storer->store($rawFile, 'storage');
        } catch (FileNotFoundException $e) {
            throw new PropertyTransformerException('File not found: "%value%"', ['%value%' => $value]);
        } catch (FileTransferException $e) {
            throw new PropertyTransformerException('Impossible to transfer the file "%value%"', ['%value%' => $value]);
        } catch (\Exception $e) {
            throw new PropertyTransformerException(
                'An error occurred during the process of the file "%value%"',
                ['%value%' => $value]
            );
        }

        return $file;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($object, ColumnInfoInterface $columnInfo, $data, array $options = array())
    {
        if (null === $data) {
            return;
        }

        $object->setMedia($data);
    }
}
