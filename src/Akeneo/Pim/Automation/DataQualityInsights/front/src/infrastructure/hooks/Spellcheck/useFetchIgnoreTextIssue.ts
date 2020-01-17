import {useCallback} from "react";
import {useCatalogContext, useGetSpellcheckWidgetsList, useProduct} from "../index";
import {fetchIgnoreTextIssue} from "../../fetcher";

const useFetchIgnoreTextIssue = () => {
  const product = useProduct();
  const widgets = useGetSpellcheckWidgetsList();
  const {locale} = useCatalogContext();

  const dispatchIgnoreTextIssue = useCallback((word: string) => {
    if (!locale || !product.meta.id) {
      return;
    }

    (async () => {
      await fetchIgnoreTextIssue(word, locale, product.meta.id as number);
    })();

    Object.values(widgets).forEach(({editor}) => {
      editor.dispatchEvent(new Event('input', { bubbles: true }));
    });
  }, [widgets, locale, product.meta.id]);

  return {
    dispatchIgnoreTextIssue
  };
};

export default useFetchIgnoreTextIssue;
