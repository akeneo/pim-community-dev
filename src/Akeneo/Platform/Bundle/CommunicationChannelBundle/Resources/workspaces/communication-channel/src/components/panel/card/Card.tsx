import React from 'react';
import styled from 'styled-components';
import {Card} from './../../../models/card';
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

type CardProps = {
  card: Card;
  campaign: string | null;
};

const CardComponent = ({card, campaign}: CardProps): JSX.Element => {
  return (
    <Container>
      <LineContainer>
        {card.tags.map((tag: string, index: number): JSX.Element => <TagComponent key={index} tag={tag} />)}
        <Date>{card.startDate}</Date>
      </LineContainer>
      <Title tags={card.tags} title={card.title} />
      <Description tags={card.tags} description={card.description} />
      {card.img && 
        <Image src={card.img} alt={card.altImg} />
      }
      <LineContainer>
        <LinkComponent 
          baseUrl={card.link}
          campaign={campaign}
        />
      </LineContainer>
    </Container>
  );
};

export {CardComponent};
