import React from 'react';
import {CreateAttributeButtonStepProps} from './CreateAttributeButtonApp';
import {CreateAttributeData} from '@akeneo-pim-community/settings-ui';

const CreateAttributeCodeAndLabel: React.FC<CreateAttributeButtonStepProps> = props => {
  return <CreateAttributeData {...props} />;
};

export const view = CreateAttributeCodeAndLabel;
