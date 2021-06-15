import React from 'react';
import styled from 'styled-components';
import {useTranslate, LocaleCode} from '@akeneo-pim-community/shared';
import {Operator} from './OperatorSelector';

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

const CompletenessFilter = ({}: CompletenessFilterProps) => {
  const translate = useTranslate();

  return (
    <Container>
      <span>
        {translate(
          'pim_connector.export.completeness.selector.label')}
      </span>
        // voir Channel
    </Container>
  );
};

export {CompletenessFilter};
export type {Operator}
