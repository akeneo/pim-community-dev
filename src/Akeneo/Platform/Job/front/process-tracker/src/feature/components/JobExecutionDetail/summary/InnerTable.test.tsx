import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {InnerTable} from './InnerTable';

test('it displays an inner table', () => {
  const content = {
    key: 'key',
    value: {sku: 'example'},
  };
  renderWithProviders(<InnerTable content={content} />);

  expect(screen.getByText('{"sku":"example"}')).toBeInTheDocument();
});
