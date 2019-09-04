import styled from 'styled-components';
import {ThemedProps} from 'akeneopimenrichmentassetmanager/platform/component/theme';

export const NoDataSection = styled.div`
  text-align: center;
  margin-top: 70px;
`;

export const NoDataTitle = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.noDataTitle};
  text-align: center;
  margin: 30px 0 20px 0;
`;

export const NoDataText = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey120};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.bigger};
  text-align: center;
`;
