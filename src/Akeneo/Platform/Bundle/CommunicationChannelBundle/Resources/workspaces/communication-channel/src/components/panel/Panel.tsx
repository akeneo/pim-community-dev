import React from 'react';
import styled from 'styled-components';
import {useTranslate, useMediator} from '@akeneo-pim-community/legacy-bridge';
import {useAnnouncements} from './../../hooks/useAnnouncements';
import {usePimVersion} from '../../hooks/usePimVersion';
import {HeaderPanel} from './../../components/panel/Header';
import {AnnouncementComponent, EmptyAnnouncementList} from './announcement';
import {Announcement} from './../../models/announcement';
import {formatCampaign} from '../../tools/formatCampaign';

const ListAnnouncement = styled.ul`
  margin: 74px 30px 0 30px;
`;

const Panel = (): JSX.Element => {
  const __ = useTranslate();
  const mediator = useMediator();
  const {pimVersion} = usePimVersion();
  const {announcements} = useAnnouncements();
  const campaign = (null !== pimVersion) ? formatCampaign(pimVersion.edition, pimVersion.version) : '';

  const closePanel = () => {
    mediator.trigger('communication-channel:panel:close');
  };
  const isSerenity = null !== campaign && 'serenity' === campaign.toLowerCase();

  return (
    <>
      <HeaderPanel title={__('akeneo_communication_channel.panel.title')} onClickCloseButton={closePanel} />
      {null !== announcements && isSerenity && (
        <ListAnnouncement>
          {announcements.map((announcement: Announcement, index: number): JSX.Element =>
            <AnnouncementComponent announcement={announcement} key={index} campaign={campaign} />)
          }
        </ListAnnouncement>
      )}
      {!isSerenity && (
        <EmptyAnnouncementList text={__('akeneo_communication_channel.panel.list.empty')} />
      )}
    </>
  );
};

export {Panel, PanelDataProvider};
