import React from 'react';
import {screen} from '@testing-library/react';
import {PageContent} from './PageContent';
import {renderWithProviders} from '../../tests/utils';

test('it renders its content', () => {
  renderWithProviders(<PageContent>Page content</PageContent>);

  expect(screen.getByText('Page content')).toBeInTheDocument();
});
