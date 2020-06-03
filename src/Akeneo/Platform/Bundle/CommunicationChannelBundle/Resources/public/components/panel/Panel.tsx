import React from 'react';
import styled from 'styled-components';
import {useCards} from 'akeneocommunicationchannel/hooks/useCards';
import {CardFetcherImplementation, CardFetcher} from 'akeneocommunicationchannel/fetcher/card';
import HeaderPanel from 'akeneocommunicationchannel/components/panel/Header';
import CardComponent from 'akeneocommunicationchannel/components/panel/Card';

const mediator = require('oro/mediator');
const __ = require('oro/translator');

const ListCard = styled.div`
`;

type PanelDataProvider = {
  cardFetcher: CardFetcher;
};

const dataProvider: PanelDataProvider = {
  cardFetcher: CardFetcherImplementation
};

const Panel = () => {
  const {cards} = useCards(dataProvider.cardFetcher);
  const closePanel = () => {
    mediator.trigger('communication-channel:panel:close');
  };

  return (
    <>
      <HeaderPanel title={__('akeneo_communication_channel.panel.title')} onClickCloseButton={closePanel} />
      <ListCard>
        {null !== cards && 
          cards.map((card, index) => {
            return <CardComponent card={card} key={index} />
          })
        }
      </ListCard>
    </>
  );
};

export {Panel};
