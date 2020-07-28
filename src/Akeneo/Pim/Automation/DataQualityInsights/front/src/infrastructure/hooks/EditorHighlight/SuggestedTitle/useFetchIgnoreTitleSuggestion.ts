import {useCallback} from "react";
import {useCatalogContext, useProduct} from "../../index";
import fetchIgnoreTitleSuggestion from "../../../fetcher/fetchIgnoreTitleSuggestion";

const useFetchIgnoreTitleSuggestion = () => {
  const product = useProduct();
  const {channel, locale} = useCatalogContext();

  const dispatchIgnoreTitleSuggestion = useCallback((title: string) => {
    if (!locale || ! channel || !product.meta.id) {
      return;
    }

    (async () => {
      await fetchIgnoreTitleSuggestion(title, channel, locale, product.meta.id as number);
    })();
  }, [channel, locale]);

  return {
    dispatchIgnoreTitleSuggestion: dispatchIgnoreTitleSuggestion
  };
};

export default useFetchIgnoreTitleSuggestion;
