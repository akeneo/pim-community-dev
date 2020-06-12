import React from 'react';
import styled from 'styled-components';
import {Announcement} from '../../../models/announcement';
import {TagComponent} from './Tag';
import {Title} from './Title';
import {Description} from './Description';
import {LinkComponent} from './Link';
import {AkeneoThemedProps} from '@akeneo-pim-community/shared';

const Container = styled.li`
  margin: 20px 0;
  padding-bottom: 20px;
  width: 340px;

  &:not(:last-child) {
    border-bottom: 1px solid ${({theme}: AkeneoThemedProps) => theme.color.grey80};
  }
`;

const Image = styled.img`
  width: 340px;
  object-fit: contain;
  min-height: 200px;
  border: 1px solid ${({theme}: AkeneoThemedProps) => theme.color.grey60};
`;

const LineContainer = styled.div`
  display: flex;
`;

const Date = styled.div`
  color: ${({theme}: AkeneoThemedProps) => theme.color.grey100};
  font-size: ${({theme}: AkeneoThemedProps) => theme.fontSize.default};
  text-align: right;
  flex-grow: 1;
`;

type AnnouncementProps = {
  announcement: Announcement;
  campaign: string | null;
};

const AnnouncementComponent = ({announcement, campaign}: AnnouncementProps): JSX.Element => {
  return (
    <Container>
      <LineContainer>
        {announcement.tags.map((tag: string, index: number): JSX.Element => <TagComponent key={index} tag={tag} />)}
        <Date>{announcement.startDate}</Date>
      </LineContainer>
      <Title tags={announcement.tags} title={announcement.title} />
      <Description tags={announcement.tags} description={announcement.description} />
      {announcement.img &&
        <Image src={announcement.img} alt={announcement.altImg} />
      }
      <LineContainer>
        <LinkComponent
          baseUrl={announcement.link}
          title={announcement.title}
          campaign={campaign}
        />
      </LineContainer>
    </Container>
  );
};

export {AnnouncementComponent};
