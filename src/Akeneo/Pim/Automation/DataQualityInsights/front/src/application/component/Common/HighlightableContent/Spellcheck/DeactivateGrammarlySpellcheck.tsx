import React, {FC, useEffect} from 'react';
import {useHighlightableContentContext} from '../../../../context/HighlightableContentContext';

const DeactivateGrammarlySpellcheck: FC<{}> = () => {
  const {element} = useHighlightableContentContext();

  useEffect(() => {
    if (element) {
      element.setAttribute('data-gramm', 'false');
      element.setAttribute('data-gramm_editor', 'false');
    }
  }, [element]);

  return <></>;
};

export default DeactivateGrammarlySpellcheck;
