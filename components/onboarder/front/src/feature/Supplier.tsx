import React from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from "styled-components";
import {useSuppliers} from "./hooks/useSuppliers";
import {SupplierList} from "./components/SupplierList";
import {CreateSupplier} from "./components/CreateSupplier";

const Header = styled.div``;
const InfoBar = styled.div``;
const Title = styled.div``;
const Content = styled.div``;
const Container = styled.div``;

const Supplier = () => {
    const translate = useTranslate();
    const [suppliers, refreshSuppliers] = useSuppliers('', 1);

    return (
        <Container>
            <Header>
                <InfoBar>
                    <Breadcrumb>
                        <Breadcrumb.Step>{translate('onboarder.supplier.breadcrumb.root')}</Breadcrumb.Step>
                        <Breadcrumb.Step>{translate('onboarder.supplier.breadcrumb.suppliers')}</Breadcrumb.Step>
                    </Breadcrumb>
                </InfoBar>
                <Title>
                    {translate('onboarder.supplier.title')}
                </Title>
            </Header>
            <Content>
                {0 === suppliers.length ? (<div>No supplier <CreateSupplier onSupplierCreated={refreshSuppliers}/></div>) : (<SupplierList suppliers={suppliers}/>)}
            </Content>
        </Container>
    );
};

export {Supplier};
