import React from 'react';
import {screen} from '@testing-library/react';
import {Section} from '../page';
import {renderWithProviders} from '../../tests/utils';

test('it renders its content', () => {
  renderWithProviders(<Section>Section content</Section>);

  expect(screen.getByText('Section content')).toBeInTheDocument();
});
