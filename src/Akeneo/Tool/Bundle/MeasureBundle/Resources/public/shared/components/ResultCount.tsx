import React, {useContext} from 'react';
import styled from 'styled-components';
import {TranslateContext} from 'akeneomeasure/context/translate-context';

const Container = styled.div`
  white-space: nowrap;
  color: ${props => props.theme.color.purple100};
  margin-left: 10px;
  line-height: 16px;
  text-transform: none;
`;

type ResultCountProps = {
  count: number | null;
  labelKey?: string;
};

const ResultCount = ({count = null, labelKey = 'measurements.list.result_count'}: ResultCountProps) =>
  count === null ? null : (
    <Container>{useContext(TranslateContext)(labelKey, {itemsCount: count.toString()}, count)}</Container>
  );

export {ResultCount};
