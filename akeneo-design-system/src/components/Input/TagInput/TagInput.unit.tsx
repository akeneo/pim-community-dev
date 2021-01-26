import React, {useState} from 'react';
import {TagInput} from './TagInput';
import '@testing-library/jest-dom/extend-expect';
import {render, screen} from '../../../storybook/test-util';
import userEvent from '@testing-library/user-event';

test('it renders an empty input tag', () => {
  const TagInputContainer = () => {
    const [tags, setTags] = useState<string[]>([]);
    return <TagInput value={tags} onChange={setTags} />;
  };

  const result = render(<TagInputContainer />);

  expect(result.container.textContent).toBe(expectedTags([]));
});

test('it renders a tag input with default tags', () => {
  const TagInputContainer = () => {
    const [tags, setTags] = useState<string[]>(['gucci', 'samsung', 'apple']);
    return <TagInput value={tags} onChange={setTags} />;
  };

  const result = render(<TagInputContainer />);

  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
});

test('it allows tags to be created', () => {
  const TagInputContainer = () => {
    const [tags, setTags] = useState<string[]>([]);
    return <TagInput value={tags} onChange={setTags} />;
  };

  const result = render(<TagInputContainer />);

  userEvent.type(screen.getByTestId('tag-input'), 'gucci{space}samsung{space}');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung']));
});

test('it supports the copy past of multiple tags', () => {
  const TagInputContainer = () => {
    const [tags, setTags] = useState<string[]>([]);
    return <TagInput value={tags} onChange={setTags} />;
  };

  const result = render(<TagInputContainer />);

  userEvent.paste(screen.getByTestId('tag-input'), ' gucci samsung    apple asus  ');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple', 'asus']));
});

test('it accepts multiple separators', () => {
  const TagInputContainer = () => {
    const [tags, setTags] = useState<string[]>([]);
    return <TagInput value={tags} onChange={setTags} />;
  };

  const result = render(<TagInputContainer />);

  /*eslint-disable */
  const input = 'gucci    samsung \
apple \
dior,renault;porsche';
  /*eslint-enable */

  userEvent.paste(screen.getByTestId('tag-input'), input);
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple', 'dior', 'renault', 'porsche']));
});

test('it handle a deletion of a tag using the mouse', () => {
  const TagInputContainer = () => {
    const [tags, setTags] = useState<string[]>([]);
    return <TagInput value={tags} onChange={setTags} />;
  };

  const result = render(<TagInputContainer />);

  userEvent.paste(screen.getByTestId('tag-input'), 'gucci samsung apple');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
  userEvent.click(screen.getByTestId('remove-1'));
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'apple']));
  userEvent.click(screen.getByTestId('remove-1'));
  expect(result.container.textContent).toBe(expectedTags(['gucci']));
  userEvent.click(screen.getByTestId('remove-0'));
  expect(result.container.textContent).toBe(expectedTags([]));
});

test('it supports the removal of a tag using keyboard only', () => {
  const TagInputContainer = () => {
    const [tags, setTags] = useState<string[]>(['gucci', 'samsung', 'apple']);
    return <TagInput value={tags} onChange={setTags} />;
  };

  const result = render(<TagInputContainer />);

  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
  userEvent.type(screen.getByTestId('tag-input'), '{backspace}');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
  userEvent.type(screen.getByTestId('tag-input'), '{del}');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung']));
  userEvent.type(screen.getByTestId('tag-input'), '{backspace}');
  userEvent.type(screen.getByTestId('tag-input'), '{backspace}');
  expect(result.container.textContent).toBe(expectedTags(['gucci']));
  userEvent.type(screen.getByTestId('tag-input'), '{backspace}');
  userEvent.type(screen.getByTestId('tag-input'), '{backspace}');
  expect(result.container.textContent).toBe(expectedTags([]));
  userEvent.type(screen.getByTestId('tag-input'), '{backspace}');
});

test('it can display only 100 tags max', () => {
  const TagInputContainer = () => {
    const [tags, setTags] = useState<string[]>([]);
    return <TagInput value={tags} onChange={setTags} />;
  };

  render(<TagInputContainer />);

  const tags = Array.from(Array(115).keys()).join(' ');
  userEvent.paste(screen.getByTestId('tag-input'), tags);

  expect(screen.queryAllByTestId('tag')).toHaveLength(100);
  expect(screen.queryByText('55')).toBeInTheDocument();
  expect(screen.queryByText('99')).toBeInTheDocument();
  expect(screen.queryByText('100')).not.toBeInTheDocument();
  expect(screen.queryByText('110')).not.toBeInTheDocument();

  userEvent.type(screen.getByTestId('tag-input'), 'newtag');
  expect(screen.queryAllByDisplayValue('newtag')).toHaveLength(0);
});

test('it allows input to be easily focused by clicking anywhere on the component', () => {
  const TagInputContainer = () => {
    const [tags, setTags] = useState<string[]>([]);
    return <TagInput value={tags} onChange={setTags} />;
  };

  render(<TagInputContainer />);

  expect(screen.getByTestId('tag-input')).not.toHaveFocus();
  userEvent.click(screen.getByTestId('tagInputContainer'));
  expect(screen.getByTestId('tag-input')).toHaveFocus();
});

test('it creates a tag if the input loses focus', () => {
  const TagInputContainer = () => {
    const [tags, setTags] = useState<string[]>([]);
    return <TagInput value={tags} onChange={setTags} />;
  };

  const result = render(<TagInputContainer />);

  expect(result.container.textContent).toBe(expectedTags([]));
  userEvent.type(screen.getByTestId('tag-input'), 'gucci');
  expect(result.container.textContent).toBe(expectedTags([]));
  screen.getByTestId('tag-input').blur();
  expect(result.container.textContent).toBe(expectedTags(['gucci']));
  userEvent.type(screen.getByTestId('tag-input'), 'dior');
  screen.getByTestId('tag-input').blur();
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'dior']));
});

const expectedTags = (tags: string[]) => {
  expect(screen.queryAllByTestId('tag')).toHaveLength(tags.length);

  return tags.join('');
};
