import React, {FC, useEffect} from 'react';
import {useHighlightableContentContext} from '../../../../context/HighlightableContentContext';
import {useHighlightsContext} from '../../../../context/HighlightsContext';

const DeactivateNativeAutocomplete: FC<{}> = () => {
  const {element, isActive} = useHighlightableContentContext();
  const {highlights} = useHighlightsContext();

  useEffect(() => {
    if (element) {
      const value = isActive && highlights.length > 0 ? 'off' : 'on';

      element.setAttribute('autocomplete', value);
    }
  }, [element, isActive, highlights]);

  return <></>;
};

export default DeactivateNativeAutocomplete;
