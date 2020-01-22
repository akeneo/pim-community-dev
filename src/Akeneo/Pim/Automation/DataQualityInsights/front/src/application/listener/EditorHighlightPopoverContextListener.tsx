import React, {FunctionComponent, RefObject, useEffect} from "react";
import {debounce} from "lodash";

const CLOSING_MILLISECONDS_DELAY = 300;

interface EditorHighlightPopoverContextListenerProps {
  popoverRef: RefObject<HTMLDivElement>;
  widgetId: string|null;
  handleClosing: Function;
}

const EditorHighlightPopoverContextListener: FunctionComponent<EditorHighlightPopoverContextListenerProps> = ({popoverRef, handleClosing}) => {
  useEffect(() => {
    const handleDocumentMouseLeave = debounce(() => {
      handleClosing();
    }, CLOSING_MILLISECONDS_DELAY);

    const handleDocumentMouseMove = debounce((event: MouseEvent) => {
      if (popoverRef.current && (
        // @ts-ignore
        event.target !== popoverRef.current && !popoverRef.current.contains(event.target)
      )) {
        handleClosing();
      }
    }, CLOSING_MILLISECONDS_DELAY);

    document.addEventListener('mouseleave', handleDocumentMouseLeave);
    document.addEventListener('mousemove', handleDocumentMouseMove);

    return () => {
      document.removeEventListener('mouseleave', handleDocumentMouseLeave);
      document.removeEventListener('mousemove', handleDocumentMouseMove);
    };
  }, [handleClosing]);

  return <></>;
};

export default EditorHighlightPopoverContextListener;
