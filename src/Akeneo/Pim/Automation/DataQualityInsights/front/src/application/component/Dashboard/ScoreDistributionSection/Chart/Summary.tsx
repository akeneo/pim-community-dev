import React, {FC} from 'react';
import {QualityScore} from '../../../QualityScore';
import styled, {css} from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type ReversibleProps = {
  flip?: boolean;
};
type Props = ReversibleProps & {
  totalA: number;
  totalB: number;
  totalC: number;
  totalD: number;
  totalE: number;
  averageScore: string | null;
};

const Container = styled.div`
  position: relative;
  width: 190px;
  height: auto;
`;
const Arrow = styled.div<ReversibleProps>`
  position: absolute;
  border-top: 10px solid transparent;
  border-bottom: 10px solid transparent;
  border-right: 10px solid;
  margin-top: 30px;
  color: ${({theme}) => theme.color.white};

  ${({flip}) =>
    flip &&
    css`
      border-left: 10px solid;
      border-right: none;
      right: 0;
    `}
`;

const ContentBox = styled.div`
  background-color: ${({theme}) => theme.color.white};
  border: none;
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  width: 170px;
  height: auto;
  margin: 5px 10px; //Take account the size of the shadow and the size of the arrow
`;

const Content = styled.div`
  padding-left: 20px;
  padding-right: 20px;
  height: auto;
  width: 100%;
`;
const Title = styled.div`
  color: ${({theme}) => theme.color.purple100};
  font-size: 11px;
  font-weight: normal;
  height: 44px;
  text-transform: uppercase;
  width: 100%;
  border-bottom: solid 1px ${({theme}) => theme.color.purple100};
  line-height: 44px;
`;

const List = styled.ul`
  height: 174px;
  margin-top: 20px;
`;

const ListItem = styled.li`
  height: 34px;
`;

const ScoreValue = styled.span`
  color: ${({theme}) => theme.color.grey120};
  font-size: 13px;
  font-weight: normal;
  margin-left: 10px;
`;

const Footer = styled.div`
  height: auto;
  width: auto;
  border-top: solid 1px ${({theme}) => theme.color.grey60};
  line-height: 44px;
  display: flex;
  padding-top: 13px;
  padding-bottom: 13px;
`;

const AverageScore = styled(QualityScore)`
  line-height: 20px;
  margin-top: -3px;
`;

const AverageScoreMessage = styled.span`
  color: ${({theme}) => theme.color.grey120};
  font-size: 13px;
  font-weight: normal;
  line-height: 17px;
  max-width: min-content;
  min-width: 92px;
  padding-left: 10px;
  white-space: normal;
`;

const Summary: FC<Props> = ({totalA, totalB, totalC, totalD, totalE, averageScore, flip = false}) => {
  const translate = useTranslate();

  return (
    <Container>
      <Arrow flip={flip} />
      <ContentBox>
        <Content>
          <Title>Distribution</Title>
          <List>
            <ListItem>
              <QualityScore score={'A'} />
              <ScoreValue>{Math.round(totalA)}%</ScoreValue>
            </ListItem>
            <ListItem>
              <QualityScore score={'B'} />
              <ScoreValue>{Math.round(totalB)}%</ScoreValue>
            </ListItem>
            <ListItem>
              <QualityScore score={'C'} />
              <ScoreValue>{Math.round(totalC)}%</ScoreValue>
            </ListItem>
            <ListItem>
              <QualityScore score={'D'} />
              <ScoreValue>{Math.round(totalD)}%</ScoreValue>
            </ListItem>
            <ListItem>
              <QualityScore score={'E'} />
              <ScoreValue>{Math.round(totalE)}%</ScoreValue>
            </ListItem>
          </List>
          <Footer>
            <AverageScore score={averageScore} />
            <AverageScoreMessage>
              {translate(`akeneo_data_quality_insights.dqi_dashboard.average_grade`)}
            </AverageScoreMessage>
          </Footer>
        </Content>
      </ContentBox>
    </Container>
  );
};

export {Summary};
