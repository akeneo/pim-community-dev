import {useEffect} from 'react';
import {useDispatch} from 'react-redux';
import {WidgetElement} from "../../../../application/helper";
import {updateWidgetContentAnalysis} from "../../../reducer";
import {fetchTextAnalysis} from "../../../fetcher";
import {useCatalogContext} from "../../index";

const useFetchTextAnalysis = (widget: WidgetElement) => {
  const dispatchAction = useDispatch();
  const {locale} = useCatalogContext();
  const {id, content, analysis, editorId} = widget;
  const dispatchTextAnalysis = async (content: string, locale: string) => {
    if (!locale || !content) {
      dispatchAction(updateWidgetContentAnalysis(id, []));
      return;
    }
    const textAnalysis = await fetchTextAnalysis(content, locale);
    dispatchAction(updateWidgetContentAnalysis(id, textAnalysis));
  };

  useEffect(() => {
    (async () => {
      await dispatchTextAnalysis(content, locale as string);
    })();
  }, [editorId]);

  return {analysis, dispatchTextAnalysis};
};

export default useFetchTextAnalysis;
