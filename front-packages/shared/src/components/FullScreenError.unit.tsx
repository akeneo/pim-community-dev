import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../tests';
import {FullScreenError} from './FullScreenError';

test('It display a client error', () => {
  renderWithProviders(<FullScreenError code={400} message={'Not found'} title={'Oh snap! An error occurred'} />);

  expect(screen.getByText('Not found')).toBeInTheDocument();
  expect(screen.getByText('400')).toBeInTheDocument();
  expect(screen.getByText('Oh snap! An error occurred')).toBeInTheDocument();
});

test('It display a server error', () => {
  renderWithProviders(<FullScreenError code={500} message={'Internal error'} title={'Oh snap! An error occurred'} />);

  expect(screen.getByText('Internal error')).toBeInTheDocument();
  expect(screen.getByText('500')).toBeInTheDocument();
  expect(screen.getByText('Oh snap! An error occurred')).toBeInTheDocument();
});
