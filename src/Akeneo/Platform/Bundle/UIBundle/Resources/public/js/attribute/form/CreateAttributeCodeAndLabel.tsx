import React from 'react';
import {CreateAttributeButtonStepProps} from './CreateAttributeButtonApp';
import {CreateAttributeModal} from '@akeneo-pim-community/settings-ui';
import {CreateAttributeProgressIndicator} from './CreateAttributeProgressIndicator';

const CreateAttributeCodeAndLabel: React.FC<CreateAttributeButtonStepProps> = props => {
  const currentIndex = props?.initialData?.attribute_type === 'table' ? 2 : 1;

  return (
    <CreateAttributeModal {...props}>
      <CreateAttributeProgressIndicator
        currentStepIndex={currentIndex}
        selectedType={props?.initialData?.attribute_type}
      />
    </CreateAttributeModal>
  );
};

export const view = CreateAttributeCodeAndLabel;
