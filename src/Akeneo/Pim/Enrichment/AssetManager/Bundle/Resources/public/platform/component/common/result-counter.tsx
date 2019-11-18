import * as React from 'react';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoassetmanager/tools/translator';

const Container = styled.div`
  white-space: nowrap;
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  margin-left: 10px;
  line-height: 16px;
`;

type ResultCounterProps = {
  count: number | null;
  labelKey?: string;
};

export const ResultCounter = ({count = null, labelKey}: ResultCounterProps) =>
  count === null ? null : <Container>{__(labelKey || 'pim_asset_manager.result_counter', {count}, count)}</Container>;
