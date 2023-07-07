import React, {ChangeEvent, FC, KeyboardEvent, useCallback, useRef, useState} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontFamily} from '../../../theme';
import {CloseIcon} from '../../../icons/CloseIcon';
import {LockIcon} from '../../../icons/LockIcon';
import {arrayUnique, Key, Override} from '../../../shared';
import {InputProps} from '../common';

const RemoveTagIcon = styled(CloseIcon)<AkeneoThemedProps & {$isErrored: boolean}>`
  min-width: 12px;
  width: 12px;
  height: 12px;
  margin-right: 2px;
  cursor: pointer;
  color: ${({$isErrored}) => ($isErrored ? getColor('red', 100) : getColor('grey', 120))};
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
  width: 100%;
  margin: 0;

  &:focus-within {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }
`;

const Tag = styled.li<AkeneoThemedProps & {isSelected: boolean; readOnly: boolean; isErrored: boolean}>`
  list-style-type: none;
  padding: ${({readOnly}) => (readOnly ? '3px 17px 3px 17px' : '3px 17px 3px 4px')};
  border: 1px ${({isErrored}) => (isErrored ? getColor('red', 80) : getColor('grey', 80))} solid;
  background-color: ${({isSelected, isErrored}) =>
    isErrored ? getColor('red', 20) : isSelected ? getColor('grey', 40) : getColor('grey', 20)};
  display: flex;
  align-items: center;
  height: 30px;
  box-sizing: border-box;
  max-width: 100%;
  color: ${({readOnly, isErrored}) =>
    isErrored ? getColor('red', 100) : readOnly ? getColor('grey', 100) : getColor('grey', 140)};
`;

const TagText = styled.span`
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
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
    width: 100%;

    &::placeholder {
      opacity: 1;
      color: ${getColor('grey', 100)};
      font-family: ${getFontFamily('default')};
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

type TagInputProps = Override<
  Override<React.InputHTMLAttributes<HTMLInputElement>, InputProps<string[]>>,
  {
    /**
     * Tags to display.
     */
    value: string[];

    /**
     * Placeholder displayed where there is no tag.
     */
    placeholder?: string;

    /**
     * Defines if the input is valid on not.
     */
    invalid?: boolean;

    /**
     * List of separators used to create tags.
     */
    separators?: string[];

    /**
     * Handler called when tags are updated.
     */
    onChange: (tags: string[]) => void;

    /**
     * Callback called when the user hits enter on the field.
     */
    onSubmit?: () => void;

    /**
     * Displays tag labels instead of tags
     */
    labels?: {[key: string]: string};

    /**
     * The selected tags which are invalid
     **/
    invalidValue?: string[];
  }
>;

const TagInput: FC<TagInputProps> = ({
  onChange,
  placeholder,
  invalid,
  value = [],
  readOnly,
  onSubmit,
  separators = ['\\s', ',', ';'], // matching spaces, tabs, line breaks, coma and semi-colon
  labels,
  invalidValue = [],
  ...inputProps
}) => {
  const [isLastTagSelected, setLastTagAsSelected] = useState<boolean>(false);
  const inputRef = useRef<HTMLInputElement>(null);
  const containerRef = useRef<HTMLUListElement>(null);
  const inputContainerRef = useRef<HTMLLIElement>(null);

  const onChangeCreateTags = (event: ChangeEvent<HTMLInputElement>) => {
    const tagsAsString = event.currentTarget.value;
    if (tagsAsString !== '') {
      const newTags = tagsAsString.split(new RegExp(`[${separators.join('')}]+`, 'g'));
      if (newTags.length === 1) {
        return;
      }
      const newTagsWithoutEmpty = newTags.filter((tag: string) => tag.trim() !== '');

      createTags([...value, ...newTagsWithoutEmpty]);
    }
  };

  const onBlurCreateTag = (event: ChangeEvent<HTMLInputElement>) => {
    const inputCurrentValue = event.currentTarget.value.trim();
    if (inputCurrentValue !== '') {
      createTags([...value, ...[inputCurrentValue]]);
    }
  };

  const createTags = (newTags: string[]) => {
    newTags = arrayUnique(newTags);
    onChange(newTags);
    if (inputRef && inputRef.current) {
      inputRef.current.value = '';
    }
  };

  const removeTag = (tagIndexToRemove: number) => {
    const clonedTags = [...value];
    clonedTags.splice(tagIndexToRemove, 1);
    onChange(clonedTags);
  };

  const focusOnInputField = (event: MouseEvent) => {
    if (
      inputRef &&
      inputRef.current &&
      ((containerRef && containerRef.current === event.target) ||
        (inputContainerRef && inputContainerRef.current === event.target))
    ) {
      inputRef.current.focus();
    }
  };

  const handleKeyDown = (event: KeyboardEvent) => {
    const inputCurrentValue = inputRef?.current?.value.trim() ?? '';

    if (Key.Enter === event.key && !isLastTagSelected && !readOnly) {
      '' === inputCurrentValue ? onSubmit?.() : createTags([...value, ...[inputCurrentValue]]);

      return;
    }

    const isDeleteKeyPressed = [Key.Backspace.toString(), Key.Delete.toString()].includes(event.key);
    const tagsAreEmpty = value.length === 0;

    if (!isDeleteKeyPressed || tagsAreEmpty || '' !== inputCurrentValue) {
      setLastTagAsSelected(false);

      return;
    }

    if (isLastTagSelected) {
      const newTags = value.slice(0, value.length - 1);
      onChange(newTags);
    }

    setLastTagAsSelected(!isLastTagSelected);
  };

  const getLabel: (tag: string) => string = useCallback(
    tag => {
      return 'undefined' === typeof labels ? tag : labels[tag] ?? `[${tag}]`;
    },
    [labels]
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
            key={`${tag}-${index}`}
            data-testid="tag"
            isSelected={index === value.length - 1 && isLastTagSelected}
            readOnly={readOnly}
            isErrored={invalidValue.includes(tag)}
          >
            {!readOnly && (
              <RemoveTagIcon
                onClick={() => removeTag(index)}
                data-testid={`remove-${index}`}
                $isErrored={invalidValue.includes(tag)}
              />
            )}
            <TagText>{getLabel(tag)}</TagText>
          </Tag>
        );
      })}
      <InputContainer ref={inputContainerRef} onClick={focusOnInputField}>
        <input
          type="text"
          data-testid="tag-input"
          ref={inputRef}
          placeholder={value.length === 0 ? placeholder : ''}
          onKeyDown={handleKeyDown}
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

export {TagInput};
