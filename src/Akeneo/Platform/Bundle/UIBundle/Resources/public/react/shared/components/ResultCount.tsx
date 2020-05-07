import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

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

const ResultCount = ({count = null, labelKey = 'pim_common.result_count'}: ResultCountProps) =>
  count === null ? null : <Container>{useTranslate()(labelKey, {itemsCount: count.toString()}, count)}</Container>;

export {ResultCount};
