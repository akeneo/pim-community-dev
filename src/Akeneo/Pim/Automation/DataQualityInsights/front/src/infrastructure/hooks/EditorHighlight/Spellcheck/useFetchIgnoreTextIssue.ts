import {useCallback} from "react";
import {useCatalogContext, useGetEditorHighlightWidgetsList, useProduct} from "../../index";
import {fetchIgnoreTextIssue} from "../../../fetcher";
import fetchProductModelIgnoreTextIssue
  from '../../../fetcher/ProductEditForm/Spellcheck/fetchProductModelIgnoreTextIssue';
import {isSimpleProduct, isVariantProduct} from "@akeneo-pim-community/data-quality-insights/src/application/helper";

const useFetchIgnoreTextIssue = () => {
  const product = useProduct();
  const widgets = useGetEditorHighlightWidgetsList();
  const {locale} = useCatalogContext();

  const dispatchIgnoreTextIssue = useCallback(
    (word: string) => {
      if (!locale || !product.meta.id) {
        return;
      }

      (async () => {
        if (isSimpleProduct(product) || isVariantProduct(product)) {
          await fetchIgnoreTextIssue(word, locale, product.meta.id as number);
        } else {
          await fetchProductModelIgnoreTextIssue(word, locale, product.meta.id as number);
        }
      })();

      Object.values(widgets).forEach(({editor}) => {
        editor.dispatchEvent(new Event('input', {bubbles: true}));
      });
    },
    [widgets, locale, product.meta.id]
  );

  return {
    dispatchIgnoreTextIssue,
  };
};

export default useFetchIgnoreTextIssue;
