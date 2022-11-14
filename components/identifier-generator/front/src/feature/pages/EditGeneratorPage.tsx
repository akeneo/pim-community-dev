import React from 'react';
import {IdentifierGenerator} from '../models';
import {CreateOrEditGeneratorPage} from './CreateOrEditGeneratorPage';

type EditGeneratorProps = {
  initialGenerator: IdentifierGenerator;
};

const EditGeneratorPage: React.FC<EditGeneratorProps> = ({initialGenerator}) => {
  return (
    <CreateOrEditGeneratorPage
      initialGenerator={initialGenerator}
      mainButtonCallback={
        /* istanbul ignore next */
        () => alert('not implemented')
      }
      validationErrors={[]}
      isNew={false}
    />
  );
};

export {EditGeneratorPage};
