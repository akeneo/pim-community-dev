import React, {FC} from 'react';
import styled, {css} from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type BulletProps = {
  color?: string;
};

const Bullet = styled.span<BulletProps>(
  ({theme, color = 'grey80'}) => css`
    display: inline-block;
    width: 8px;
    height: 8px;
    margin: 0 8px 0 5px;
    border: none;
    border-radius: 8px;
    background: ${() => theme.color[color]};
  `
);

const Item = styled.span`
  height: 13px;
  margin-right: 10px;
  color: ${({theme}) => theme.color.grey140};
  font-size: ${({theme}) => theme.fontSize.small};
`;

const Container = styled.header`
  display: flex;
  flex-direction: row;
  justify-content: flex-end;
  margin: 20px 0 15px;
  line-height: 20px;
  height: 20px;
`;

const Legend: FC = () => {
  const translate = useTranslate();

  return (
    <Container>
      <Item>
        <Bullet color={'green60'} />
        {translate(`akeneo_data_quality_insights.dqi_dashboard.legend.excellent`)}
      </Item>
      <Item>
        <Bullet color={'green100'} />
        {translate(`akeneo_data_quality_insights.dqi_dashboard.legend.good`)}
      </Item>
      <Item>
        <Bullet color={'yellow60'} />
        {translate(`akeneo_data_quality_insights.dqi_dashboard.legend.average`)}
      </Item>
      <Item>
        <Bullet color={'red60'} />
        {translate(`akeneo_data_quality_insights.dqi_dashboard.legend.below_average`)}
      </Item>
      <Item>
        <Bullet color={'red100'} />
        {translate(`akeneo_data_quality_insights.dqi_dashboard.legend.to_improve`)}
      </Item>
    </Container>
  );
};

export {Legend};
