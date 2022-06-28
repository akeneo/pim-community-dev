import React from 'react';
import styled from 'styled-components';
import {OnboarderLogo} from '../../components';
import {FormattedMessage} from 'react-intl';
import {BadgeButton} from '../../components/BadgeButton';
import {AkeneoThemedProps, getColor, getFontSize} from 'akeneo-design-system';
import illustration from '../../assets/images/Factory.svg';

const FilesDropping = () => {
    return (
        <Container>
            <Menu>
                <OnboarderLogo width={164} />
                <MenuContent>
                    <MenuSectionHeader active={true}>
                        <FormattedMessage defaultMessage="File enrichment" id="gxGnf7" />
                    </MenuSectionHeader>
                    <MenuSection>
                        <BadgeButton isActive={true}>
                            <FormattedMessage defaultMessage="File transfer" id="l2OoTB" />
                        </BadgeButton>
                        <BadgeButton>
                            <FormattedMessage defaultMessage="File history" id="E+F5l+" />
                        </BadgeButton>
                    </MenuSection>
                    <MenuSectionHeader>
                        <FormattedMessage defaultMessage="Settings" id="D3idYv" />
                    </MenuSectionHeader>
                    <MenuSection>
                        <BadgeButton>
                            <FormattedMessage defaultMessage="Log out" id="PlBReU" />
                        </BadgeButton>
                    </MenuSection>
                </MenuContent>
                <ApplicationName>
                    <FormattedMessage defaultMessage="Akeneo Onboarder Serenity" id="+udE9J" />
                </ApplicationName>
            </Menu>
            <Content>
                <WelcomeMessage>
                    <WelcomeMessageTitle>
                        <FormattedMessage defaultMessage="Akeneo Onboarder Assistant" id="vf3HzI" />
                    </WelcomeMessageTitle>
                    <p>
                        <FormattedMessage
                            defaultMessage="Welcome on your personnal data onboarding service."
                            id="7L75AN"
                        />
                    </p>
                    <p>
                        <FormattedMessage
                            id="IEEx9e"
                            defaultMessage="Please share below your product information in a completed <b>XLSX file.</b>"
                            values={{
                                b: chunks => <b>{chunks}</b>,
                            }}
                        />
                    </p>
                </WelcomeMessage>
                <WorkInProgress>
                    <Illustration src={illustration} />
                    <FormattedMessage defaultMessage="Work in progress" id="UvDPcD" />
                </WorkInProgress>
            </Content>
        </Container>
    );
};

const Container = styled.div`
    display: flex;
    height: 100vh;
`;

const Menu = styled.div`
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

const Content = styled.div`
    flex: 1;
`;

const WelcomeMessage = styled.div`
    padding: 50px;
    background-color: ${getColor('blue10')};
    p {
        font-size: 25px;
        color: ${getColor('grey140')};
        line-height: 30px;
    }
`;
const WelcomeMessageTitle = styled.div`
    margin-bottom: 10px;
    color: ${getColor('grey120')};
`;

const WorkInProgress = styled.div`
    font-size: ${getFontSize('title')};
    color: ${getColor('grey140')};
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 100px;
`;
const Illustration = styled.img`
    width: 280px;
`;

export {FilesDropping};
