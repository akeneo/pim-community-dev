import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';
import {useSaveIdentifierGenerator} from '../hooks';

type CreateGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const CreateGeneratorPage: React.FC<CreateGeneratorProps> = ({initialGenerator}) => {
  const translate = useTranslate();
  const {onSave, validationErrors} = useSaveIdentifierGenerator();

  return <CreateOrEditGeneratorPage
    initialGenerator={initialGenerator}
    mainButtonLabel={translate('pim_common.save')}
    mainButtonCallback={onSave}
    validationErrors={validationErrors}
  />;
};

export {CreateGeneratorPage};
