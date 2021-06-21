import React from 'react';
import {fireEvent, render, screen} from '../../../../storybook/test-util';
import {Item} from '../Item/Item';
import {Section} from '../Section/Section';
import {Dropdown} from './Dropdown';

test('it opens the dropdown', () => {
  render(
    <Dropdown>
      <Section>
        <Item>Content</Item>
      </Section>
    </Dropdown>
  );
  expect(screen.queryByText('Content')).toBeNull();
  fireEvent.click(screen.getByTitle('Navigation'));
  expect(screen.getByText('Content')).toBeInTheDocument();
});

test('it renders only the SubNavigation.Item(s) of the SubNavigation.Section(s) children', () => {
  render(
    <Dropdown>
      <Item>Ignored Item</Item>
      <Section>
        <Item>First Item</Item>
        <Item>Second Item</Item>
      </Section>
    </Dropdown>
  );
  fireEvent.click(screen.getByTitle('Navigation'));
  expect(screen.queryByText('Ignored Item')).toBeNull();
  expect(screen.getByText('First Item')).toBeInTheDocument();
  expect(screen.getByText('Second Item')).toBeInTheDocument();
});
