import React from 'react';
import {VictoryLine, VictoryChart, VictoryAxis, VictoryTheme, VictoryGroup, VictoryBar, VictoryLegend} from 'victory';
import {SectionTitle, useTheme} from 'akeneo-design-system';
import styled from 'styled-components';

const Container = styled.div`
  display: grid;
  flex-direction: row;
  height: calc(100vh - 278px);
  gap: 40px;
`;

const LeftContainer = styled.div`
  grid-column: 1;
`;
const RightContainer = styled.div`
  grid-column: 2;
`;

const Dummy = () => {
  const theme = useTheme();

  const data = [
    {month: 'Jan', time_to_enrich_in_days: 8},
    {month: 'Feb', time_to_enrich_in_days: 8},
    {month: 'Mar', time_to_enrich_in_days: 7},
    {month: 'Ap', time_to_enrich_in_days: 5},
    {month: 'May', time_to_enrich_in_days: 6},
    {month: 'Jun', time_to_enrich_in_days: 5},
  ];

  const channel_data = {
    'e-commerce': [
      {month: 'Jan', time_to_enrich_in_days: 8},
      {month: 'Feb', time_to_enrich_in_days: 8},
      {month: 'Mar', time_to_enrich_in_days: 7},
      {month: 'Apr', time_to_enrich_in_days: 5},
      {month: 'May', time_to_enrich_in_days: 6},
      {month: 'Jun', time_to_enrich_in_days: 5},
    ],
    mobile: [
      {month: 'Jan', time_to_enrich_in_days: 5},
      {month: 'Feb', time_to_enrich_in_days: 4},
      {month: 'Mar', time_to_enrich_in_days: 9},
      {month: 'Apr', time_to_enrich_in_days: 5},
      {month: 'May', time_to_enrich_in_days: 7},
      {month: 'Jun', time_to_enrich_in_days: 5},
    ],
    print: [
      {month: 'Jan', time_to_enrich_in_days: 9},
      {month: 'Feb', time_to_enrich_in_days: 8},
      {month: 'Mar', time_to_enrich_in_days: 7},
      {month: 'Apr', time_to_enrich_in_days: 1},
      {month: 'May', time_to_enrich_in_days: 3},
      {month: 'Jun', time_to_enrich_in_days: 2},
    ],
  };
  const channel_colors = [theme.color.blue40, theme.color.purple40, theme.color.yellow40];

  return (
    <Container>
      <LeftContainer>
        <SectionTitle>
          <SectionTitle.Title level="secondary">Global Time to enrich in days</SectionTitle.Title>
        </SectionTitle>
        <VictoryChart domainPadding={10} theme={VictoryTheme.material} domain={{y: [0, 10]}}>
          <VictoryAxis />
          <VictoryAxis dependentAxis />
          <VictoryLine data={data} x="month" y="time_to_enrich_in_days" style={{data: {stroke: channel_colors[0]}}} />
        </VictoryChart>
      </LeftContainer>
      <RightContainer>
        <SectionTitle>
          <SectionTitle.Title level="secondary">Channel Time to enrich in days</SectionTitle.Title>
        </SectionTitle>
        <VictoryChart domainPadding={10} theme={VictoryTheme.material} domain={{y: [0, 10]}}>
          <VictoryLegend
            x={75}
            y={50}
            orientation="horizontal"
            gutter={10}
            data={Object.keys(channel_data).map((name, index) => ({name, symbol: {fill: channel_colors[index]}}))}
          />
          <VictoryGroup colorScale="qualitative" offset={10}>
            {Object.values(channel_data).map((data, index) => (
              <VictoryBar
                key={index}
                data={data}
                style={{data: {fill: channel_colors[index]}}}
                x="month"
                y="time_to_enrich_in_days"
                animate={{duration: 500}}
              />
            ))}
          </VictoryGroup>
        </VictoryChart>
      </RightContainer>
    </Container>
  );
};

export {Dummy};
