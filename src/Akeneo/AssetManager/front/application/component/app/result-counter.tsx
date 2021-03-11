import * as React from 'react';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.div`
  white-space: nowrap;
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  margin-left: 10px;
  line-height: 16px;
  text-transform: none;
`;

type ResultCounterProps = {
  count: number | null;
  labelKey?: string;
};

export const ResultCounter = ({count = null, labelKey}: ResultCounterProps) => {
  const translate = useTranslate();

  return count === null ? null : (
    <Container>{translate(labelKey || 'pim_asset_manager.result_counter', {count}, count)}</Container>
  );
};
