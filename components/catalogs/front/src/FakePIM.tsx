import React, {FC, PropsWithChildren} from 'react';
import styled from 'styled-components';
import {AkeneoIcon, Breadcrumb, CommonStyle, getColor, getFontSize, ImportXlsxIllustration} from 'akeneo-design-system';

const Container = styled.div`
    display: flex;
    width: 100vw;
    height: 100vh;

    ${CommonStyle}
`;

const Header = styled.div`
    display: flex;
    width: 100%;
    height: 154px;
`;

const Title = styled.div`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('title')};
`;

const Menu = styled.div`
    display: flex;
    justify-content: center;
    padding: 15px;
    width: 80px;
    height: 100vh;
    border-right: 1px solid ${getColor('grey', 60)};
    color: ${getColor('brand', 100)};
`;

const Page = styled.div`
    flex: 1;
    padding: 40px;
`;

const FakePIM: FC<PropsWithChildren<{}>> = ({children}) => {
    return (
        <Container>
            <Menu>
                <AkeneoIcon size={36} />
            </Menu>
            <Page>
                <Header>
                    <ImportXlsxIllustration size={138} />
                    <div>
                        <Breadcrumb>
                            <Breadcrumb.Step>Imports</Breadcrumb.Step>
                        </Breadcrumb>
                        <Title>THIS IS NOT THE PIM</Title>
                    </div>
                </Header>
                {children}
            </Page>
        </Container>
    );
};

export {FakePIM};
