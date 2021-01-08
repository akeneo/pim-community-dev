import React, {useState, useRef, ChangeEvent, FC} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';
import {CloseIcon} from '../../icons';

type InputTagProps = {
    /**
     * Specifies if the component will accept duplicated tags or not
     */
    allowDuplicates: boolean;

    /**
     * Default tags to display
     */
    defaultTags?: string[];
}

const InputTag: FC<InputTagProps> = ({allowDuplicates, defaultTags = []}) => {
    const [tags, setTags] = useState<string[]>(defaultTags);
    const inputRef = useRef();
    const containerRef = useRef();
    const inputContainerRef = useRef();

    const addTag = (event: ChangeEvent<HTMLInputElement>) => {
        let tagsAsString = event.currentTarget.value;
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
            if (inputRef.current) {
                inputRef.current.value = '';
            }
        }
    }

    const removeWord = (wordToRemove: string) => {
        const newTags = tags.filter((word: string) => word !== wordToRemove);
        setTags(newTags);
    }

    const focusOnInputText = (event: any, ref: any) => {
        ref.current && ref.current === event.target && inputRef.current ? inputRef.current.focus() : null
    }

    return <TagContainer ref={containerRef} onClick={(event: any) => focusOnInputText(event, containerRef)}>
        {tags.map((tag, key) => {
            return (
              <Tag key={key} data-testid={'tag'}>
                  <RemoveWordIcon onClick={() => removeWord(tag)} data-testid={`remove-${tag}`}/>
                  {tag}
              </Tag>
            );
        })}
        <Tag key='inputer' ref={inputContainerRef} onClick={(event: any) => focusOnInputText(event, inputContainerRef)}>
            <input type='text' data-testid={'tag-input'} ref={inputRef} onChange={addTag}/>
        </Tag>
    </TagContainer>;
}

const arrayUnique = (array: string[]) => {
    return Array.from(new Set(array))
}

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

const Tag = styled.li<AkeneoThemedProps>`
    list-style-type: none;
    padding: 9px 5px;
    margin: 0 5px 5px 0;
    border: 1px ${getColor('grey', 80)} solid;
    background-color: ${getColor('grey', 20)};
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
