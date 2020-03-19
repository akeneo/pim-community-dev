import {useEffect} from 'react';
import {useDispatch} from 'react-redux';
import {WidgetElement} from "../../../../application/helper";
import {fetchTitleSuggestion} from "../../../fetcher";
import {useCatalogContext, useProduct} from "../../index";
import {setHasSuggestedTitleAction, updateWidgetContentAnalysis} from "../../../reducer";
import useProductAxesRates from "../../useProductAxesRates";
import {isSimpleProduct} from '../../../../application/helper/ProductEditForm/Product';
import fetchProductModelTitleSuggestion from '../../../fetcher/fetchProductModelTitleSuggestion';

const useFetchTitleSuggestion = (widget: WidgetElement) => {
  const {locale, channel} = useCatalogContext();
  const {axesRates, productId} = useProductAxesRates();
  const {analysis} = widget;
  const product = useProduct();
  const dispatchAction = useDispatch();

  useEffect(() => {
    if (axesRates !== undefined) { // on app initialization, the axes rates data is undefined for the current product
      (async () => {
        if (!widget.isMainLabel) {
          return;
        }

        if (!productId || !channel || !locale) {
          dispatchAction(setHasSuggestedTitleAction(widget.id, false));
          dispatchAction(updateWidgetContentAnalysis(widget.id, []));
          return;
        }

        const result: string|null = await (isSimpleProduct(product) ?
          fetchTitleSuggestion(product, channel, locale) :
          fetchProductModelTitleSuggestion(product, channel, locale));

        if (typeof result !== "string" || result.length === 0) {
          dispatchAction(setHasSuggestedTitleAction(widget.id, false));
          dispatchAction(updateWidgetContentAnalysis(widget.id, []));
          return;
        }

        const suggestions: string[] = [result];

        dispatchAction(setHasSuggestedTitleAction(widget.id, true));
        dispatchAction(updateWidgetContentAnalysis(widget.id, [{
          text: widget.content,
          type: "title_suggestion",
          globalOffset: 0,
          offset: 0,
          line: 1,
          suggestions
        }]));
      })();
    }
  }, [axesRates]);

  return {analysis};
};

export default useFetchTitleSuggestion;
