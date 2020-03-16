import React, {PropsWithChildren, ReactElement} from 'react';
import styled from 'styled-components';
import {akeneoTheme} from 'akeneomeasure/shared/theme';
import {CloseIcon} from 'akeneomeasure/shared/icons/CloseIcon';

const Modal = styled.div.attrs(() => ({className: 'AknFullPage'}))``;

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

const ModalCloseButton = ({title, ...props}: {title: string} & any) => (
  <ModalCloseButtonContainer title={title} tabIndex={0} aria-label={title} {...props}>
    <CloseIcon color={akeneoTheme.color.grey100} title={title} size={24} />
  </ModalCloseButtonContainer>
);

type ModalWithIllustationProps = {
  illustration: ReactElement;
};

const ModalBodyWithIllustration = ({illustration, children}: PropsWithChildren<ModalWithIllustationProps>) => (
  <div className="AknFullPage-content AknFullPage-content--withIllustration">
    <div>{illustration}</div>
    <div>{children}</div>
  </div>
);

const ModalTitleContainer = styled.div.attrs(() => ({className: 'AknFullPage-titleContainer'}))`
  margin-bottom: 16px;
`;

type ModalTitleProps = {
  title: string;
  subtitle?: string;
};

const ModalTitle = ({title, subtitle}: ModalTitleProps) => (
  <ModalTitleContainer>
    {subtitle && <div className="AknFullPage-subTitle">{subtitle}</div>}
    <div className="AknFullPage-title">{title}</div>
  </ModalTitleContainer>
);

export {Modal, ModalCloseButton, ModalBodyWithIllustration, ModalTitle};
