import React, {FC, useEffect} from 'react';
import {debounce} from 'lodash';

import {useHighlightableContentContext} from '../../../context/HighlightableContentContext';

const CHANGE_MILLISECONDS_DELAY = 300;

const UpdateHighlightsOnInputChange: FC<{}> = () => {
  const {element, refresh} = useHighlightableContentContext();

  useEffect(() => {
    const handleInput = debounce(() => {
      refresh();
    }, CHANGE_MILLISECONDS_DELAY);

    if (element !== null) {
      element.addEventListener('input', handleInput as EventListener);
    }

    return () => {
      if (element !== null) {
        element.removeEventListener('input', handleInput as EventListener);
      }
    };
  }, [element, refresh]);

  return <></>;
};

export default UpdateHighlightsOnInputChange;
