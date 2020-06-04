import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps} from '@akeneo-pim-community/shared';

const __ = require('oro/translator');

const Header = styled.div`
  margin-bottom: 20px;
  background-color: ${({theme}: AkeneoThemedProps) => theme.color.purple100};
  height: 67px;
`;

const Icon = styled.div`
  background: url(/bundles/akeneocommunicationchannel/images/icon-gift-white.svg) no-repeat 50% 50%;
  width: 20px;
  height: 20px;
  float: left;
  margin: 20px 0 0 20px;
`;

const Title = styled.div`
  color: ${({theme}: AkeneoThemedProps) => theme.color.white};
  font-size: 22px;
  height: 27px;
  float: left;
  margin: 20px 0 0 10px;
  letter-spacing: 0.92px;
`;

const CloseButton = styled.div`
  background: url(/bundles/pimui/images/icon-delete-white.svg) no-repeat 50% 50%;
  cursor: pointer;
  border: none;
  float: right;
  margin: 24px 24px 0 0;
  width: 16px;
  height: 16px;
`;

type HeaderPanelProps = {
  title: string;
  onClickCloseButton: () => void;
};

const HeaderPanelComponent = ({title, onClickCloseButton}: HeaderPanelProps): JSX.Element => {
  return (
    <Header>
      <Icon />
      <Title>{title}</Title>
      <CloseButton title={__('pim_common.close')} onClick={onClickCloseButton} />
    </Header>
  );
};

export = HeaderPanelComponent;
