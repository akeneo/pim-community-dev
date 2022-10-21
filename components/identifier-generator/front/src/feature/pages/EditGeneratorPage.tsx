import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';

type EditGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const EditGeneratorPage: React.FC<EditGeneratorProps> = ({initialGenerator}) => {
  const translate = useTranslate();

  return <CreateOrEditGeneratorPage
    initialGenerator={initialGenerator}
    mainButtonCallback={
      /* istanbul ignore next */
      () => alert('not implemented')
    }
    mainButtonLabel={translate('pim_common.save')}
    validationErrors={[]}
  />;
};

export {EditGeneratorPage};
