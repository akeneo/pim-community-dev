import React from 'react';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';
import {useSaveIdentifierGenerator} from '../hooks';

type CreateGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const CreateGeneratorPage: React.FC<CreateGeneratorProps> = ({initialGenerator}) => {
  const {onSave, validationErrors} = useSaveIdentifierGenerator();

  return (
    <CreateOrEditGeneratorPage
      initialGenerator={initialGenerator}
      mainButtonCallback={onSave}
      validationErrors={validationErrors}
    />
  );
};

export {CreateGeneratorPage};
