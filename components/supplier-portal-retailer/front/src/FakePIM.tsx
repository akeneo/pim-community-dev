import React, {useState} from 'react';
import styled from 'styled-components';
import {AkeneoIcon, CommonStyle, getColor} from 'akeneo-design-system';
import {RetailerApp} from './feature';
import {AkeneoThemedProps, getFontSize} from 'akeneo-design-system/lib/theme/theme';

const Container = styled.div`
    display: flex;
    width: 100vw;
    height: 100vh;

    ${CommonStyle}
`;

const Menu = styled.div`
    display: flex;
    justify-content: center;
    padding: 15px;
    width: 80px;
    height: 100vh;
    border-right: 1px solid ${getColor('grey', 60)};
`;

const SubMenu = styled.ul`
    display: block;
    padding: 30px;
    width: 280px;
    height: 100vh;
    border-right: 1px solid ${getColor('grey', 60)};
    font-size: ${getFontSize('big')};
    color: ${getColor('brand', 100)};
`;

const SubMenuItem = styled.li<AkeneoThemedProps & {active: boolean}>`
    margin-bottom: 20px;
    color: ${({active}) => getColor(active ? 'brand100' : 'grey140')};
    cursor: pointer;
`;

const SubMenuHeader = styled.div`
    margin-bottom: 20px;
    color: #a1a9b7;
    text-transform: uppercase;
    font-size: 11px;
    line-height: 20px;
`;

const style = `
ul {
  padding: 0;
  margin: 0;
  list-style: none;
}
`;

const Page = styled.div`
    flex: 1;
`;

const apps = {
    supplierManagement: '#/retailer-portal/supplier',
    productFileDropping: '#/retailer-portal/product-file',
};

const FakePIM = () => {
    const [activeApp, setActiveApp] = useState(
        window.location.hash.includes(apps.supplierManagement) || '#/' === window.location.hash
            ? apps.supplierManagement
            : apps.productFileDropping
    );

    return (
        <Container>
            <style>{style}</style>
            <Menu>
                <AkeneoIcon size={36} />
            </Menu>
            <SubMenu>
                <SubMenuHeader>Supplier Portal</SubMenuHeader>
                <SubMenuItem
                    active={activeApp === apps.supplierManagement}
                    onClick={() => {
                        window.location.href = '/' + apps.supplierManagement;
                        setActiveApp(apps.supplierManagement);
                    }}
                >
                    Suppliers
                </SubMenuItem>
                <SubMenuItem
                    active={activeApp === apps.productFileDropping}
                    onClick={() => {
                        window.location.href = '/' + apps.productFileDropping;
                        setActiveApp(apps.productFileDropping);
                    }}
                >
                    Product files
                </SubMenuItem>
            </SubMenu>
            <Page>
                {(activeApp === apps.supplierManagement || activeApp === apps.productFileDropping) && <RetailerApp />}
            </Page>
        </Container>
    );
};

export {FakePIM};
