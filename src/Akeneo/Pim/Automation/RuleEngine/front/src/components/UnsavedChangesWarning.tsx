import React from 'react';
import { Translate } from '../dependenciesTools';

type Props = {
  translate: Translate;
};

const UnsavedChangesWarning: React.FC<Props> = ({ translate }) => {
  return (
    <div className='AknTitleContainer-state'>
      <div className='updated-status'>
        <span className='AknState'>
          {translate('There are unsaved changes.')}
        </span>
      </div>
    </div>
  );
};

export { UnsavedChangesWarning };
