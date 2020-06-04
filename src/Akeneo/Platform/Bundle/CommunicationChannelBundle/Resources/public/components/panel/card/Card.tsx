import React from 'react';
import styled from 'styled-components';
import {Card} from 'akeneocommunicationchannel/models/card';
import Tag from 'akeneocommunicationchannel/components/panel/card/Tag';
import Link from 'akeneocommunicationchannel/components/panel/card/Link';
import {AkeneoThemedProps} from '@akeneo-pim-community/shared';

const Container = styled.li`
  margin: 20px;
  padding-bottom: 20px;

  &:not(:last-child) {
    border-bottom: 1px solid #c7cbd4;
  }
`;

const Title = styled.div`
  font-size: ${({theme}: AkeneoThemedProps) => theme.fontSize.bigger};
  height: 21px;
  color: ${({theme}: AkeneoThemedProps) => theme.color.purple80};
  margin: 10px 0;
`;

const Description = styled.p`
`;

const Image = styled.img`
  width: 340px;
  height: 200px;
  object-fit: cover;
  object-position: 20% 10px;
  margin: 20px 0;
`;

const LineContainer = styled.div`
  display: flex;
`;

const Date = styled.div`
  color: #adb2c0;
  font-size: ${({theme}: AkeneoThemedProps) => theme.fontSize.default};
  text-align: right;
  flex-grow: 1;
`;

type CardProps = {
  card: Card;
  campaign: string | null;
};

const CardComponent = ({card, campaign}: CardProps): JSX.Element => {
  const descriptionEllipsed = card.description.split(/(?<=\.)/)[0];

  return (
    <Container>
      <LineContainer>
        {card.tags.map((tag: string, index: number): JSX.Element => <Tag key={index} tag={tag} />)}
        <Date>{card.date}</Date>
      </LineContainer>
      <Title>{card.title}</Title>
      <Description>{descriptionEllipsed}</Description>
      {card.img && 
        <Image src={card.img} />
      }
      <LineContainer>
        <Link 
          baseUrl={card.link}
          campaign={campaign}
        />
      </LineContainer>
    </Container>
  );
};

export {CardComponent};
