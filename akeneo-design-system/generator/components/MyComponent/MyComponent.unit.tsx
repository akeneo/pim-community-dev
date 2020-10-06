import React from 'react';
import {MyComponent} from './MyComponent';
import {render} from '../../storybook/test-util';
import '@testing-library/jest-dom/extend-expect';

test('it renders its children properly', () => {
  const {getByText} = render(<MyComponent>MyComponent content</MyComponent>);

  expect(getByText('MyComponent content')).toBeInTheDocument();
});
