import React, {FunctionComponent, RefObject, useEffect} from 'react';
import {debounce} from 'lodash';
import {HighlightElement, isIntersectingHighlight} from '../../helper';

const CLOSING_MILLISECONDS_DELAY = 300;

interface EditorHighlightPopoverContextListenerProps {
  popoverRef: RefObject<HTMLDivElement>;
  handleClosing: Function;
  activeHighlight: HighlightElement | null;
}

const EditorHighlightPopoverContextListener: FunctionComponent<EditorHighlightPopoverContextListenerProps> = ({
  popoverRef,
  activeHighlight,
  handleClosing,
}) => {
  useEffect(() => {
    const handleDocumentMouseLeave = debounce(() => {
      handleClosing();
    }, CLOSING_MILLISECONDS_DELAY);

    const handleDocumentMouseMove = debounce((event: MouseEvent) => {
      if (
        popoverRef.current &&
        // @ts-ignore
        event.target !== popoverRef.current &&
        // @ts-ignore
        !popoverRef.current.contains(event.target) &&
        activeHighlight &&
        !isIntersectingHighlight(event.clientX, event.clientY, activeHighlight)
      ) {
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
