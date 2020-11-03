import React, {FunctionComponent, useEffect} from 'react';
import {HighlightsCollection, isIntersectingHighlight, WidgetElement} from '../../helper';
import {useGetEditorHighlightPopover} from '../../../infrastructure/hooks';

interface EditorContextListenerProps {
  widget: WidgetElement;
  highlights: HighlightsCollection;
}

const EditorContextListener: FunctionComponent<EditorContextListenerProps> = ({widget, highlights}) => {
  const {handleOpening, handleClosing} = useGetEditorHighlightPopover();

  useEffect(() => {
    const {editor} = widget;

    const handleMouseMove = (event: MouseEvent) => {
      const eventClientX = event.clientX;
      const eventClientY = event.clientY;

      window.requestAnimationFrame(() => {
        const activeHighlight = Object.values(highlights).find(highlight => {
          return isIntersectingHighlight(eventClientX, eventClientY, highlight);
        });

        if (!activeHighlight) {
          handleClosing();
          return;
        }

        handleOpening(widget, activeHighlight);
      });
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

export default EditorContextListener;
