import React, {useState, useRef, ChangeEvent, FC, RefObject} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';
import {CloseIcon} from '../../icons';
import {Key} from '../../shared';

type InputTagProps = {
  /**
   * Specifies if the component will accept duplicated tags or not
   */
  allowDuplicates: boolean;

  /**
   * Default tags to display
   */
  defaultTags?: string[];
};

const InputTag: FC<InputTagProps> = ({allowDuplicates, defaultTags = []}) => {
  const [tags, setTags] = useState<string[]>(defaultTags);
  const [isLastTagSelected, selectLastTag] = useState<boolean>(false);
  const inputRef = useRef<HTMLInputElement>(null);
  const containerRef = useRef();
  const inputContainerRef = useRef();

  const addTag = (event: ChangeEvent<HTMLInputElement>) => {
    const tagsAsString = event.currentTarget.value;
    if (tagsAsString !== '') {
      let newTags = tagsAsString.split(/[\s]+/);
      if (newTags.length === 1) {
        return;
      }
      newTags = newTags.filter((word: string) => word.trim() !== '');
      newTags = [...tags, ...newTags];
      if (!allowDuplicates) {
        newTags = arrayUnique(newTags);
      }
      setTags(newTags);
      if (inputRef && inputRef.current) {
        inputRef.current.value = '';
      }
    }
  };

  const removeWord = (wordToRemove: string) => {
    const newTags = tags.filter((word: string) => word !== wordToRemove);
    setTags(newTags);
  };

  const focusOnInputText = (event: MouseEvent, ref: RefObject<HTMLElement>) => {
    if (ref && ref.current === event.target && inputRef && inputRef.current) {
      inputRef.current.focus();
    }
  };

  const onBackspaceKeyUp = (event: KeyboardEvent) => {
    if (![Key.Backspace, Key.Delete].includes(event.key)) {
      selectLastTag(false);
      return;
    }

    if (tags.length === 0 || inputRef.current.value.trim() !== '') {
      return;
    }

    if (!isLastTagSelected) {
      selectLastTag(true);
    } else {
      const newTags = tags.slice(0, tags.length - 1);
      setTags(newTags);
      selectLastTag(false);
    }
  };

  return (
    <TagContainer ref={containerRef} onClick={(event: MouseEvent) => focusOnInputText(event, containerRef)}>
      {tags.map((tag, key) => {
        return (
          <Tag key={key} data-testid={'tag'} isSelected={key === tags.length - 1 && isLastTagSelected}>
            <RemoveWordIcon onClick={() => removeWord(tag)} data-testid={`remove-${tag}`} />
            {tag}
          </Tag>
        );
      })}
      <Tag key="inputer" ref={inputContainerRef} onClick={(event: any) => focusOnInputText(event, inputContainerRef)}>
        <input
          type="text"
          data-testid={'tag-input'}
          ref={inputRef}
          onKeyDownCapture={onBackspaceKeyUp}
          onChange={addTag}
        />
      </Tag>
    </TagContainer>
  );
};

const arrayUnique = (array: string[]) => {
  return Array.from(new Set(array));
};

const RemoveWordIcon = styled(CloseIcon)<AkeneoThemedProps>`
  width: 15px;
  height: 15px;
  color: ${getColor('grey', 100)};
  margin-right: 3px;
  cursor: pointer;
`;

const TagContainer = styled.ul<AkeneoThemedProps>`
  border: 1px ${getColor('grey', 80)} solid;
  border-radius: 2px;
  padding: 5px 5px 0px 5px;
  display: flex;
  flex-wrap: wrap;
`;

const Tag = styled.li<AkeneoThemedProps & {isSelected: boolean}>`
  list-style-type: none;
  padding: 9px 5px;
  margin: 0 5px 5px 0;
  border: 1px ${getColor('grey', 80)} solid;
  background-color: ${props => (props.isSelected ? getColor('grey', 40) : getColor('grey', 20))};
  text-transform: capitalize;
  display: flex;
  align-items: center;
  color: ${getColor('grey', 120)};

  :last-child {
    background-color: ${getColor('white')};
    border: 0;
    flex: 1;
  }

  > input {
    border: 0;
    outline: 0;
    color: ${getColor('grey', 120)};
  }
`;

export {InputTag};
