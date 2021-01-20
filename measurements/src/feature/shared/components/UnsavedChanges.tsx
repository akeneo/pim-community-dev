import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy';

const UnsavedChanges = () => {
  const __ = useTranslate();

  return (
    <div className="AknTitleContainer-state">
      <div className="updated-status">
        <span className="AknState">{__('pim_common.entity_updated')}</span>
      </div>
    </div>
  );
};

export {UnsavedChanges};
