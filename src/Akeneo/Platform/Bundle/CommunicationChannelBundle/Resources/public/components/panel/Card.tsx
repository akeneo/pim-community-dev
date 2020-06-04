import React from 'react';
import styled from 'styled-components';
import {Card} from 'akeneocommunicationchannel/models/card';
import Tag from 'akeneocommunicationchannel/components/panel/Tag';
import Link from 'akeneocommunicationchannel/components/panel/Link';

const Container = styled.div`
  margin: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #c7cbd4;
`;

const Title = styled.div`
  font-size: 17px;
  height: 21px;
  color: #a974c7;
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
  font-size: 13px;
  text-align: right;
  flex-grow: 1;
`;

type CardProps = {
  card: Card;
  campaign: string | null;
};

const CardComponent = ({card, campaign}: CardProps) => {
  return (
    <Container>
      <LineContainer>
        {card.tags.map((tag, index) => {
          return <Tag key={index} tag={tag}>{tag}</Tag>
        })}
        <Date>{card.date}</Date>
      </LineContainer>
      <Title>{card.title}</Title>
      <Description>{card.description.split('.')[0]}.</Description>
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

export = CardComponent;
