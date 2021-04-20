import React from 'react';
import {renderWithProviders} from '../utils';
import {FullScreenError} from '../../../../src/components';

test('It display a client error', () => {
  const {getByText} = renderWithProviders(
    <FullScreenError code={400} message={'Not found'} title={'Oh snap! An error occurred'} />
  );

  expect(getByText('Not found')).toBeInTheDocument();
  expect(getByText('400')).toBeInTheDocument();
  expect(getByText('Oh snap! An error occurred')).toBeInTheDocument();
});

test('It display a server error', () => {
  const {getByText} = renderWithProviders(
    <FullScreenError code={500} message={'Internal error'} title={'Oh snap! An error occurred'} />
  );

  expect(getByText('Internal error')).toBeInTheDocument();
  expect(getByText('500')).toBeInTheDocument();
  expect(getByText('Oh snap! An error occurred')).toBeInTheDocument();
});
