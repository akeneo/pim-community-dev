import React from 'react';
import {Dropdown} from './Dropdown';
import {Link, Image} from '../../components';
import {render, screen, fireEvent} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Dropdown>
      <Dropdown.Action>Dropdown</Dropdown.Action>
      <Dropdown.Overlay position="down" onClose={() => {}}>
        <Dropdown.Header>
          <Dropdown.Title>Elements</Dropdown.Title>
        </Dropdown.Header>
        <Dropdown.ItemCollection>
          <Link>
            <Dropdown.Item>Item 1</Dropdown.Item>
          </Link>
          <Dropdown.Item>Item 2</Dropdown.Item>
          <Dropdown.Item>Item 3</Dropdown.Item>
          <Dropdown.Item>Item 4</Dropdown.Item>
        </Dropdown.ItemCollection>
      </Dropdown.Overlay>
    </Dropdown>
  );

  expect(screen.getByText('Dropdown')).toBeInTheDocument();
  expect(screen.getByText('Item 1')).toBeInTheDocument();
  expect(screen.getByText('Elements')).toBeInTheDocument();
});
test('it renders selectable item', () => {
  const onChange = jest.fn();

  render(
    <Dropdown>
      <Dropdown.Action>Dropdown</Dropdown.Action>
      <Dropdown.Overlay position="down" onClose={() => {}}>
        <Dropdown.Header>
          <Dropdown.Title>Elements</Dropdown.Title>
        </Dropdown.Header>
        <Dropdown.ItemCollection>
          <Dropdown.SelectableItem selected={false} onChange={onChange}>
            Selectable Item
          </Dropdown.SelectableItem>
        </Dropdown.ItemCollection>
      </Dropdown.Overlay>
    </Dropdown>
  );

  fireEvent.click(screen.getByText('Selectable Item'));
  expect(onChange).toBeCalledTimes(1);
});
test('it renders Image item', () => {
  render(
    <Dropdown>
      <Dropdown.Action>Dropdown</Dropdown.Action>
      <Dropdown.Overlay position="down" onClose={() => {}}>
        <Dropdown.Header>
          <Dropdown.Title>Elements</Dropdown.Title>
        </Dropdown.Header>
        <Dropdown.ItemCollection>
          <Dropdown.ImageItem>
            <Image src="https://picsum.photos/seed/akeneo/200/140" alt="An image" />
            Item with Image
          </Dropdown.ImageItem>
          <Dropdown.Item>Simple Item</Dropdown.Item>
        </Dropdown.ItemCollection>
      </Dropdown.Overlay>
    </Dropdown>
  );

  expect(screen.getByAltText('An image')).toHaveProperty('width', 34);
  expect(screen.getByAltText('An image')).toHaveProperty('height', 34);
});

test('Dropdown supports ...rest props', () => {
  render(<Dropdown data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
