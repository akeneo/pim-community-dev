import React, {FunctionComponent, useEffect, useState} from "react";
import {createPortal} from "react-dom";
import {useDispatch} from "react-redux";
import {
  disableWidgetAction,
  enableWidgetAction,
  showWidgetAction,
  updateWidgetContent
} from "../../../../../../infrastructure/reducer";
import {getEditorContent, WidgetElement} from "../../../../../../domain";
import Widget from "./Widget";
import {
  useCatalogContext,
  useFetchSpellcheckTextAnalysis
} from "../../../../../../infrastructure/hooks";
import {debounce} from "lodash";

const WIDGET_PREFIX_ID = "akeneo-spellchecker-widget";
const CHANGE_MILLISECONDS_DELAY = 500;

interface WidgetPortalProps {
  widget: WidgetElement
}

const WidgetPortal: FunctionComponent<WidgetPortalProps> = ({ widget}) => {
  const [widgetRootElement, setWidgetRootElement] = useState();
  const {locale} = useCatalogContext();
  const dispatchAction = useDispatch();
  const {dispatchTextAnalysis} = useFetchSpellcheckTextAnalysis(widget);

  useEffect(() => {
    const element = document.createElement("div");
    element.id = `${WIDGET_PREFIX_ID}-${widget.id}`;
    setWidgetRootElement(element);

    const handleFocus = () => {
      dispatchAction(showWidgetAction(widget.id));
      dispatchAction(enableWidgetAction(widget.id));
    };

    const handleBlur = () => {
      dispatchAction(disableWidgetAction(widget.id));
    };

    const handleChange = () => {
      const content = getEditorContent(widget.editor);
      dispatchAction(updateWidgetContent(widget.id, content));
    };

    const handleTextAnalysis = debounce(async () => {
      const content = getEditorContent(widget.editor);

      await dispatchTextAnalysis(content, locale as string);
    }, CHANGE_MILLISECONDS_DELAY);

    document.body.prepend(element);
    widget.editor.addEventListener("focus", handleFocus);
    widget.editor.addEventListener("blur", handleBlur);
    widget.editor.addEventListener("input", handleChange);
    widget.editor.addEventListener("input", handleTextAnalysis);

    return () => {
      widget.editor.removeEventListener("focus", handleFocus);
      widget.editor.removeEventListener("blur", handleBlur);
      widget.editor.removeEventListener("input", handleChange);
      widget.editor.removeEventListener("input", handleTextAnalysis);
      document.body.removeChild(element);
    };
  }, [widget.id, widget.editor, dispatchAction]);

  return <>{widgetRootElement && createPortal(<Widget widget={widget}/>, widgetRootElement)}</>;
};

export default WidgetPortal;
