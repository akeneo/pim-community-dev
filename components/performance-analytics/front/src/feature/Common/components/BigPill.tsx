import React from 'react';
import styled from 'styled-components';
import {getColor} from 'akeneo-design-system/lib/theme/theme';

const BluePill = styled.div`
  width: 16px;
  height: 16px;
  border-radius: 50%;
  display: inline-block;
  vertical-align: middle;
  border: 2px solid ${getColor('blue', 100)};
  background-color: ${getColor('blue', 20)};
`;

const BigPill = () => {
  return <BluePill />;
};

export {BigPill};
