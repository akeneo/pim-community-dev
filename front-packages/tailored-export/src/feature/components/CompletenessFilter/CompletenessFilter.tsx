import React from 'react';
import styled from 'styled-components';
import {LocaleCode} from '@akeneo-pim-community/shared';
import {OperatorSelector, Operator} from './OperatorSelector';
import {LocalesSelector} from './LocalesSelector';

const Container = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
`;


type CompletenessFilterProps = {
    operator: Operator,
    locales: LocaleCode[],
    onOperatorChange: (operator: string) => void;
    onLocalesChange: (locales: LocaleCode[]) => void;
};

const CompletenessFilter = ({operator, locales, onOperatorChange, onLocalesChange}: CompletenessFilterProps) => {
  return (
    <Container>
        <span>
            <OperatorSelector operator={operator} onChange={onOperatorChange} />
            {operator !== 'ALL' && (<LocalesSelector locales={locales} onChange={onLocalesChange} />)}
        </span>
    </Container>
  );
};

export {CompletenessFilter};
export type {Operator}
