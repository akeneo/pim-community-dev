import * as React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const EditState = () => {
  const translate = useTranslate();

  return (
    <div className="AknTitleContainer-state">
      <div className="updated-status">
        <span className="AknState">{translate('pim_common.entity_updated')}</span>
      </div>
    </div>
  );
};

export default EditState;
