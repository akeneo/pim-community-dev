import React, {FC, PropsWithChildren, useCallback} from 'react';
import styled, {createGlobalStyle} from 'styled-components';
import {AkeneoIcon, Breadcrumb, CommonStyle, getColor, getFontSize, DraftIllustration} from 'akeneo-design-system';
import {useHistory} from 'react-router-dom';

const GlobalStyle = createGlobalStyle`
  body {
    margin: 0;
  }
  p {
    margin-top: 0;
    margin-bottom: 0;
  }
  * {
    box-sizing: border-box;
  }
`;

const Container = styled.div`
    display: flex;
    height: 100vh;
    width: 100vw;

    ${CommonStyle}
`;

const Header = styled.div`
    display: flex;
    height: 154px;
    width: 100%;
`;

const Title = styled.div`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('title')};
    margin: 15px 0 0;
`;

const Menu = styled.div`
    align-items: start;
    border-right: 1px solid ${getColor('grey', 60)};
    display: flex;
    height: 100vh;
    justify-content: center;
    width: 80px;
`;

const MenuButton = styled.div`
    align-items: center;
    border-left-width: 0px;
    border: 0 solid #9452ba;
    color: ${getColor('brand', 100)};
    cursor: pointer;
    display: flex;
    height: 80px;
    justify-content: center;
    padding: 0 4px 0;
    width: 100%;

    &:hover {
        border-left-width: 4px;
        padding-left: 0px;
    }
`;

const Page = styled.div`
    flex: 1;
    padding: 40px;
`;

const FakePIM: FC<PropsWithChildren<{}>> = ({children}) => {
    const history = useHistory();

    const handleAkeneoIconClick = useCallback(() => {
        history.push('/');
    }, [history]);

    return (
        <>
            <GlobalStyle />
            <Container>
                <Menu>
                    <MenuButton>
                        <AkeneoIcon size={36} onClick={handleAkeneoIconClick} />
                    </MenuButton>
                </Menu>
                <Page>
                    <Header>
                        <DraftIllustration size={138} />
                        <div>
                            <Breadcrumb>
                                <Breadcrumb.Step>Catalogs</Breadcrumb.Step>
                            </Breadcrumb>
                            <Title>Catalogs</Title>
                        </div>
                    </Header>
                    {children}
                </Page>
            </Container>
        </>
    );
};

export {FakePIM};
