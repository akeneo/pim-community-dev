import React, {KeyboardEvent, useState, useRef} from "react";
import styled from "styled-components";

const TagContainer = styled.ul`
    border: 1px solid grey;
`;

const Tag = styled.li`
    float: left;
    list-style-type: none;
    padding:5px;
    margin: 5px;
    border:1px solid grey;
    background-color: #9e9e9e;
`;

const InputTag = () => {
    const [tags, setTags] = useState<string[]>([]);
    const inputRef = useRef();

    return <TagContainer>
        {tags.map((tag, key) => {
            return <Tag key={key} data-testid={'tag'}>{tag}</Tag>;
        })}
        <Tag key='inputer'>
            <input type="text" data-testid={'tag-input'} ref={inputRef} onKeyUp={(event: KeyboardEvent<HTMLInputElement>) => {
                if(event.key===' ')
                {
                    const word = event.currentTarget.value.trim();
                    if (word !== ''){
                        setTags([...tags, ...[word]]);
                        if (inputRef.current) {
                            inputRef.current.value = '';
                        }
                    }
                }
            }}/></Tag>
    </TagContainer>;
}

export {InputTag};
