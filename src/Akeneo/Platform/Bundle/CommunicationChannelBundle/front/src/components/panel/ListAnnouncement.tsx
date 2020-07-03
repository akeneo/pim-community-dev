import React, {useRef} from 'react';
import styled from 'styled-components';
import {useTranslate, useMediator} from '@akeneo-pim-community/legacy-bridge';
import {AnnouncementComponent, EmptyAnnouncementList} from './announcement';
import {Announcement} from './../../models/announcement';
import {useInfiniteScroll} from '../../hooks/useInfiniteScroll';
import {fetchAnnouncements} from '../../fetcher/announcementFetcher';
import {useHasNewAnnouncements} from '../../hooks/useHasNewAnnouncements';

const Container = styled.ul`
  margin: 30px 30px 0 30px;
`;

type ListAnnouncementProps = {
  campaign: string;
};

const ListAnnouncement = ({campaign}: ListAnnouncementProps) => {
  const __ = useTranslate();
  const mediator = useMediator();
  const containerRef = useRef<HTMLUListElement | null>(null);
  const scrollableElement = null !== containerRef.current ? containerRef.current.parentElement : null;
  const announcementResponse = useInfiniteScroll(fetchAnnouncements, scrollableElement);
  const hasNewAnnouncements = useHasNewAnnouncements();
  if (hasNewAnnouncements) {
    mediator.trigger('communication-channel:announcements:new');
  }

  if (announcementResponse.hasError) {
    return <EmptyAnnouncementList text={__('akeneo_communication_channel.panel.list.error')} />;
  }

  return (
    <Container ref={containerRef}>
      {announcementResponse.items.map(
        (announcement: Announcement, index: number): JSX.Element => (
          <AnnouncementComponent announcement={announcement} key={index} campaign={campaign} />
        )
      )}
    </Container>
  );
};

export {ListAnnouncement};
