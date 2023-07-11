import React from 'react';
import {ProgressIndicator} from 'akeneo-design-system';
import styled from 'styled-components';

const ProgressIndicatorModalCreation = styled(ProgressIndicator)`
  width: 45%;
  position: absolute;
  bottom: 50px;
`;

type Props = {
  currentStepIndex: number;
  selectedType?: string;
};

const CreateAttributeProgressIndicator: React.FC<Props> = ({currentStepIndex, selectedType}) => {
  const isSettingsStep = selectedType === 'table' ? currentStepIndex === 2 : currentStepIndex === 1;

  return (
    <ProgressIndicatorModalCreation>
      <ProgressIndicator.Step current={currentStepIndex === -1}>ATTRIBUTE TYPE</ProgressIndicator.Step>
      {selectedType === 'table' && <ProgressIndicator.Step>TEMPLATE</ProgressIndicator.Step>}
      <ProgressIndicator.Step current={isSettingsStep}>ATTRIBUTE SETTINGS</ProgressIndicator.Step>
    </ProgressIndicatorModalCreation>
  );
};

export {CreateAttributeProgressIndicator};
