import React, {useState, useRef, ChangeEvent, FC, RefObject, KeyboardEvent, useCallback} from 'react';
import styled from 'styled-components';
import {CloseIcon, getColor, AkeneoThemedProps} from 'akeneo-design-system';

type TagInputProps = {
  /**
   * Specifies if the component will accept duplicated tags or not
   */
  allowDuplicates: boolean;

  /**
   * Tags to display
   */
  tags: string[];

  /**
   * Handle called when tags are updated
   */
  setTags: (tags: string[]) => void;

  /**
   * Placeholder displayed where there is no tag
   */
  placeholder?: string;

  /**
   * Defines if the input is valid on not.
   */
  isInvalid?: boolean;
};

const TagInput: FC<TagInputProps> = ({allowDuplicates, setTags, placeholder, isInvalid, tags = []}) => {
  const [isLastTagSelected, selectLastTag] = useState<boolean>(false);
  const inputRef = useRef<HTMLInputElement>(null);
  const containerRef = useRef<HTMLUListElement>(null);
  const inputContainerRef = useRef<HTMLLIElement>(null);

  const updateTags = useCallback(
    (updatedTags: string[]) => {
      updatedTags = updatedTags.slice(0, Math.min(100, updatedTags.length));
      setTags(updatedTags);
    },
    [setTags]
  );

  const onChangeCreateTags = useCallback(
    (event: ChangeEvent<HTMLInputElement>) => {
      const tagsAsString = event.currentTarget.value;
      if (tagsAsString !== '') {
        let newTags = tagsAsString.split(/[\s,;]+/);
        if (newTags.length === 1) {
          return;
        }
        newTags = newTags.filter((tag: string) => tag.trim() !== '');

        createTags([...tags, ...newTags]);
      }
    },
    [tags]
  );

  const onBlurCreateTag = useCallback(
    (event: ChangeEvent<HTMLInputElement>) => {
      const inputCurrentValue = event.currentTarget.value.trim();
      if (inputCurrentValue !== '') {
        createTags([...tags, ...[inputCurrentValue]]);
      }
    },
    [tags]
  );

  const createTags = useCallback(
    (newTags: string[]) => {
      if (!allowDuplicates) {
        newTags = arrayUnique(newTags);
      }
      updateTags(newTags);
      if (inputRef && inputRef.current) {
        inputRef.current.value = '';
      }
    },
    [arrayUnique, inputRef, updateTags]
  );

  const removeTag = useCallback(
    (tagIdToRemove: number) => {
      const clonedTags = [...tags];
      clonedTags.splice(tagIdToRemove, 1);
      updateTags(clonedTags);
    },
    [tags, updateTags]
  );

  const focusOnInputField = useCallback(
    (event: MouseEvent, ref: RefObject<HTMLElement>) => {
      if (ref && ref.current === event.target && inputRef && inputRef.current) {
        inputRef.current.focus();
      }
    },
    [inputRef]
  );

  const handleTagDeletionUsingKeyboard = useCallback(
    (event: KeyboardEvent) => {
      if (!['Backspace', 'Delete'].includes(event.key)) {
        selectLastTag(false);

        if (tags.length >= 100) {
          event.preventDefault();
          event.stopPropagation();
        }

        return;
      }

      if (tags.length === 0 || (inputRef && inputRef.current && inputRef.current.value.trim() !== '')) {
        return;
      }

      if (!isLastTagSelected) {
        selectLastTag(true);
      } else {
        const newTags = tags.slice(0, tags.length - 1);
        updateTags(newTags);
        selectLastTag(false);
      }
    },
    [isLastTagSelected, selectLastTag, updateTags, tags, inputRef]
  );

  return (
    <TagContainer
      data-testid={'container'}
      ref={containerRef}
      isInvalid={isInvalid}
      onClick={(event: MouseEvent) => focusOnInputField(event, containerRef)}
    >
      {tags.map((tag, key) => {
        return (
          <Tag key={key} data-testid={'tag'} isSelected={key === tags.length - 1 && isLastTagSelected}>
            <RemoveTagIcon onClick={() => removeTag(key)} data-testid={`remove-${key}`} />
            {tag}
          </Tag>
        );
      })}
      <Tag
        key="tag-input"
        ref={inputContainerRef}
        onClick={(event: MouseEvent) => focusOnInputField(event, inputContainerRef)}
      >
        <input
          type="text"
          data-testid={'tag-input'}
          ref={inputRef}
          placeholder={tags.length === 0 ? placeholder : ''}
          onKeyDownCapture={handleTagDeletionUsingKeyboard}
          onChange={onChangeCreateTags}
          onBlurCapture={onBlurCreateTag}
        />
      </Tag>
    </TagContainer>
  );
};

const arrayUnique = (array: string[]) => {
  return Array.from(new Set(array));
};

const RemoveTagIcon = styled(CloseIcon)<AkeneoThemedProps>`
  width: 12px;
  height: 12px;
  color: ${getColor('grey', 120)};
  margin-right: 2px;
  cursor: pointer;
`;

const TagContainer = styled.ul<AkeneoThemedProps & {isInvalid: boolean}>`
  border: 1px solid ${({isInvalid}) => (isInvalid ? getColor('red', 100) : getColor('grey', 80))};
  border-radius: 2px;
  padding: 5px;
  display: flex;
  flex-wrap: wrap;
  min-height: 45px;
  gap: 5px;
  box-sizing: border-box;

  &:focus-within {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }
`;

const Tag = styled.li<AkeneoThemedProps & {isSelected: boolean}>`
  list-style-type: none;
  padding: 4px 17px 4px 4px;
  border: 1px ${getColor('grey', 80)} solid;
  background-color: ${props => (props.isSelected ? getColor('grey', 40) : getColor('grey', 20))};
  display: flex;
  align-items: center;
  color: ${getColor('grey', 120)};

  :last-child {
    background-color: ${getColor('white')};
    border: 0;
    flex: 1;
    padding: 0;
  }

  > input {
    border: 0;
    outline: 0;
    color: ${getColor('grey', 120)};

    &::placeholder {
      color: ${getColor('grey', 100)};
    }
  }
`;

export {TagInput};
