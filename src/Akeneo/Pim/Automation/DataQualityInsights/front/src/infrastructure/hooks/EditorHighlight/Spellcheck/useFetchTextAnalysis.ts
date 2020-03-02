import {useEffect, useState} from 'react';
import {useDispatch} from 'react-redux';
import {WidgetElement} from "../../../../application/helper";
import {updateWidgetContentAnalysis} from "../../../reducer";
import {fetchTextAnalysis} from "../../../fetcher";
import {useCatalogContext} from "../../index";
import useProduct from "../../useProduct";

const useFetchTextAnalysis = (widget: WidgetElement) => {
  const [previousContent, setPreviousContent] = useState<null | string>(null);
  const dispatchAction = useDispatch();
  const {locale} = useCatalogContext();
  const product = useProduct();
  const {id, content, analysis, editorId, isActive} = widget;
  const dispatchTextAnalysis = async (content: string, locale: string) => {
    if (!locale || !content) {
      dispatchAction(updateWidgetContentAnalysis(id, []));
      return;
    }
    const textAnalysis = await fetchTextAnalysis(content, locale);
    dispatchAction(updateWidgetContentAnalysis(id, textAnalysis));
  };

  const hasContentChangedSinceLastAnalysis = () => content === null || content != previousContent;

  useEffect(() => {
    if(isActive && hasContentChangedSinceLastAnalysis()) {
      (async () => {
        setPreviousContent(content);
        await dispatchTextAnalysis(content, locale as string);
      })();
    }
  }, [editorId, isActive]);

  useEffect(() => {
    if (previousContent !== null) {
      setPreviousContent(content);
    }
  }, [content]);

  useEffect(() => {
    setPreviousContent(null);
  }, [product]);

  return {analysis, dispatchTextAnalysis};
};

export default useFetchTextAnalysis;
