import React, {useState, useRef, ChangeEvent, FC, RefObject, KeyboardEvent, useEffect} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {CloseIcon} from '../../../icons';
import {Key} from '../../../shared';

type TagInputProps = {
  /**
   * Specifies if the component will accept duplicated tags or not
   */
  allowDuplicates: boolean;

  /**
   * Default tags to display
   */
  defaultTags?: string[];

  /**
   * Handle called when tags are updated
   */
  onTagsUpdate: (tags: string[]) => void;
};

const TagInput: FC<TagInputProps> = ({allowDuplicates, onTagsUpdate, defaultTags = []}) => {
  const [tags, setTags] = useState<string[]>(defaultTags);
  const [isLastTagSelected, selectLastTag] = useState<boolean>(false);
  const inputRef = useRef<HTMLInputElement>(null);
  const containerRef = useRef<HTMLUListElement>(null);
  const inputContainerRef = useRef<HTMLLIElement>(null);

  const updateTags = (tags: string[]) => {
    tags = tags.slice(0, Math.min(100, tags.length));
    setTags(tags);
  }

  const addTag = (event: ChangeEvent<HTMLInputElement>) => {
    const tagsAsString = event.currentTarget.value;
    if (tagsAsString !== '') {
      let newTags = tagsAsString.split(/[\s,;]+/);
      if (newTags.length === 1) {
        return;
      }
      newTags = newTags.filter((tag: string) => tag.trim() !== '');
      newTags = [...tags, ...newTags];
      if (!allowDuplicates) {
        newTags = arrayUnique(newTags);
      }
      updateTags(newTags);
      if (inputRef && inputRef.current) {
        inputRef.current.value = '';
      }
    }
  };

  const removeTag = (tagIdToRemove: number) => {
    const clonedTags = [...tags];
    clonedTags.splice(tagIdToRemove, 1);
    updateTags(clonedTags);
  };

  const focusOnInputText = (event: MouseEvent, ref: RefObject<HTMLElement>) => {
    if (ref && ref.current === event.target && inputRef && inputRef.current) {
      inputRef.current.focus();
    }
  };

  const handleTagDeletionUsingKeyboard = (event: KeyboardEvent) => {
    if (![Key.Backspace.toString(), Key.Delete.toString()].includes(event.key)) {
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
  };

  useEffect(() => {
    onTagsUpdate(tags);
  }, [tags]);

  return (
    <TagContainer ref={containerRef} onClick={(event: MouseEvent) => focusOnInputText(event, containerRef)}>
      {tags.map((tag, key) => {
        return (
          <Tag key={key} data-testid={'tag'} isSelected={key === tags.length - 1 && isLastTagSelected}>
            <RemoveTagIcon onClick={() => removeTag(key)} data-testid={`remove-${key}`} />
            {tag}
          </Tag>
        );
      })}
      <Tag key="tag-input" ref={inputContainerRef} onClick={(event: any) => focusOnInputText(event, inputContainerRef)}>
        <input
          type="text"
          data-testid={'tag-input'}
          ref={inputRef}
          onKeyDownCapture={handleTagDeletionUsingKeyboard}
          onChange={addTag}
        />
      </Tag>
    </TagContainer>
  );
};

const arrayUnique = (array: string[]) => {
  return Array.from(new Set(array));
};

const RemoveTagIcon = styled(CloseIcon)<AkeneoThemedProps>`
  width: 15px;
  height: 15px;
  color: ${getColor('grey', 100)};
  margin-right: 3px;
  cursor: pointer;
`;

const TagContainer = styled.ul<AkeneoThemedProps>`
  border: 1px ${getColor('grey', 80)} solid;
  border-radius: 2px;
  padding: 5px;
  display: flex;
  flex-wrap: wrap;
  min-height: 42px;
  gap: 5px;
  box-sizing: border-box;
`;

const Tag = styled.li<AkeneoThemedProps & {isSelected: boolean}>`
  list-style-type: none;
  padding: 4px;
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
  }
`;

export {TagInput};
