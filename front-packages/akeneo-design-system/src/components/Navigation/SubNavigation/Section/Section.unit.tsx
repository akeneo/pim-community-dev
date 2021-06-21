import React from 'react';
import {render, screen} from '../../../../storybook/test-util';
import {Section} from './Section';
import {Item} from '../Item/Item';

test('it renders its children', () => {
  render(
    <Section>
      <Item>Content</Item>
    </Section>
  );
  expect(screen.getByText('Content')).toBeInTheDocument();
});
