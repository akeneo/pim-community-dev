import React from 'react';
import { render } from '@testing-library/react';
import App from './App';

test('renders without error', () => {
  render(<App />);
});

test('renders the expected elements', () => {
  const { baseElement } = render(<App />);
  expect(baseElement).toMatchSnapshot();
});
