import React from 'react';
import {CreateAttributeButtonStepProps} from './CreateAttributeButtonApp';
import {CreateAttributeModal} from '@akeneo-pim-community/settings-ui';

const CreateAttributeCodeAndLabel: React.FC<CreateAttributeButtonStepProps> = props => {
  return <CreateAttributeModal {...props} />;
};

export const view = CreateAttributeCodeAndLabel;
