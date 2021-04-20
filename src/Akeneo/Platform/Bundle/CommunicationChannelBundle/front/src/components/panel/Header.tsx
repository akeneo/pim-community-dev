import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, useTheme, CloseIcon, MegaphoneIcon} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
  margin-right: 30px;
  margin-left: 30px;
  background-color: ${({theme}: AkeneoThemedProps) => theme.color.white};
  width: 340px;
  height: 44px;
  border-bottom: 1px solid ${({theme}: AkeneoThemedProps) => theme.color.purple100};
  display: flex;
  position: sticky;
  top: 0px;
  padding-bottom: 47px;
`;

const IconContainer = styled.div`
  margin: 20px 0 0 0px;
`;

const Title = styled.div`
  color: ${({theme}: AkeneoThemedProps) => theme.color.purple100};
  font-size: ${({theme}: AkeneoThemedProps) => theme.fontSize.big};
  height: 18px;
  margin: 20px 0 0 8px;
  text-transform: uppercase;
`;

const CloseButton = styled.div`
  cursor: pointer;
  border: none;
  margin: 24px 0 0 0;
  position: absolute;
  right: 0px;
`;

type HeaderPanelProps = {
  title: string;
  onClickCloseButton: () => void;
};

const HeaderPanel = ({title, onClickCloseButton}: HeaderPanelProps): JSX.Element => {
  const __ = useTranslate();
  const akeneoTheme = useTheme();

  return (
    <Container>
      <IconContainer>
        <MegaphoneIcon color={akeneoTheme.color.purple100} size={24} />
      </IconContainer>
      <Title>{title}</Title>
      <CloseButton onClick={onClickCloseButton}>
        <CloseIcon color={akeneoTheme.color.purple100} title={__('pim_common.close')} size={15} />
      </CloseButton>
    </Container>
  );
};

export {HeaderPanel};
