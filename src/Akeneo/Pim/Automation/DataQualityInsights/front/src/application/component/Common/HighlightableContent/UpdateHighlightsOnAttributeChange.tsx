import React, {FC, useCallback, useEffect} from 'react';

import {useHighlightableContentContext} from '../../../context/HighlightableContentContext';

const UpdateHighlightsOnAttributeChange: FC<{}> = () => {
  const {refresh} = useHighlightableContentContext();

  const handleAttributeUpdate = useCallback(() => {
    refresh();
  }, []);

  useEffect(() => {
    window.addEventListener('quality_summary_header_updated', handleAttributeUpdate);

    return () => {
      window.removeEventListener('quality_summary_header_updated', handleAttributeUpdate);
    };
  }, [handleAttributeUpdate]);

  return <></>;
};

export default UpdateHighlightsOnAttributeChange;
