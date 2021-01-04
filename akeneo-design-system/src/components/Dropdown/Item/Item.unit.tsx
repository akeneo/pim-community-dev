import React from 'react';
import {Item} from './Item';
import {Image, Link, Checkbox} from '../../../components';
import {render, screen, fireEvent} from '../../../storybook/test-util';
import 'jest-styled-components';

test('It displays an item and add a label wrapper if needed', () => {
  render(
    <>
      <Item>Needs a label wrapper</Item>
      <Item>
        <div>Does not need a wrapper</div>
      </Item>
    </>
  );

  expect(screen.getByText('Needs a label wrapper')).toBeInstanceOf(HTMLSpanElement);
  expect(screen.getByText('Does not need a wrapper')).toBeInstanceOf(HTMLDivElement);
});

test('It displays itself bigger if containing images', () => {
  render(
    <>
      <Item>
        <Image src="" alt="A nice Image" />
      </Item>
    </>
  );

  expect(screen.getByAltText('A nice Image')).toBeInTheDocument();
  expect(screen.getByAltText('A nice Image')).toHaveProperty('width', 34);
  expect(screen.getByAltText('A nice Image')).toHaveProperty('height', 34);
  expect(screen.getByAltText('A nice Image').parentNode).toHaveStyleRule('height', '44px');
});

test('It transmit click and keydown events to links', () => {
  const clickHandler = jest.fn();

  render(
    <>
      <Item>
        The Item
        <Link onClick={clickHandler}>A link</Link>
      </Item>
    </>
  );

  fireEvent.click(screen.getByText('The Item'));
  fireEvent.keyDown(screen.getByText('The Item'), {key: ' ', code: 'Space'});
  fireEvent.keyDown(screen.getByText('The Item'), {key: 'Enter', code: 'Enter'});
  expect(clickHandler).toHaveBeenCalledTimes(3);
  fireEvent.keyDown(screen.getByText('The Item'), {key: 'ArrowDown', code: 'ArrowDown'});
  expect(clickHandler).toHaveBeenCalledTimes(3);
});

test('It transmit click and keydown events to checkboxes', () => {
  const handleChange = jest.fn();

  render(
    <>
      <Item>
        <Checkbox checked={false} onChange={handleChange} />
        The Item
      </Item>
    </>
  );

  fireEvent.click(screen.getByText('The Item'));
  fireEvent.keyDown(screen.getByText('The Item'), {key: ' ', code: 'Space'});
  fireEvent.keyDown(screen.getByText('The Item'), {key: 'Enter', code: 'Enter'});
  expect(handleChange).toHaveBeenCalledTimes(3);
  fireEvent.keyDown(screen.getByText('The Item'), {key: 'ArrowDown', code: 'ArrowDown'});
  expect(handleChange).toHaveBeenCalledTimes(3);
});
