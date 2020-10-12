import React from 'react';
import {ProgressBar} from './ProgressBar';
import {render} from '../../storybook/test-util';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';

test('it renders a progress bar', () => {
  render(<ProgressBar percent={50} color={'#f9b53f'} />);

  expect(screen.getByRole('progressbar')).toBeInTheDocument();
});

test('it renders a progress bar with sanitized percent', () => {
  const {rerender} = render(<ProgressBar percent={-1} color={'#f9b53f'} />);
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '0');

  rerender(<ProgressBar percent={0} color={'#f9b53f'} />);
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '0');

  rerender(<ProgressBar percent={50} color={'#f9b53f'} />);
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '50');

  rerender(<ProgressBar percent={100} color={'#f9b53f'} />);
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '100');

  rerender(<ProgressBar percent={101} color={'#f9b53f'} />);
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '100');
});

test('it renders a large progress bar', () => {
  render(<ProgressBar height="large" percent={50} color={'#f9b53f'} />);

  expect(screen.getByRole('progressbar')).toBeInTheDocument();
});

test('it renders a progress bar with title', () => {
  render(<ProgressBar percent={50} title="Progress bar title" color={'#f9b53f'} />);

  expect(screen.getByText('Progress bar title')).toBeInTheDocument();
});

test('it renders a progress bar with progress label', () => {
  render(<ProgressBar percent={50} progressLabel="Progress label" color={'#f9b53f'} />);

  expect(screen.getByText('Progress label')).toBeInTheDocument();
});
