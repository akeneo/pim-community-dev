import React, {FC, useState, ReactNode, useEffect} from 'react';
import styled from 'styled-components';
import {Dropdown} from './Dropdown';
import {OptionWithThumbnail} from './OptionWithThumbnail';
import {Selector} from './Selector';
import {Title} from './Title';

type Props = {
    data: {[value: string]: {label: string; imageSrc?: string}};
    onChange: (value?: string) => void;
    dropdownTitle?: ReactNode;
};

export const Select: FC<Props> = ({data, onChange, dropdownTitle}: Props) => {
    const [opened, setOpened] = useState(false);
    const [selectedValue, setSelectedValue] = useState(
        (Object.keys(data).length > 0 && Object.keys(data)[0]) || undefined
    );
    useEffect(() => {
        onChange(selectedValue);
    }, [selectedValue]);

    const handleClick = (value: string) => {
        setSelectedValue(value);
        setOpened(false);
    };

    if (!selectedValue) {
        return null;
    }
    return (
        <Container>
            {opened && (
                <Dropdown onClose={() => setOpened(false)}>
                    {dropdownTitle && <Title>{dropdownTitle}</Title>}

                    <Options>
                        {Object.entries(data).map(([value, optionData]) => (
                            <OptionWithThumbnail
                                key={value}
                                value={value}
                                onClick={handleClick}
                                selected={value === selectedValue}
                                data={optionData}
                            />
                        ))}
                    </Options>
                </Dropdown>
            )}

            <Selector onClick={() => setOpened(!opened)}>{data[selectedValue].label}</Selector>
        </Container>
    );
};

const Container = styled.div`
    display: inline-block;
    position: relative;
`;

const Options = styled.ul`
    list-style-type: none;
    margin: 0;
    max-height: 216px;
    overflow-y: auto;
    padding: 0;
    scroll-snap-type: y mandatory;
`;
