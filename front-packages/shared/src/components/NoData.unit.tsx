import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../tests';
import {NoDataSection, NoDataText, NoDataTitle} from './NoData';

test('it renders its children properly', () => {
  renderWithProviders(
    <NoDataSection>
      <NoDataTitle>My title</NoDataTitle>
      <NoDataText>My text</NoDataText>
    </NoDataSection>
  );

  expect(screen.getByText('My title')).toBeInTheDocument();
  expect(screen.getByText('My text')).toBeInTheDocument();
});
