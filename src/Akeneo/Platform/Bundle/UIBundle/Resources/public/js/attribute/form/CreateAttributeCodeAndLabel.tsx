import React from 'react';
import {CreateAttributeButtonStepProps} from './CreateAttributeButtonApp';
import {CreateAttributeModal} from '@akeneo-pim-community/settings-ui';
import {CreateAttributeProgressIndicator} from '@akeneo-pim-community/settings-ui';

const CreateAttributeCodeAndLabel: React.FC<CreateAttributeButtonStepProps> = props => {
  const currentIndex = props?.initialData?.attribute_type === 'pim_catalog_table' ? 2 : 1;
  console.log('coucou');
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
