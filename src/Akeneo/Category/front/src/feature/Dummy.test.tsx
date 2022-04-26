import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {Dummy} from './Dummy';

test('it renders column details', async () => {
  renderWithProviders(<Dummy />);

  expect(screen.getByText('Hello world')).toBeInTheDocument();
});
