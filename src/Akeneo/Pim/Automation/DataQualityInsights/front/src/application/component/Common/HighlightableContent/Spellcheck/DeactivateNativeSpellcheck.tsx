import React, {FC, useEffect} from 'react';
import {useHighlightableContentContext} from '../../../../context/HighlightableContentContext';

const DeactivateNativeSpellcheck: FC<{}> = () => {
  const {element} = useHighlightableContentContext();

  useEffect(() => {
    if (element) {
      element.setAttribute('spellcheck', 'false');
    }
  }, [element]);

  return <></>;
};

export default DeactivateNativeSpellcheck;
