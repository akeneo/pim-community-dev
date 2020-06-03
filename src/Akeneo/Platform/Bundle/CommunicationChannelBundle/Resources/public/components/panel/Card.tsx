import React from 'react';
import styled from 'styled-components';
import {Card} from 'akeneocommunicationchannel/models/card';
import {Tag} from 'akeneocommunicationchannel/components/panel/Tag';

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

const Link = styled.a`
  border-radius: 16px;
  border: 1px solid #a1a9b7;
  height: 24px;
  padding: 4px 10px;
  line-height: 14px;
  margin-left: auto;
  color: #768096;
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
  card: Card
};

const CardComponent = ({card}: CardProps) => {
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
        <Link href={card.link} target="_blank">Read More</Link>
      </LineContainer>
    </Container>
  );
};

export = CardComponent;
