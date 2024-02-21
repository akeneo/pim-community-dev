import React from 'react';
import styled from 'styled-components';
import {DangerPlainIcon, getColor} from 'akeneo-design-system';
import {useTranslate} from '../hooks';

const Container = styled.div`
  color: ${getColor('grey', 140)};
  font-style: italic;
  border-bottom: 1px solid ${getColor('yellow', 100)};
  white-space: nowrap;
  margin-top: 6px;
  display: flex;
  align-items: center;
  height: 17px;
  gap: 3px;
`;

const Danger = styled(DangerPlainIcon)`
  color: ${getColor('yellow', 100)};
`;

const UnsavedChanges = () => {
  const translate = useTranslate();

  return (
    <Container>
      <Danger size={18} /> {translate('pim_common.entity_updated')}
    </Container>
  );
};

export {UnsavedChanges};
