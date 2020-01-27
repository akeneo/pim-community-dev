import React, {FunctionComponent, useEffect} from "react";
import {HighlightElement, HighlightsCollection, WidgetElement} from "../../domain";
import {useGetSpellcheckPopover} from "../hooks";

const HIGHLIGHT_INTERSECTING_MARGIN = 2;

const isIntersectingHighlight = (x: number, y: number, highlight: HighlightElement) => {
  const rect: DOMRect = highlight.domRange.getBoundingClientRect();

  return (
    x >= (rect.left - HIGHLIGHT_INTERSECTING_MARGIN) &&
    x <= (rect.right + HIGHLIGHT_INTERSECTING_MARGIN) &&
    y >= (rect.top - HIGHLIGHT_INTERSECTING_MARGIN) &&
    y <= (rect.bottom + HIGHLIGHT_INTERSECTING_MARGIN)
  );
};

interface EditorContextProviderProps {
  widget: WidgetElement;
  highlights: HighlightsCollection;
}

const EditorContextProvider: FunctionComponent<EditorContextProviderProps> = ({widget, highlights}) => {
  const {handleOpening, handleClosing} = useGetSpellcheckPopover();

  useEffect(() => {
    const {editor} = widget;

    const handleMouseMove = (event: MouseEvent) => {
      const eventClientX = event.clientX;
      const eventClientY = event.clientY;

      window.requestAnimationFrame(() => {
        const activeHighlight = Object.values(highlights).find((highlight) =>  {
          return isIntersectingHighlight(eventClientX, eventClientY, highlight)
        });

        if (!activeHighlight) {
          handleClosing();
          return;
        }

        handleOpening(widget.id, activeHighlight);
      })
    };

    if (editor) {
      // @ts-ignore
      editor.addEventListener('mousemove', handleMouseMove);
    }

    return () => {
      if (editor) {
        // @ts-ignore
        editor.removeEventListener('mousemove', handleMouseMove);
      }
    };
  }, [widget.editor, widget.editorId, highlights]);

  return <></>;
};

export default EditorContextProvider;
