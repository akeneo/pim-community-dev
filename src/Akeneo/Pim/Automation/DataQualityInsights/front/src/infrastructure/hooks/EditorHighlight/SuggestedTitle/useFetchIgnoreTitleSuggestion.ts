import {useCallback} from "react";
import {useCatalogContext, useProduct} from "../../index";
import {isSimpleProduct, isVariantProduct} from '../../../../application/helper/ProductEditForm/Product';
import fetchIgnoreTitleSuggestion from "../../../fetcher/ProductEditForm/SuggestedTitle/fetchIgnoreTitleSuggestion";
import fetchProductModelIgnoreTitleSuggestion from '../../../fetcher/ProductEditForm/SuggestedTitle/fetchProductModelIgnoreTitleSuggestion';

const useFetchIgnoreTitleSuggestion = () => {
  const product = useProduct();
  const {channel, locale} = useCatalogContext();

  const dispatchIgnoreTitleSuggestion = useCallback((title: string) => {
    if (!locale || ! channel || !product.meta.id) {
      return;
    }

    (async () => {
      if (isSimpleProduct(product) || isVariantProduct(product)) {
        return await fetchIgnoreTitleSuggestion(title, channel, locale, product.meta.id as number);
      } else {
        return await fetchProductModelIgnoreTitleSuggestion(title, channel, locale, product.meta.id as number);
      }
    })();
  }, [channel, locale]);

  return {
    dispatchIgnoreTitleSuggestion: dispatchIgnoreTitleSuggestion
  };
};

export default useFetchIgnoreTitleSuggestion;
