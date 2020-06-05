import React from 'react';
import styled from 'styled-components';
import {useCards} from 'akeneocommunicationchannel/hooks/useCards';
import {CardFetcher} from 'akeneocommunicationchannel/fetcher/card';
import {useCampaign} from 'akeneocommunicationchannel/hooks/useCampaign';
import {CampaignFetcher} from 'akeneocommunicationchannel/fetcher/campaign';
import {HeaderPanel} from 'akeneocommunicationchannel/components/panel/Header';
import {CardComponent} from 'akeneocommunicationchannel/components/panel/card';
import {Card} from 'akeneocommunicationchannel/models/card';

const mediator = require('oro/mediator');
const __ = require('oro/translator');

const ListCard = styled.ul`
  margin-top: 88px;
  margin-left: 30px;
`;

type PanelDataProvider = {
  cardFetcher: CardFetcher;
  campaignFetcher: CampaignFetcher;
};

type PanelProps = {
  dataProvider: PanelDataProvider
};

const Panel = ({dataProvider}: PanelProps): JSX.Element => {
  const {cards} = useCards(dataProvider.cardFetcher);
  const {campaign} = useCampaign(dataProvider.campaignFetcher);
  const closePanel = () => {
    mediator.trigger('communication-channel:panel:close');
  };

  return (
    <>
      <HeaderPanel title={__('akeneo_communication_channel.panel.title')} onClickCloseButton={closePanel} />
      {null !== cards && (
        <ListCard>
          {cards.map((card: Card, index: number): JSX.Element => <CardComponent card={card} key={index} campaign={campaign} />)}
        </ListCard>
      )}
    </>
  );
};

export {Panel, PanelDataProvider};
