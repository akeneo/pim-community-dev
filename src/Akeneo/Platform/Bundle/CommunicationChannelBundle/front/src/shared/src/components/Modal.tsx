import * as React from 'react';
import {PropsWithChildren, ReactElement, ButtonHTMLAttributes} from 'react';

import styled from 'styled-components';
import {useAkeneoTheme} from '../hooks';
import {CloseIcon} from '../icons';
import {Button} from '../components';

const Modal = styled.div`
  position: fixed;
  width: 100vw;
  height: 100vh;
  top: 0;
  left: 0;
  background: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
  z-index: 1040;
  overflow: hidden;
  align-items: center;
  padding: 20px 0;
`;

const ModalCloseButtonContainer = styled.button`
  background: none;
  border: none;
  height: 32px;
  left: 24px;
  margin: 0;
  padding: 0;
  position: absolute;
  top: 24px;
  width: 32px;

  &:hover {
    cursor: pointer;
  }
`;

const ModalConfirmButton = styled(Button)`
  position: absolute;
  top: 24px;
  right: 24px;
`;

const ModalCloseButton = ({title, ...props}: ButtonHTMLAttributes<HTMLButtonElement>) => {
  const akeneoTheme = useAkeneoTheme();

  return (
    <ModalCloseButtonContainer tabIndex={0} title={title} aria-label={title} {...props}>
      <CloseIcon color={akeneoTheme.color.grey100} title={title} size={24} />
    </ModalCloseButtonContainer>
  );
};

type ModalWithIllustrationProps = {
  illustration: ReactElement;
};

const ModalBodyWithIllustration = ({illustration, children}: PropsWithChildren<ModalWithIllustrationProps>) => (
  <div className='AknFullPage-content AknFullPage-content--withIllustration'>
    <div>{illustration}</div>
    <div>{children}</div>
  </div>
);

const ModalTitleContainer = styled.div.attrs(() => ({className: 'AknFullPage-titleContainer'}))`
  margin-bottom: 16px;
`;

const ModalDescription = styled.div`
  font-size: ${props => props.theme.fontSize.bigger};
  line-height: 1.5;
  margin-bottom: 10px;
`;

type ModalTitleProps = {
  title: string;
  subtitle?: string;
};

const ModalTitle = ({title, subtitle, ...props}: ModalTitleProps) => (
  <ModalTitleContainer {...props}>
    {subtitle && <div className='AknFullPage-subTitle'>{subtitle}</div>}
    <div className='AknFullPage-title'>{title}</div>
  </ModalTitleContainer>
);

export {Modal, ModalCloseButton, ModalBodyWithIllustration, ModalTitle, ModalDescription, ModalConfirmButton};
