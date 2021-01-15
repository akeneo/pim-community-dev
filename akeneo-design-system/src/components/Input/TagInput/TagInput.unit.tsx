import React from 'react';
import {TagInput} from './TagInput';
import '@testing-library/jest-dom/extend-expect';
import {render, screen} from '../../../storybook/test-util';
import userEvent from '@testing-library/user-event';

test('it renders an empty input tag', () => {
  const result = render(<TagInput allowDuplicates={true} />);

  expect(result.container.textContent).toBe(expectedTags([]));
});

test('it renders an input tag with default tags', () => {
  const result = render(<TagInput allowDuplicates={true} defaultTags={['gucci', 'samsung', 'apple']} />);

  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
});

test('it renders a list of tags', () => {
  const result = render(<TagInput allowDuplicates={true} />);

  userEvent.type(screen.getByTestId('tag-input'), 'gucci{space}samsung{space}');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung']));
});

test('it handle a copy pasted input list of tags', () => {
  const result = render(<TagInput allowDuplicates={true} />);

  userEvent.paste(screen.getByTestId('tag-input'), ' gucci samsung    apple asus  ');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple', 'asus']));
});

test('it handle a deletion of a tag', () => {
  const result = render(<TagInput allowDuplicates={true} />);

  userEvent.paste(screen.getByTestId('tag-input'), 'gucci samsung apple');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
  userEvent.click(screen.getByTestId('remove-samsung'));
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'apple']));
  userEvent.click(screen.getByTestId('remove-apple'));
  expect(result.container.textContent).toBe(expectedTags(['gucci']));
  userEvent.click(screen.getByTestId('remove-gucci'));
  expect(result.container.textContent).toBe(expectedTags([]));
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

test('it removes the tag on the left of the input on several user events', () => {
  const result = render(<TagInput defaultTags={['gucci', 'samsung', 'apple']} allowDuplicates={false} />);

  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
  userEvent.type(screen.getByTestId('tag-input'), '{backspace}');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
  userEvent.type(screen.getByTestId('tag-input'), '{del}');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung']));
});

const expectedTags = (tags: string[]) => {
  expect(screen.queryAllByTestId('tag')).toHaveLength(tags.length);

  return tags.join('');
};
