import React, {KeyboardEvent, useState, useRef} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../theme';
import {CloseIcon} from '../../icons';

const InputTag = () => {
    const [tags, setTags] = useState<string[]>([]);
    const inputRef = useRef();

    const addWord = (event: KeyboardEvent<HTMLInputElement>) => {
        if(event.key===' ')
        {
            const word = event.currentTarget.value.trim();
            if (word !== ''){
                const newWords = [...tags, ...[word]];
                setTags(arrayUnique(newWords));
                if (inputRef.current) {
                    inputRef.current.value = '';
                }
            }
        }
    }

    const removeWord = (wordToRemove: string) => {
        const newTags = tags.filter((word: string) => word !== wordToRemove);
        setTags(newTags);
    }

    return <TagContainer>
        {tags.map((tag, key) => {
            return (
              <Tag key={key} data-testid={'tag'}>
                  <RemoveWordIcon onClick={() => removeWord(tag)} data-testid={'remove-{key}'}/>
                  {tag}
              </Tag>
            );
        })}
        <Tag key='inputer'>
            <input type='text' data-testid={'tag-input'} ref={inputRef} onKeyUp={addWord}/>
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
    padding: 6px 5px;
    display: flex;
`;

const Tag = styled.li<AkeneoThemedProps>`
    list-style-type: none;
    padding: 9px 5px;
    margin-right: 5px;
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
