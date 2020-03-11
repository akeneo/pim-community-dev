import React, {PropsWithChildren, ReactElement, ReactNode} from 'react';
import styled from 'styled-components';
import {akeneoTheme} from 'akeneomeasure/shared/theme';
import {CloseIcon} from 'akeneomeasure/shared/icons/CloseIcon';

export const Modal = ({children}: { children?: ReactNode; }) => {
  return (
    <div className="AknFullPage">
      {children}
    </div>
  );
};

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

export const ModalCloseButton = ({title, ...props}: {title: string} & any) => (
  <ModalCloseButtonContainer title={title} tabIndex={0} aria-label={title} {...props}>
    <CloseIcon color={akeneoTheme.color.grey100} title={title} size={24} />
  </ModalCloseButtonContainer>
);

type ModalWithIllustationProps = {
  illustration: ReactElement;
}

export const ModalBodyWithIllustration = ({
  illustration,
  children,
}: PropsWithChildren<ModalWithIllustationProps>) => {
  return (
    <div className="AknFullPage-content AknFullPage-content--withIllustration">
      <div>{illustration}</div>
      <div>{children}</div>
    </div>
  );
};

type ModalTitleProps = {
  title: string;
  subtitle?: string;
}

export const ModalTitle = ({title, subtitle}: ModalTitleProps) => {
  return (
    <div className="AknFullPage-titleContainer">
      {subtitle && <div className="AknFullPage-subTitle">{subtitle}</div>}
      <div className="AknFullPage-title">{title}</div>
    </div>
  )
};
