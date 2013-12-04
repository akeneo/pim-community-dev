<?php

namespace Pim\Bundle\ImportExportBundle\Reader\JSON;

use Pim\Bundle\ImportExportBundle\Reader\JSON\JSONReader;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager as EntityManager;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProductReader
 *
 * @author wn-s.rascar
 * @todo Treat file import
 */
class ProductReader extends JSONReader
{

    /** @var array Unique attribute value data grouped by attribute codes */
    protected $uniqueValues = array();

    /** @var array Media attribute codes */
    protected $mediaAttributes = array();

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, $uploadDirectory)
    {
        parent::__construct($uploadDirectory);
        $repository = $entityManager->getRepository('PimCatalogBundle:ProductAttribute');
        foreach ($repository->findUniqueAttributeCodes() as $code) {
            $this->uniqueValues[$code] = array();
        }
        $this->mediaAttributes = $repository->findMediaAttributeCodes();
    }

    public function read()
    {
        $data = parent::read();
        if (!is_array($data)) {
            return $data;
        }
        $this->assertValueUniqueness($data);

        return $this->transformMediaPathToAbsolute($data);
    }
    
    
    /**
     * @param array $data
     *
     * @throws InvalidItemException
     */
    protected function assertValueUniqueness(array $product)
    {

        foreach ($product as $key => $value ) {
            if (array_key_exists($key, $this->uniqueValues)) {
                if (in_array($value, $this->uniqueValues[$key])) {
                    throw new InvalidItemException(
                        sprintf(
                            'The "%s" attribute is unique, the value "%s" was already read ' .
                            'in this file',
                            $key,
                            $value
                        ),
                        $product
                    );
                }
                $this->uniqueValues[$key][] = $value;
            }
        }
        
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function transformMediaPathToAbsolute(array $product)
    {
 
        foreach ($product as $key => $value ) {
            if (in_array($key, $this->mediaAttributes)) {
                $product[$key] = $this->uploadDirectory. '/' . $value;
            }
        }
        
        return $product;
    }

}
