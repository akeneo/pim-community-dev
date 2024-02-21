import {getFontSize} from 'akeneo-design-system';
import React from 'react';
import styled from 'styled-components';
import {useAvatar} from '../../../../../shared/user';

const Container = styled.div`
    display: flex;
    align-items: center;
    font-size: ${getFontSize('big')};
    gap: 1ch;
`;

const Image = styled.img`
    width: 27px;
    height: 27px;
    border-radius: 50%;
`;

export const UserAvatar = () => {
    const avatar = useAvatar();

    return (
        <Container>
            <Image src={avatar.imageUrl} /> {avatar.firstName} {avatar.lastName}
        </Container>
    );
};
