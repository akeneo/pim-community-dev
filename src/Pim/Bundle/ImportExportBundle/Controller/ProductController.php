<?php

namespace Pim\Bundle\ImportExportBundle\Controller;


use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\SearchableRepository;
use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductController
{
    /** @var SearchableRepository  */
    protected $productRepository;
    
    public function __construct(SearchableRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param Request $request
     * 
     * @return JsonResponse
     */
    public function identifierListAction(Request $request)
    {
        $options = $request->get('options');
        
        if (!isset($options['limit'])) {
            $options['limit'] = 20;
        }  
        
        if (!isset($options['page'])) {
            $options['page'] = 1;
        }
        
        $identifiers = $this->productRepository->findBySearch(
            $request->get('search'),
            $request->get('options')
        );
        
        return new JsonResponse($identifiers);
    }
}
