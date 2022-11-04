import React from 'react';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';
import {useSaveGenerator} from '../hooks/useSaveGenerator';

type EditGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const EditGeneratorPage: React.FC<EditGeneratorProps> = ({initialGenerator}) => {
  // @ts-ignore
  const {save, isLoading, error} = useSaveGenerator();

  return (
    <CreateOrEditGeneratorPage
      initialGenerator={initialGenerator}
      mainButtonCallback={save}
      isMainButtonDisabled={isLoading}
      validationErrors={error}
      isNew={false}
    />
  );
};

export {EditGeneratorPage};
