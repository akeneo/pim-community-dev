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
  steps?: any;
};

const CreateAttributeProgressIndicator: React.FC<Props> = ({currentStepIndex, steps}) => {
  const translate = useTranslate();

  const stepsIndicator = useMemo(
    () => [
      'pim_enrich.entity.attribute.property.attribute_creation_type',
      ...Object.keys(steps).map(stepKey => {
        switch (stepKey) {
          default:
            return 'pim_enrich.entity.attribute.property.attribute_creation_settings';
        }
      }),
    ],
    [steps]
  );

  return (
    <ProgressIndicatorModalCreation>
      {stepsIndicator?.map((label, index) => (
        <ProgressIndicator.Step current={currentStepIndex + 1 === index} key={label}>
          {translate(label)}
        </ProgressIndicator.Step>
      ))}
    </ProgressIndicatorModalCreation>
  );
};

export {CreateAttributeProgressIndicator};
