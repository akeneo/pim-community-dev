import React from 'react';
import styled from 'styled-components';
import {useTranslate, useMediator} from '@akeneo-pim-community/legacy-bridge';
import {useCards} from './../../hooks/useCards';
import {CardFetcher} from './../../fetcher/card';
import {useCampaign} from './../../hooks/useCampaign';
import {CampaignFetcher} from './../../fetcher/campaign';
import {HeaderPanel} from './../../components/panel/Header';
import {CardComponent} from './../../components/panel/card';
import {Card} from './../../models/card';

const ListCard = styled.ul`
  margin-top: 107px;
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
  const __ = useTranslate();
  const mediator = useMediator();
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
