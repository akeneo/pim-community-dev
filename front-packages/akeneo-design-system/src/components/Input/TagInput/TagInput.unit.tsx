import React, {useState} from 'react';
import {TagInput} from './TagInput';
import {render, screen} from '../../../storybook/test-util';
import userEvent from '@testing-library/user-event';

test('it renders a tag input with default tags', () => {
  render(<TagInput value={['gucci', 'samsung', 'apple']} onChange={jest.fn()} />);

  expect(screen.getByText('gucci')).toBeInTheDocument();
  expect(screen.getByText('samsung')).toBeInTheDocument();
  expect(screen.getByText('apple')).toBeInTheDocument();
});

test('it allows tags to be created', () => {
  const handleChange = jest.fn();

  render(<TagInput value={[]} onChange={handleChange} />);

  userEvent.type(screen.getByRole('textbox'), 'gucci{space}');

  expect(handleChange).toHaveBeenCalledWith(['gucci']);
});

test('it can create tags using Enter and handles on submit callback', () => {
  const handleChange = jest.fn();
  const handleSubmit = jest.fn();

  render(<TagInput value={['12']} onChange={handleChange} onSubmit={handleSubmit} />);

  const input = screen.getByRole('textbox');

  userEvent.type(input, 'nice{enter}');

  expect(handleChange).toHaveBeenCalledWith(['12', 'nice']);
  expect(handleSubmit).not.toHaveBeenCalled();

  userEvent.type(input, '{enter}');

  expect(handleSubmit).toHaveBeenCalled();
});

test('it supports the copy paste of multiple tags', () => {
  const handleChange = jest.fn();

  render(<TagInput value={[]} onChange={handleChange} />);

  userEvent.paste(screen.getByRole('textbox'), ' gucci samsung    apple asus  ');

  expect(handleChange).toBeCalledWith(['gucci', 'samsung', 'apple', 'asus']);
});

test('it accepts multiple separators', () => {
  const handleChange = jest.fn();

  render(<TagInput value={[]} onChange={handleChange} />);

  /*eslint-disable */
  const input = 'gucci    samsung \
apple \
dior,renault;porsche';
  /*eslint-enable */

  userEvent.paste(screen.getByRole('textbox'), input);

  expect(handleChange).toBeCalledWith(['gucci', 'samsung', 'apple', 'dior', 'renault', 'porsche']);
});

test('it can use overridden separators', () => {
  const handleChange = jest.fn();

  render(<TagInput value={[]} separators={['w', 'y']} onChange={handleChange} />);

  userEvent.paste(screen.getByRole('textbox'), 'nicewsepa ratorwindeedythisyoneytoo');

  expect(handleChange).toBeCalledWith(['nice', 'sepa rator', 'indeed', 'this', 'one', 'too']);
});

test('it handles deletion of a tag using the mouse', () => {
  const TagInputContainer = () => {
    const [tags, setTags] = useState<string[]>([]);
    return <TagInput value={tags} onChange={setTags} />;
  };

  const result = render(<TagInputContainer />);

  userEvent.paste(screen.getByRole('textbox'), 'gucci samsung apple');
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
  userEvent.type(screen.getByRole('textbox'), '{backspace}');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung', 'apple']));
  userEvent.type(screen.getByRole('textbox'), '{del}');
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'samsung']));
  userEvent.type(screen.getByRole('textbox'), '{backspace}');
  userEvent.type(screen.getByRole('textbox'), '{backspace}');
  expect(result.container.textContent).toBe(expectedTags(['gucci']));
  userEvent.type(screen.getByRole('textbox'), '{backspace}');
  userEvent.type(screen.getByRole('textbox'), '{backspace}');
  expect(result.container.textContent).toBe(expectedTags([]));
  userEvent.type(screen.getByRole('textbox'), '{backspace}');
});

test('it allows input to be easily focused by clicking anywhere on the component', () => {
  render(<TagInput value={[]} onChange={jest.fn()} />);

  expect(screen.getByRole('textbox')).not.toHaveFocus();

  userEvent.click(screen.getByTestId('tagInputContainer'));

  expect(screen.getByRole('textbox')).toHaveFocus();
});

test('it creates a tag if the input loses focus', () => {
  const TagInputContainer = () => {
    const [tags, setTags] = useState<string[]>([]);
    return <TagInput value={tags} onChange={setTags} />;
  };

  const result = render(<TagInputContainer />);

  expect(result.container.textContent).toBe(expectedTags([]));
  userEvent.type(screen.getByRole('textbox'), 'gucci');
  expect(result.container.textContent).toBe(expectedTags([]));
  screen.getByRole('textbox').blur();
  expect(result.container.textContent).toBe(expectedTags(['gucci']));
  userEvent.type(screen.getByRole('textbox'), 'dior');
  screen.getByRole('textbox').blur();
  expect(result.container.textContent).toBe(expectedTags(['gucci', 'dior']));
});

test('it prevents readonly tags to be deleted', () => {
  render(<TagInput value={['gucci', 'samsung', 'apple']} onChange={jest.fn()} readOnly={true} />);

  expect(screen.queryByTestId('remove-0')).not.toBeInTheDocument();
  expect(screen.queryByTestId('remove-1')).not.toBeInTheDocument();
  expect(screen.queryByTestId('remove-2')).not.toBeInTheDocument();
});

const expectedTags = (tags: string[]) => {
  expect(screen.queryAllByTestId('tag')).toHaveLength(tags.length);

  return tags.join('');
};
