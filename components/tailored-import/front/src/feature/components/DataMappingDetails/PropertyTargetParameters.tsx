import React from 'react';
import styled from 'styled-components';
import {Checkbox, SectionTitle} from 'akeneo-design-system';
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
  onTargetChange: (target: PropertyTarget) => void;
};

const PropertyTargetParameters = ({target, onTargetChange}: PropertyTargetParametersProps) => {
  const translate = useTranslate();

  const handleClearIfEmptyChange = (clearIfEmpty: boolean) =>
    onTargetChange({...target, action_if_empty: clearIfEmpty ? 'clear' : 'skip'});

  return (
    <TargetParametersContainer>
      <SectionTitle sticky={0}>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.tailored_import.data_mapping.target.title')}
        </SectionTitle.Title>
      </SectionTitle>
      <Container>
        <Checkbox checked={'clear' === target.action_if_empty} onChange={handleClearIfEmptyChange}>
          {translate('akeneo.tailored_import.data_mapping.target.clear_if_empty')}
        </Checkbox>
      </Container>
    </TargetParametersContainer>
  );
};

export type {PropertyTargetParametersProps};
export {PropertyTargetParameters};
