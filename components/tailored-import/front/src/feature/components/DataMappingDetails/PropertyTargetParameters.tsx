import React from 'react';
import styled from 'styled-components';
import {Checkbox} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {PropertyTarget} from '../../models';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
`;

type PropertyTargetParametersProps = {
  target: PropertyTarget;
  onTargetChange: (target: PropertyTarget) => void;
};

const PropertyTargetParameters = ({target, onTargetChange}: PropertyTargetParametersProps) => {
  const translate = useTranslate();

  const handleClearIfEmptyChange = (clearIfEmpty: boolean) =>
    onTargetChange({...target, if_empty: clearIfEmpty ? 'clear' : 'skip'});

  return (
    <Container>
      <Checkbox checked={'clear' === target.if_empty} onChange={handleClearIfEmptyChange}>
        {translate('akeneo.tailored_import.data_mapping.target.clear_if_empty')}
      </Checkbox>
    </Container>
  );
};

export {PropertyTargetParameters};
