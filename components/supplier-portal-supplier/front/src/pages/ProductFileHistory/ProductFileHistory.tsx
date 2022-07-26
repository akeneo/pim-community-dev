import React from 'react';
import styled from 'styled-components';
import {ConversationalHelper, Menu} from '../../components';
import {FormattedMessage} from 'react-intl';
import {EmptyProductFileHistory} from './components';
import {useQuery} from 'react-query';
import {fetchProductFiles} from './api/fetchProductFiles';
import {ProductFileList} from './components/ProductFileList';
import {ProductFiles} from './model';

const ProductFileHistory = () => {
    const {data: productFiles} = useQuery<ProductFiles>('fetchProductFiles', () => fetchProductFiles());

    const HeaderWelcomeMessage = (
        <>
            <p>
                <FormattedMessage defaultMessage="You will find here a recap of the files you shared." id="VeYJWI" />
            </p>
        </>
    );

    return (
        <Container>
            <Menu activeItem="history" />
            <Content>
                <ConversationalHelper content={HeaderWelcomeMessage} />
                {undefined === productFiles ||
                    null === productFiles ||
                    (0 === productFiles.length && <EmptyProductFileHistory />)}
                {undefined !== productFiles && null !== productFiles && <ProductFileList productFiles={productFiles} />}
            </Content>
        </Container>
    );
};

const Container = styled.div`
    display: flex;
    height: 100vh;
`;

const Content = styled.div`
    flex: 1;
    display: flex;
    flex-direction: column;
`;

export {ProductFileHistory};
