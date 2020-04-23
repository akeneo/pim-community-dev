import React, {FC} from 'react';
import defaultImageSrc from '../../assets/illustrations/NewAPI.svg';
import styled from '../../styled-with-theme';

type Props = {
    value: string;
    onClick: (value: string) => void;
    selected?: boolean;
    data: {
        label: string;
        imageSrc?: string;
    };
};

export const OptionWithThumbnail: FC<Props> = ({value, onClick, selected = false, data}: Props) => {
    return (
        <Container onClick={() => onClick(value)} selected={selected} title={data.label}>
            <Thumbnail src={data.imageSrc || defaultImageSrc} />
            <Label>{data.label}</Label>
        </Container>
    );
};

const Container = styled.li<{selected: boolean}>`
    align-items: center;
    color: ${({theme, selected}) => (selected ? theme.color.purple100 : theme.color.grey120)};
    cursor: pointer;
    display: flex;
    height: 34px;
    line-height: 34px;
    scroll-snap-align: start;
    user-select: none;
    outline: none;

    :hover {
        background: ${({theme}) => theme.color.grey20};
        color: ${({theme}) => theme.color.grey140};
    }
`;

const Thumbnail = styled.img`
    border: 1px solid ${({theme}) => theme.color.grey80};
    object-fit: cover;
    width: 28px;
    height: 28px;
`;

const Label = styled.div`
    font-size: ${({theme}) => theme.fontSize.default};
    overflow: hidden;
    padding-left: 10px;
    padding-right: 6px;
    text-overflow: ellipsis;
    white-space: nowrap;
`;
