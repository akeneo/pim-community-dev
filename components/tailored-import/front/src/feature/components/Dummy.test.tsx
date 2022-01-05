import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {Dummy} from './Dummy';

test('it renders Dummy component', () => {
  renderWithProviders(<Dummy />);

  expect(screen.getByText('Hello Tailored Import')).toBeInTheDocument();
});
