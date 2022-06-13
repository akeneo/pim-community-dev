import React, {ReactElement} from 'react';
import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';
import illustration from '../assets/images/Factory.svg';

const UnauthenticatedContainer = ({children}: {children: ReactElement[]}) => {
    return (
        <Container>
            <LeftColumn>
                <Content>{children}</Content>
            </LeftColumn>
            <RightColumn>
                <Illustration src={illustration} />
            </RightColumn>
        </Container>
    );
};

const Container = styled.div`
    display: flex;
    justify-content: space-between;
    height: 100vh;
    align-items: center;
`;
const LeftColumn = styled.div`
    flex: 1;
    display: flex;
    justify-content: center;
`;
const Content = styled.div`
    width: 300px;
`;
const RightColumn = styled.div`
    width: 58%;
    background-color: ${getColor('grey20')};
    display: flex;
    justify-content: center;
    align-self: normal;
`;
const Illustration = styled.img`
    width: 500px;
`;

export {UnauthenticatedContainer};
