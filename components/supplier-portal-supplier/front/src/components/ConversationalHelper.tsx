import React, {ReactElement} from 'react';
import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';
import {FormattedMessage} from 'react-intl';

type Props = {
    content: ReactElement | string;
};

const ConversationalHelper = ({content}: Props) => {
    return (
        <Container>
            <Title>
                <FormattedMessage defaultMessage="Akeneo Supplier Portal Assistant" id="tp6tYp" />
            </Title>
            {content}
        </Container>
    );
};

const Container = styled.div`
    padding: 50px;
    background-color: ${getColor('blue10')};
    p {
        font-size: 25px;
        color: ${getColor('grey140')};
        line-height: 30px;
    }
`;
const Title = styled.div`
    margin-bottom: 10px;
    color: ${getColor('grey120')};
`;

export {ConversationalHelper};
