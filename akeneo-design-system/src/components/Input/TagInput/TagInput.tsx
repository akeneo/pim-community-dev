import React, {useState, useRef, ChangeEvent, FC, KeyboardEvent, useCallback} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {CloseIcon, LockIcon} from '../../../icons';
import {arrayUnique, Key, Override} from '../../../shared';
import {InputProps} from '../InputProps';

type TagInputProps = Override<
  Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<string[]>>,
  {
    /**
     * Tags to display
     */
    value: string[];

    /**
     * Handle called when tags are updated
     */
    onChange: (tags: string[]) => void;

    /**
     * Placeholder displayed where there is no tag
     */
    placeholder?: string;

    /**
     * Defines if the input is valid on not
     */
    invalid?: boolean;
  }
>;

const TagInput: FC<TagInputProps> = ({onChange, placeholder, invalid, value = [], readOnly, ...inputProps}) => {
  const [isLastTagSelected, setLastTagAsSelected] = useState<boolean>(false);
  const inputRef = useRef<HTMLInputElement>(null);
  const containerRef = useRef<HTMLUListElement>(null);
  const inputContainerRef = useRef<HTMLLIElement>(null);

  const onChangeCreateTags = useCallback(
    (event: ChangeEvent<HTMLInputElement>) => {
      const tagsAsString = event.currentTarget.value;
      if (tagsAsString !== '') {
        const newTags = tagsAsString.split(/[\s,;]+/); // matching spaces, tabs, line breaks, coma and semi-colon
        if (newTags.length === 1) {
          return;
        }
        const newTagsWithoutEmpty = newTags.filter((tag: string) => tag.trim() !== '');

        createTags([...value, ...newTagsWithoutEmpty]);
      }
    },
    [value]
  );

  const onBlurCreateTag = useCallback(
    (event: ChangeEvent<HTMLInputElement>) => {
      const inputCurrentValue = event.currentTarget.value.trim();
      if (inputCurrentValue !== '') {
        createTags([...value, ...[inputCurrentValue]]);
      }
    },
    [value]
  );

  const createTags = useCallback(
    (newTags: string[]) => {
      newTags = arrayUnique(newTags);
      onChange(newTags);
      if (inputRef && inputRef.current) {
        inputRef.current.value = '';
      }
    },
    [inputRef, onChange]
  );

  const removeTag = useCallback(
    (tagIndexToRemove: number) => {
      const clonedTags = [...value];
      clonedTags.splice(tagIndexToRemove, 1);
      onChange(clonedTags);
    },
    [value, onChange]
  );

  const focusOnInputField = useCallback(
    (event: MouseEvent) => {
      if (
        inputRef &&
        inputRef.current &&
        ((containerRef && containerRef.current === event.target) ||
          (inputContainerRef && inputContainerRef.current === event.target))
      ) {
        inputRef.current.focus();
      }
    },
    [inputRef, containerRef, inputContainerRef]
  );

  const handleTagDeletion = useCallback(
    (event: KeyboardEvent) => {
      const isDeleteKeyPressed = [Key.Backspace.toString(), Key.Delete.toString()].includes(event.key);
      const tagsAreEmpty = value.length === 0;
      const inputFieldIsNotEmpty = inputRef && inputRef.current && inputRef.current.value.trim() !== '';

      if (!isDeleteKeyPressed || tagsAreEmpty || inputFieldIsNotEmpty) {
        setLastTagAsSelected(false);
        return;
      }

      if (isLastTagSelected) {
        const newTags = value.slice(0, value.length - 1);
        onChange(newTags);
      }

      setLastTagAsSelected(!isLastTagSelected);
    },
    [isLastTagSelected, setLastTagAsSelected, onChange, value, inputRef]
  );

  return (
    <TagContainer
      data-testid="tagInputContainer"
      ref={containerRef}
      invalid={invalid}
      onClick={focusOnInputField}
      readOnly={readOnly}
    >
      {value.map((tag, index) => {
        return (
          <Tag
            key={`${tag.toLowerCase()}-${index}`}
            data-testid="tag"
            isSelected={index === value.length - 1 && isLastTagSelected}
            readOnly={readOnly}
          >
            {!readOnly && <RemoveTagIcon onClick={() => removeTag(index)} data-testid={`remove-${index}`} />}
            {tag}
          </Tag>
        );
      })}
      <InputContainer ref={inputContainerRef} onClick={focusOnInputField}>
        <input
          type="text"
          data-testid="tag-input"
          ref={inputRef}
          placeholder={value.length === 0 ? placeholder : ''}
          onKeyDown={handleTagDeletion}
          onChange={onChangeCreateTags}
          onBlurCapture={onBlurCreateTag}
          aria-invalid={invalid}
          readOnly={readOnly}
          {...inputProps}
        />
        {readOnly && <ReadOnlyIcon size={16} />}
      </InputContainer>
    </TagContainer>
  );
};

const RemoveTagIcon = styled(CloseIcon)<AkeneoThemedProps>`
  width: 12px;
  height: 12px;
  color: ${getColor('grey', 120)};
  margin-right: 2px;
  cursor: pointer;
`;

const TagContainer = styled.ul<AkeneoThemedProps & {invalid: boolean}>`
  border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
  border-radius: 2px;
  padding: 4px;
  display: flex;
  flex-wrap: wrap;
  min-height: 40px;
  gap: 5px;
  box-sizing: border-box;
  background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
  position: relative;

  &:focus-within {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }
`;

const Tag = styled.li<AkeneoThemedProps & {isSelected: boolean; readOnly: boolean}>`
  list-style-type: none;
  padding: ${({readOnly}) => (readOnly ? '3px 17px 3px 17px' : '3px 17px 3px 4px')};
  border: 1px ${getColor('grey', 80)} solid;
  background-color: ${({isSelected}) => (isSelected ? getColor('grey', 40) : getColor('grey', 20))};
  display: flex;
  align-items: center;
  height: 22px;
`;

const InputContainer = styled.li<AkeneoThemedProps>`
  list-style-type: none;
  color: ${getColor('grey', 120)};
  border: 0;
  flex: 1;
  padding: 0;
  align-items: center;
  display: flex;

  > input {
    border: 0;
    outline: 0;
    color: ${getColor('grey', 120)};
    background-color: transparent;

    &::placeholder {
      color: ${getColor('grey', 100)};
      font-family: 'Lato';
    }
  }
`;

const ReadOnlyIcon = styled(LockIcon)`
  position: absolute;
  right: 0;
  top: 0;
  margin: 11px;
  color: ${getColor('grey', 100)};
`;

export {TagInput};
