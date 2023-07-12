import React, {useMemo} from 'react';
import styled from 'styled-components';
import {getColor, ProgressIndicator} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const ProgressIndicatorModalCreation = styled(ProgressIndicator)`
  width: 45%;
  position: absolute;
  bottom: 50px;
`;

type Props = {
  currentStepIndex: number;
  stepsName?: string[];
};

const Footer = styled.div`
  background-color: ${getColor('white')};
  position: fixed;
  width: 45%;
  bottom: 30px;
  left: 40%;
`;

const CreateAttributeProgressIndicator: React.FC<Props> = ({currentStepIndex, stepsName}) => {
  const translate = useTranslate();

  const stepsIndicator = useMemo(() => {
    if (currentStepIndex === -1) {
      return [
        'pim_enrich.entity.attribute.property.attribute_creation_type',
        'pim_enrich.entity.attribute.property.attribute_creation_settings',
      ];
    }

    return stepsName
      ? [
          'pim_enrich.entity.attribute.property.attribute_creation_type',
          ...stepsName?.map(stepName => {
            switch (stepName) {
              case 'SelectTemplate':
                return 'pim_enrich.entity.attribute.property.attribute_creation_template';
              default:
                return 'pim_enrich.entity.attribute.property.attribute_creation_settings';
            }
          }),
        ]
      : [];
  }, [currentStepIndex, stepsName]);

  return (
    <Footer>
      <ProgressIndicatorModalCreation>
        {stepsIndicator?.map((label, index) => (
          <ProgressIndicator.Step current={currentStepIndex + 1 === index} key={label}>
            {translate(label)}
          </ProgressIndicator.Step>
        ))}
      </ProgressIndicatorModalCreation>
    </Footer>
  );
};

export {CreateAttributeProgressIndicator};
