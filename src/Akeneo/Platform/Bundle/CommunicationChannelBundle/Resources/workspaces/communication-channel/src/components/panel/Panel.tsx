import React from 'react';
import styled from 'styled-components';
import {useTranslate, useMediator} from '@akeneo-pim-community/legacy-bridge';
import {useAnnouncements} from './../../hooks/useAnnouncements';
import {AnnouncementFetcher} from './../../fetcher/announcement.type';
import {useCampaign} from './../../hooks/useCampaign';
import {CampaignFetcher} from './../../fetcher/campaign.type';
import {HeaderPanel} from './../../components/panel/Header';
import {AnnouncementComponent} from './announcement';
import {Announcement} from './../../models/announcement';

const ListCard = styled.ul`
  margin-top: 88px;
  margin-left: 30px;
`;

type PanelDataProvider = {
  announcementFetcher: AnnouncementFetcher;
  campaignFetcher: CampaignFetcher;
};

type PanelProps = {
  dataProvider: PanelDataProvider
};

const Panel = ({dataProvider}: PanelProps): JSX.Element => {
  const __ = useTranslate();
  const mediator = useMediator();
  const {announcements} = useAnnouncements(dataProvider.announcementFetcher);
  const {campaign} = useCampaign(dataProvider.campaignFetcher);
  const closePanel = () => {
    mediator.trigger('communication-channel:panel:close');
  };

  return (
    <>
      <HeaderPanel title={__('akeneo_communication_channel.panel.title')} onClickCloseButton={closePanel} />
      {null !== announcements && (
        <ListCard>
          {announcements.map((announcement: Announcement, index: number): JSX.Element =>
            <AnnouncementComponent announcement={announcement} key={index} campaign={campaign} />)
          }
        </ListCard>
      )}
    </>
  );
};

export {Panel, PanelDataProvider};
