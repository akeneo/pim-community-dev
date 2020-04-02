import React from 'react';
import {useContext} from 'react';
import {TranslateContext} from 'akeneomeasure/context/translate-context';

const UnsavedChanges = () => {
  const __ = useContext(TranslateContext);

  return (
    <div className="AknTitleContainer-state">
      <div className="updated-status">
        <span className="AknState">{__('pim_common.entity_updated')}</span>
      </div>
    </div>
  );
};

export {UnsavedChanges};
