import * as React from 'react';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoassetmanager/tools/translator';

const Container = styled.div`
  white-space: nowrap;
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  margin-left: 10px;
`;

type ResultCountProps = {
  resultCount: number | null;
};
const ResultCounter = ({resultCount = null}: ResultCountProps) => {
  if (resultCount === null) return null;

  return <Container>{__('pim_asset_manager.grid.counter', {count: resultCount}, resultCount)}</Container>;
};

export default ResultCounter;
