import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {PropertyTarget} from '../../models';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 10px;
`;

const TargetParametersContainer = styled.div`
  display: flex;
  flex-direction: column;
`;

type PropertyTargetParametersProps = {
  target: PropertyTarget;
  /** Specific parameters of the property */
  children?: ReactNode;
  onTargetChange: (target: PropertyTarget) => void;
};

const PropertyTargetParameters = ({children, target, onTargetChange}: PropertyTargetParametersProps) => {
  const translate = useTranslate();

  return (
    <TargetParametersContainer>
      <SectionTitle sticky={0}>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.tailored_import.data_mapping.target.title')}
        </SectionTitle.Title>
      </SectionTitle>
      <Container>{children}</Container>
    </TargetParametersContainer>
  );
};

export type {PropertyTargetParametersProps};
export {PropertyTargetParameters};
