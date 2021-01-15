import React from 'react';
import {TagInput} from './TagInput';
import '@testing-library/jest-dom/extend-expect';
import {render, screen} from '../../../storybook/test-util';
import userEvent from '@testing-library/user-event';

test('it renders an empty input tag', () => {
  const result = render(<TagInput allowDuplicates={false} />);

  expect(result.container.textContent).toBe(expectedTags([]));
});

test('it renders an input tag with default tags', () => {
  const result = render(<TagInput allowDuplicates={false} defaultTags={['gucci', 'samsung', 'apple']} />);

  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
});

test('it renders a list of tags', () => {
  const result = render(<TagInput allowDuplicates={false} />);

  userEvent.type(screen.getByTestId('tag-input'), 'gucci{space}samsung{space}');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung']));
});

test('it supports the copy past of multiple tags', () => {
  const result = render(<TagInput allowDuplicates={false} />);

  userEvent.paste(screen.getByTestId('tag-input'), ' gucci samsung    apple asus  ');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple', 'asus']));
});

test('it accepts multiple separators', () => {
  const result = render(<TagInput allowDuplicates={false} />);

  /*eslint-disable */
  const input = 'gucci    samsung \
apple \
dior,renault;porsche';
  /*eslint-enable */
  userEvent.paste(screen.getByTestId('tag-input'), input);
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple', 'dior', 'renault', 'porsche']));
});

test('it can keep duplicated tags', () => {
  const result = render(<TagInput allowDuplicates={true} />);

  userEvent.paste(screen.getByTestId('tag-input'), 'gucci samsung apple samsung gucci');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple', 'samsung', 'gucci']));
});

test('it can remove duplicated tags', () => {
  const result = render(<TagInput allowDuplicates={false} />);

  userEvent.paste(screen.getByTestId('tag-input'), 'gucci samsung apple samsung gucci');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
});

test('it handle a deletion of a tag using the mouse', () => {
  const result = render(<TagInput allowDuplicates={true} />);

  userEvent.paste(screen.getByTestId('tag-input'), 'gucci samsung apple');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
  userEvent.click(screen.getByTestId('remove-1'));
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'apple']));
  userEvent.click(screen.getByTestId('remove-1'));
  expect(result.container.textContent).toBe(expectedTags(['gucci']));
  userEvent.click(screen.getByTestId('remove-0'));
  expect(result.container.textContent).toBe(expectedTags([]));

  userEvent.paste(screen.getByTestId('tag-input'), 'gucci samsung gucci gucci');
  userEvent.click(screen.getByTestId('remove-2'));
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'gucci']));
});

test('it supports the removal of a tag using keyboard only', () => {
  const result = render(<TagInput defaultTags={['gucci', 'samsung', 'apple']} allowDuplicates={false} />);

  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
  userEvent.type(screen.getByTestId('tag-input'), '{backspace}');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
  userEvent.type(screen.getByTestId('tag-input'), '{del}');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung']));
  userEvent.type(screen.getByTestId('tag-input'), '{backspace}');
  userEvent.type(screen.getByTestId('tag-input'), '{backspace}');
  expect(result.container.textContent).toBe(expectedTags(['gucci']));
});

const expectedTags = (tags: string[]) => {
  expect(screen.queryAllByTestId('tag')).toHaveLength(tags.length);

  return tags.join('');
};
