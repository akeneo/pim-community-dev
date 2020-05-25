import {useEffect, useState} from 'react';
import {useUserContext, useRoute} from '@akeneo-pim-community/legacy-bridge';
import {AssociationIdentifiers, Product, ProductType} from '../models';

const productPromises = {};
const productFetcher = async (route: string, identifiers: AssociationIdentifiers): Promise<Product[]> => {
  const response = await fetch(route, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'fetch',
    },
    body: JSON.stringify(identifiers),
  });

  return (await response.json()).items;
};

const useProducts = (identifiers: AssociationIdentifiers): Product[] | null => {
  const [products, setProducts] = useState<Product[] | null>(null);
  const userContext = useUserContext();
  const url = useRoute('pim_enrich_product_and_product_model_by_identifiers_rest_list', {
    channel: userContext.get('catalogScope'),
    locale: userContext.get('catalogLocale'),
  });

  const identifiersToFetch: AssociationIdentifiers =
    null === products
      ? identifiers
      : {
          products: identifiers.products.filter(
            identifier =>
              !products.some(
                product => product.identifier === identifier && product.document_type === ProductType.Product
              )
          ),
          product_models: identifiers.product_models.filter(
            identifier =>
              !products.some(
                product => product.identifier === identifier && product.document_type === ProductType.ProductModel
              )
          ),
        };

  useEffect(() => {
    (async () => {
      if (0 === identifiersToFetch.product_models.length && 0 === identifiersToFetch.products.length) return;
      debugger;
      const newProducts = await productFetcher(url, identifiersToFetch);

      setProducts(currentProducts => {
        return null === currentProducts ? newProducts : [...newProducts, ...currentProducts];
      });
    })();
  }, [JSON.stringify(identifiersToFetch), url]);

  return products;
};

export {useProducts};
