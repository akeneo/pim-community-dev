import {useEffect, useState} from 'react';
import {useUserContext, useRoute} from '@akeneo-pim-community/legacy-bridge';
import {AssociationIdentifiers, Product, ProductsType, getProductsType} from '../models';
import {Row} from '../components';

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

const addProductToRows = (rows: Row[], products: Product[]): Row[] =>
  rows.map((row: Row) => {
    if (null === products) return {...row, product: null};
    const product = products.find(product => product.identifier === row.identifier);
    if (undefined === product) return {...row, product: null};

    return {...row, product};
  });

const getAssociationIdentifiers = (rows: Row[]): AssociationIdentifiers =>
  rows.reduce(
    (identifiers: AssociationIdentifiers, row): AssociationIdentifiers => {
      identifiers[getProductsType(row.productType)].push(row.identifier);

      return identifiers;
    },
    {
      [ProductsType.Products]: [],
      [ProductsType.ProductModels]: [],
    }
  );

const useProducts = (identifiers: AssociationIdentifiers): Product[] | null => {
  const [products, setProducts] = useState<Product[] | null>(null);
  const userContext = useUserContext();
  const url = useRoute('pim_enrich_product_and_product_model_by_identifiers_rest_list', {
    channel: userContext.get('catalogScope'),
    locale: userContext.get('catalogLocale'),
  });

  useEffect(() => {
    (async () => {
      setProducts(await productFetcher(url, identifiers));
    })();
  }, [JSON.stringify(identifiers), url]);

  return products;
};

export {useProducts, addProductToRows, getAssociationIdentifiers};
