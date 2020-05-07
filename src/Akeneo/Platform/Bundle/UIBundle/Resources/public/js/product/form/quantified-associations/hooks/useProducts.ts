import {useEffect, useState} from 'react';
import {useUserContext, useRoute} from '@akeneo-pim-community/legacy-bridge';
import {AssociationIdentifiers, Product} from '../models';

const productFetcher = async (route: string, identifiers: AssociationIdentifiers): Promise<Product[]> => {
  const response = await fetch(route, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(identifiers),
  });

  return (await response.json()).items;
};

const useProducts = (identifiers: AssociationIdentifiers): Product[] | null => {
  const [products, setProducts] = useState<Product[] | null>(null);
  const userContext = useUserContext();
  const route = useRoute('pim_enrich_product_and_product_model_by_identifiers_rest_list', {
    channel: userContext.get('catalogScope'),
    locale: userContext.get('catalogLocale'),
  });

  useEffect(() => {
    (async () => {
      setProducts(await productFetcher(route, identifiers));
    })();
  }, [JSON.stringify(identifiers), route]);

  return products;
};

export {useProducts};
