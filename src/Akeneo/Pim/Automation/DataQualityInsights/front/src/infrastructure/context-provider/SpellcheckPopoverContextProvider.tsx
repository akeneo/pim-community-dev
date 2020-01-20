import React, {FunctionComponent, RefObject, useEffect} from "react";
import {debounce} from "lodash";

const CLOSING_MILLISECONDS_DELAY = 300;

interface SpellcheckPopoverContextProviderProps {
  popoverRef: RefObject<HTMLDivElement>;
  widgetId: string|null;
  isOpen: boolean;
  handleClosing: Function;
}

const SpellcheckPopoverContextProvider: FunctionComponent<SpellcheckPopoverContextProviderProps> = ({popoverRef, isOpen, handleClosing}) => {

  useEffect(() => {
    const handleDocumentMouseLeave = debounce(() => {
      if (isOpen) {
        handleClosing();
      }
    }, CLOSING_MILLISECONDS_DELAY);

    const handleDocumentMouseMove = debounce((event: MouseEvent) => {
      if (isOpen && popoverRef.current && (
        // @ts-ignore
        event.target !== popoverRef.current || !popoverRef.current.contains(event.target)
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
  }, [popoverRef, isOpen, handleClosing]);

  return <></>;
};

export default SpellcheckPopoverContextProvider;
