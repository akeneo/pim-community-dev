import {useEffect} from 'react';
import {useDispatch} from 'react-redux';
import {WidgetElement} from "../../../../application/helper";
import {fetchTitleSuggestion} from "../../../fetcher";
import {useCatalogContext} from "../../index";
import {updateWidgetContentAnalysis} from "../../../reducer";
import useProductAxesRates from "../../useProductAxesRates";

const useFetchTitleSuggestion = (widget: WidgetElement) => {
  const {locale, channel} = useCatalogContext();
  const {axesRates, productId} = useProductAxesRates();
  const {analysis} = widget;
  const dispatchAction = useDispatch();

  useEffect(() => {
    (async () => {
      if (!widget.isMainLabel) {
        return;
      }

      if (!productId || !channel || !locale) {
        dispatchAction(updateWidgetContentAnalysis(widget.id, []));
        return;
      }

      const result: string|null = await fetchTitleSuggestion(productId, channel, locale);

      if (typeof result !== "string" || result.length === 0) {
        dispatchAction(updateWidgetContentAnalysis(widget.id, []));
        return;
      }

      const suggestions: string[] = [result];

      dispatchAction(updateWidgetContentAnalysis(widget.id, [{
        text: widget.content,
        type: "title_suggestion",
        globalOffset: 0,
        offset: 0,
        line: 1,
        suggestions
      }]));
    })();
  }, [axesRates]);

  return {analysis};
};

export default useFetchTitleSuggestion;
