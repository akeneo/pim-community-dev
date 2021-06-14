import React from 'react';
import {fireEvent, render, screen} from '../../../../storybook/test-util';
import {Item} from '../Item/Item';
import {DropdownItem} from './DropdownItem';
import {Tag} from '../../../Tags/Tags';

test('it is an anchor', () => {
  render(
    <DropdownItem>
      <Item href="https://my-site.test/">Content</Item>
    </DropdownItem>
  );
  expect(screen.getByText('Content').closest('a')).toHaveAttribute('href', 'https://my-site.test/');
});

test('it renders its children but ignore Tag component', () => {
  render(
    <DropdownItem>
      <Item href="https://my-site.test/">
        Content <Tag tint="blue">New</Tag>
      </Item>
    </DropdownItem>
  );
  expect(screen.queryByText('New')).toBeNull();
});

test('it supports forwardRef', () => {
  const ref = {current: null};
  render(
    <DropdownItem ref={ref}>
      <Item href="https://my-site.test/">Content</Item>
    </DropdownItem>
  );
  expect(ref.current).not.toBe(null);
});

test('it supports ...rest props', () => {
  render(
    <DropdownItem data-testid="my_value">
      <Item href="https://my-site.test/">Content</Item>
    </DropdownItem>
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it doesnt set the attribute href when it is disabled', () => {
  render(
    <DropdownItem>
      <Item href="https://my-site.test/" disabled>
        Content
      </Item>
    </DropdownItem>
  );
  expect(screen.getByText('Content').closest('a')).not.toHaveAttribute('href', 'https://my-site.test/');
});

test('it triggers onClick when it is clicked', () => {
  const onClick = jest.fn();
  render(
    <DropdownItem>
      <Item onClick={onClick}>Content</Item>
    </DropdownItem>
  );
  fireEvent.click(screen.getByText('Content'));
  expect(onClick).toBeCalled();
});

test('it doesnt trigger onClick when it is disabled', () => {
  const onClick = jest.fn();
  render(
    <DropdownItem>
      <Item onClick={onClick} disabled>
        Content
      </Item>
    </DropdownItem>
  );
  fireEvent.click(screen.getByText('Content'));
  expect(onClick).not.toBeCalled();
});
