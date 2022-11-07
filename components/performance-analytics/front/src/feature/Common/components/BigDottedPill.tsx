import React from 'react';
import styled from 'styled-components';
import {getColor} from 'akeneo-design-system/lib/theme/theme';

const DottedPill = styled.div`
  width: 16px;
  height: 16px;
  border-radius: 50%;
  display: inline-block;
  margin-left: 5px;
  border: 2px solid ${getColor('blue', 100)};
  border-style: dotted;
  vertical-align: middle;
`;

const BigDottedPill = () => {
  return <DottedPill />;
};

export {BigDottedPill};
