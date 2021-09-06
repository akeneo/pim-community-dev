import React, {FC} from 'react';
import styled from 'styled-components';
import certifiedIcon from '../../../common/assets/icons/certified.svg';
import {getColor, getFontSize, Button, Link} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import {ConnectedApp} from "../../../model/connected-app";

const Grid = styled.section`
    margin: 20px 0;
    display: grid;
    grid-template-columns: repeat(2, minmax(300px, 1fr));
    gap: 20px;
`;

const CardContainer = styled.div`
    padding: 20px;
    border: 1px ${getColor('grey', 40)} solid;
    display: grid;
    gap: 0 20px;
    grid-template-columns: 100px 1fr 50px;
    grid-template-rows: 50px 50px;
    grid-template-areas:
        'logo text text'
        'logo actions certified';
`;

const Logo = styled.img`
    margin: auto;
    max-height: 98px;
    max-width: 98px;
`;

const LogoContainer = styled.div`
    width: 100px;
    height: 100px;
    grid-area: logo;
    border: 1px ${getColor('grey', 40)} solid;
    display: flex;
`;

const TextInformation = styled.div`
    grid-area: text;
    max-width: 100%;
    height: 50px;
`;

const Name = styled.h1`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('big')};
    font-weight: bold;
    margin: 0 0 5px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
`;

const Tag = styled.span`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('small')};
    text-transform: uppercase;
    font-weight: normal;

    border: 1px ${getColor('grey', 120)} solid;
    background: ${getColor('white')};
    border-radius: 2px;

    display: inline-block;
    line-height: ${getFontSize('small')};
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: calc(50% - 3px);

    padding: 2px 5px;
    margin-right: 5px;
`;

const PartnerTag = styled(Tag)`
    color: ${getColor('purple', 140)};
    border: 1px ${getColor('purple', 100)} solid;
`;

const CertifiedIcon = styled.div`
    background-image: url(${certifiedIcon});
    background-position: center;
    background-repeat: no-repeat;
    background-size: 50px;
    width: 50px;
    height: 50px;

    grid-area: certified;
    justify-self: end;
    align-self: end;
`;

const Actions = styled.div`
    grid-area: actions;
    justify-self: start;
    align-self: end;

    & > * {
        margin-right: 10px;
    }
`;

type Props = {
    item: ConnectedApp;
};

const ConnectedAppCard: FC<Props> = ({item}) => {
    const translate = useTranslate();

    return (
        <CardContainer>
            <LogoContainer>
                <Logo src={item.logo} alt={item.name} />
            </LogoContainer>
            <TextInformation>
                <Name>{item.name}</Name>
                {item.partner && <PartnerTag>{item.partner}</PartnerTag>}
                {item.categories.length > 0 && <Tag>{item.categories[0]}</Tag>}
            </TextInformation>
            <Actions>
                <Button ghost level='tertiary' href={item.url} target='_blank'>
                    {translate('akeneo_connectivity.connection.connect.connected_apps.card.manage_app')}
                </Button>
                <Button level='secondary' href={item.url} target='_blank'>
                    {translate('akeneo_connectivity.connection.connect.connected_apps.card.open_app')}
                </Button>
            </Actions>
            {item.certified && <CertifiedIcon />}
        </CardContainer>
    );
};

export {ConnectedAppCard, Grid};
