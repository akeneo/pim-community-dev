import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

type HelperIconProps = {
  src: string
};

export const HelperSection = styled.div`
  background-color: ${(props: ThemedProps<void>) => props.theme.color.blue10};
  display: flex;
  padding: 20px;
  min-height: 80px;
  padding: 5px 5px 5px 0;
  margin-bottom: 20px;
  width: 100%;
  line-height: 25px;
  margin-top: 20px;
`;

export const HelperIcon = styled.div<HelperIconProps>`
  margin-left: 20px;
  min-width: 80px;
  min-height: 80px;
  height: auto;
  position: relative;
  background-image: url(${(props: any) => props.src});
  background-repeat: no-repeat;
  background-size: 80px;
  background-position: 50%;
`;

export const HelperSeparator = styled.div`
  background-color: ${(props: ThemedProps<void>) => props.theme.color.grey80};
  width: 1px;
  margin: 10px 20px 10px 20px;
`;

export const HelperTitle = styled.div`
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.bigger};
  margin: 20px 0 20px 0;
`;

export const HelperText = styled.div`
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
`;
