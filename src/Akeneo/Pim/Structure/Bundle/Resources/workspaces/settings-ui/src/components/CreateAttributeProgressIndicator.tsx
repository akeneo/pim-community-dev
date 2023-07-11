import React, {useMemo} from 'react';
import styled from 'styled-components';
import {ProgressIndicator} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

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
  const translate = useTranslate();
  const isSettingsStep = selectedType === 'pim_catalog_table' ? currentStepIndex === 2 : currentStepIndex === 1;
  const steps = useMemo(() => {
    const commonSteps = [
      {current: currentStepIndex === -1, label: 'pim_enrich.entity.attribute.property.attribute_creation_type'},
      {current: isSettingsStep, label: 'pim_enrich.entity.attribute.property.attribute_creation_settings'},
    ];

    if (selectedType === 'pim_catalog_table')
      commonSteps.splice(1, 0, {
        current: currentStepIndex === 1,
        label: 'pim_enrich.entity.attribute.property.attribute_creation_template',
      });

    return commonSteps;
  }, [selectedType, currentStepIndex]);

  return (
    <ProgressIndicatorModalCreation>
      {steps.map(({label, current}) => (
        <ProgressIndicator.Step current={current} key={label}>
          {translate(label)}
        </ProgressIndicator.Step>
      ))}
    </ProgressIndicatorModalCreation>
  );
};

export {CreateAttributeProgressIndicator};
