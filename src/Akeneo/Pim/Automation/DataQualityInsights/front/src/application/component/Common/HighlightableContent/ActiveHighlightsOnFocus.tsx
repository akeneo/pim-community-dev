import React, {FC, useEffect} from 'react';
import {useHighlightableContentContext} from '../../../context/HighlightableContentContext';

const ActiveHighlightsOnFocus: FC<{}> = () => {
  const {element, activate, isActive} = useHighlightableContentContext();

  useEffect(() => {
    const handleFocus = () => {
      if (!isActive) {
        activate();
      }
    };

    if (element !== null) {
      element.addEventListener('focus', handleFocus as EventListener);
    }

    return () => {
      if (element !== null) {
        element.removeEventListener('focus', handleFocus as EventListener);
      }
    };
  }, [element, activate]);

  return <></>;
};

export default ActiveHighlightsOnFocus;
