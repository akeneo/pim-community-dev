import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useAnnouncements} from './../../hooks/useAnnouncements';
import {AnnouncementComponent, EmptyAnnouncementList} from './announcement';
import {Announcement} from './../../models/announcement';

const Container = styled.ul`
  margin: 30px 30px 0 30px;
`;

type ListAnnouncementProps = {
  campaign: string;
};

const ListAnnouncement = ({campaign}: ListAnnouncementProps) => {
  const __ = useTranslate();
  const announcementResponse = useAnnouncements();

  if (announcementResponse.hasError) {
    return <EmptyAnnouncementList text={__('akeneo_communication_channel.panel.list.error')} />;
  }

  return (
    <Container>
      {announcementResponse.data.map(
        (announcement: Announcement, index: number): JSX.Element => (
          <AnnouncementComponent announcement={announcement} key={index} campaign={campaign} />
        )
      )}
    </Container>
  );
};

export {ListAnnouncement};
