import React, {FC, useEffect} from 'react';
import {useHighlightableContentContext} from '../../../context/HighlightableContentContext';

const ActiveHighlightsOnLoad: FC<{}> = () => {
  const {element, activate, isActive} = useHighlightableContentContext();

  useEffect(() => {
    if (!isActive) {
      activate();
    }
  }, [element, activate]);

  return <></>;
};

export default ActiveHighlightsOnLoad;
