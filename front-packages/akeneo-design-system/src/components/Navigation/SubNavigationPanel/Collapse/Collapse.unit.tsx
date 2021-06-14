import React from 'react';
import {render, screen} from '../../../../storybook/test-util';
import {Collapse} from './Collapse';

test('it renders its children', () => {
  render(<Collapse>Content</Collapse>);
  expect(screen.getByText('Content')).toBeInTheDocument();
});
