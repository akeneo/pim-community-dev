import React, {useRef, useEffect, useCallback} from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AnnouncementComponent, EmptyAnnouncementList} from './announcement';
import {Announcement} from '../../models/announcement';
import {useInfiniteScroll} from '../../hooks/useInfiniteScroll';
import {fetchAnnouncements} from '../../fetcher/announcementFetcher';
import {useHasNewAnnouncements} from '../../hooks/useHasNewAnnouncements';
import {useAddViewedAnnouncements} from '../../hooks/useAddViewedAnnouncements';

const Container = styled.ul`
  margin: 30px 30px 0 30px;
`;

type ListAnnouncementProps = {
  campaign: string;
  panelIsClosed: boolean;
};

const AnnouncementList = ({campaign, panelIsClosed}: ListAnnouncementProps) => {
  const __ = useTranslate();
  const containerRef = useRef<HTMLUListElement | null>(null);
  const scrollableElement = null !== containerRef.current ? containerRef.current.parentElement : null;
  const [announcementResponse, handleFetchingResults] = useInfiniteScroll(fetchAnnouncements, scrollableElement);
  const handleHasNewAnnouncements = useHasNewAnnouncements();
  const handleAddViewedAnnouncements = useAddViewedAnnouncements();

  useEffect(() => {
    handleHasNewAnnouncements();
  }, []);

  const updateNewAnnouncements = useCallback(async () => {
    const newAnnouncements = announcementResponse.items.filter((item: Announcement) => item.tags.includes('new'));
    if (newAnnouncements.length > 0) {
      await handleAddViewedAnnouncements(newAnnouncements);
      await handleHasNewAnnouncements();
      await handleFetchingResults(null);
    }
  }, [announcementResponse.items]);

  useEffect(() => {
    if (panelIsClosed) {
      updateNewAnnouncements();
      if (null !== scrollableElement) {
        /* istanbul ignore next: can't simulate a scrollable element in the AnnouncementList.unit.tsx */
        scrollableElement.scrollTop = 0;
      }
    }
  }, [panelIsClosed]);

  if (announcementResponse.hasError) {
    return <EmptyAnnouncementList text={__('akeneo_communication_channel.panel.list.error')} />;
  }

  if (announcementResponse.items.length === 0) {
    return <EmptyAnnouncementList text={__('akeneo_communication_channel.panel.list.empty')} />;
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

export {AnnouncementList};
