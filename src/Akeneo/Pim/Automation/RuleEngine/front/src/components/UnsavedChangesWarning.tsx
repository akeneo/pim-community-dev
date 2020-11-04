import React from 'react';
import {useTranslate} from '../dependenciesTools/hooks';

type Props = {};

const UnsavedChangesWarning: React.FC<Props> = () => {
  const translate = useTranslate();

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

export {UnsavedChangesWarning};
