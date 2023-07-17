import React, {useMemo} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, ProgressIndicator} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
  currentStepIndex: number;
  stepsName?: string[];
};

const Footer = styled.div<AkeneoThemedProps & {width: number}>`
  background-color: ${getColor('white')};
  position: fixed;
  width: ${({width}) => `${width}px`};
  bottom: 30px;
  left: calc(100vh - ${({width}) => `${width / 2}px`});

  :first-child {
    width: ${({width}) => `${width}px`};
    position: absolute;
    bottom: 50px;
  }
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

  const width = useMemo(() => 175 * (stepsIndicator.length || 1), [stepsIndicator]);

  return (
    <Footer width={width}>
      <ProgressIndicator>
        {stepsIndicator?.map((label, index) => (
          <ProgressIndicator.Step current={currentStepIndex + 1 === index} key={label}>
            {translate(label)}
          </ProgressIndicator.Step>
        ))}
      </ProgressIndicator>
    </Footer>
  );
};

export {CreateAttributeProgressIndicator};
