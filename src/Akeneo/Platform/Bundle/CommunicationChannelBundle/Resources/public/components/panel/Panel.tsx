import React from 'react';
import styled from 'styled-components';
import {useCards} from 'akeneocommunicationchannel/hooks/useCards';
import {CardFetcherImplementation, CardFetcher} from 'akeneocommunicationchannel/fetcher/card';

const mediator = require('oro/mediator');
const __ = require('oro/translator');

const Header = styled.div`
  margin-bottom: 50px;
`;

const Title = styled.div`
  color: #9452BA;
  font-size: 28px;
  line-height: 41px;
  float: left;
`;

const CloseButton = styled.div`
  background: url(/bundles/pimui/images/icon-delete-slategrey.svg) no-repeat 50% 50%;
  cursor: pointer;
  border: none;
  float: right;
  margin-right: 10px;
  width: 20px;
  height: 50px;
`;

type PanelDataProvider = {
  cardFetcher: CardFetcher;
};

const dataProvider: PanelDataProvider = {
  cardFetcher: CardFetcherImplementation
};

const Panel = () => {
  useCards(dataProvider.cardFetcher);
  const closePanel = () => {
    mediator.trigger('communication-channel:panel:close');
  };

  return (
    <>
      <Header>
        <Title>{__('akeneo_communication_channel.panel.title')}</Title>
        <CloseButton title={__('akeneo_communication_channel.panel.button.close')} onClick={closePanel} />
      </Header>
    </>
  );
};

export {Panel};
