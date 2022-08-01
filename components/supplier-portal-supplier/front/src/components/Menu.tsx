import {SupplierPortalLogo} from './SupplierPortalLogo';
import {FormattedMessage} from 'react-intl';
import {BadgeButton} from './BadgeButton';
import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor} from 'akeneo-design-system';
import {useUserContext} from '../contexts';

type Props = {
    activeItem: 'fileEnrichment' | 'history';
};

const Menu = ({activeItem}: Props) => {
    const {updateUser} = useUserContext();

    const logout = async () => {
        await fetch('/supplier-portal/logout');
        updateUser(null);
    };

    return (
        <Container>
            <SupplierPortalLogo width={164} />
            <MenuContent>
                <MenuSectionHeader active={true}>
                    <FormattedMessage defaultMessage="File enrichment" id="gxGnf7" />
                </MenuSectionHeader>
                <MenuSection>
                    <BadgeButton isActive={'fileEnrichment' === activeItem}>
                        <FormattedMessage defaultMessage="File transfer" id="l2OoTB" />
                    </BadgeButton>
                    <BadgeButton isActive={'history' === activeItem}>
                        <FormattedMessage defaultMessage="File history" id="E+F5l+" />
                    </BadgeButton>
                </MenuSection>
                <MenuSectionHeader>
                    <FormattedMessage defaultMessage="Settings" id="D3idYv" />
                </MenuSectionHeader>
                <MenuSection>
                    <BadgeButton onClick={logout}>
                        <FormattedMessage defaultMessage="Log out" id="PlBReU" />
                    </BadgeButton>
                </MenuSection>
            </MenuContent>
            <ApplicationName>
                <FormattedMessage defaultMessage="Akeneo Supplier Portal" id="CRLCRt" />
            </ApplicationName>
        </Container>
    );
};

const Container = styled.div`
    width: 300px;
    padding: 50px 0 50px 50px;
    border-right: 1px ${getColor('grey60')} solid;
    display: flex;
    flex-direction: column;
`;

const MenuContent = styled.div`
    flex: 1;
    margin-top: 100px;
`;

const MenuSectionHeader = styled.div<{active?: boolean} & AkeneoThemedProps>`
    color: ${({active}) => getColor(active ? 'brand100' : 'grey140')};
    margin-bottom: 20px;

    &:not(:first-child) {
        margin-top: 40px;
    }
`;

const MenuSection = styled.div`
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
`;

const ApplicationName = styled.div`
    color: ${getColor('grey100')};
`;

export {Menu};
