import React from 'react';
import {MyComponent} from './MyComponent';
import {render} from '../../storybook/test-util';
import '@testing-library/jest-dom/extend-expect';
import {screen} from '@testing-library/react';

test('it renders its children properly', () => {
  render(<MyComponent>MyComponent content</MyComponent>);

  expect(screen.getByText('MyComponent content')).toBeInTheDocument();
});
