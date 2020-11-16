import React from 'react';
import {ProgressBar} from './ProgressBar';
import {render} from '../../storybook/test-util';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';

test('it renders a progress bar', () => {
  render(<ProgressBar percent={50} level="primary" />);

  expect(screen.getByRole('progressbar')).toBeInTheDocument();
});

test('it renders a progress bar with sanitized percent', () => {
  const {rerender} = render(<ProgressBar percent={-1} level="primary" />);
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '0');

  rerender(<ProgressBar percent={0} level="primary" />);
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '0');

  rerender(<ProgressBar percent={50} level="primary" />);
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '50');

  rerender(<ProgressBar percent={100} level="primary" />);
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '100');

  rerender(<ProgressBar percent={101} level="primary" />);
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-valuenow', '100');
});

test('it render a progress bar with indeterminate progress', () => {
  render(<ProgressBar percent="indeterminate" level="primary" />);
  expect(screen.getByRole('progressbar')).not.toHaveAttribute('aria-valuemin');
  expect(screen.getByRole('progressbar')).not.toHaveAttribute('aria-valuenow');
  expect(screen.getByRole('progressbar')).not.toHaveAttribute('aria-valuemax');
});

test('it render a indeterminate progress when percent is not a number', () => {
  render(<ProgressBar percent={NaN} level="primary" />);
  expect(screen.getByRole('progressbar')).not.toHaveAttribute('aria-valuemin');
  expect(screen.getByRole('progressbar')).not.toHaveAttribute('aria-valuenow');
  expect(screen.getByRole('progressbar')).not.toHaveAttribute('aria-valuemax');
});

test.each<'large' | 'small'>(['large', 'small'])('it renders a %s progress bar', (size: 'large' | 'small') => {
  render(<ProgressBar size={size} percent={50} level="primary" />);

  expect(screen.getByRole('progressbar')).toBeInTheDocument();
});

test('it renders a progress bar with title', () => {
  render(<ProgressBar percent={50} title="Progress bar title" level="primary" />);

  expect(screen.getByText('Progress bar title')).toBeInTheDocument();
  expect(screen.getByRole('progressbar')).toHaveAttribute('aria-labelledby');
});

test('it renders a progress bar with progress label', () => {
  render(<ProgressBar percent={50} progressLabel="Progress label" level="primary" />);

  expect(screen.getByText('Progress label')).toBeInTheDocument();
});

test('ProgressBar supports forwardRef', () => {
  const ref = {current: null};

  render(<ProgressBar level="primary" percent={50} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Button supports ...rest props', () => {
  const {container} = render(<ProgressBar level="primary" percent={50} data-my-attribute="my_value" />);
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
