import styled from 'styled-components';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

export const Modal = styled.div`
  display: flex;
  flex-direction: column;
  border-radius: 0;
  border: none;
  top: 0;
  left: 0;
  position: fixed;
  z-index: 1050;
  background: white;
  width: 100%;
  height: 100%;
  padding: 40px;
`;

export const ScrollableModal = styled(Modal)`
  padding-bottom: 0;
`;

export const ConfirmButton = styled(Button)`
  position: absolute;
  top: 0;
  right: 0;
`;

export const Title = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  line-height: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  margin-bottom: 23px;
  text-align: center;
  width: 100%;
`;

export const SubTitle = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey120};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
  margin-bottom: 4px;
  text-align: center;
  text-transform: uppercase;
  width: 100%;
`;

export const Header = styled.div`
  position: relative;
`;
