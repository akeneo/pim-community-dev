import React, {FC} from 'react';
import styled from 'styled-components';
import certifiedIcon from '../../common/assets/icons/certified.svg';
import {getColor, getFontSize, Button, Link} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {Extension} from '../../model/extension';

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
    gap: 20px 20px;
    grid-template-columns: 100px 1fr 50px;
    grid-template-rows: 1fr 50px;
    grid-template-areas:
        'logo text text'
        'actions actions certified';
`;

const Logo = styled.img`
    display: inline-block;
    width: 100px;
    height: auto;
    max-height: 100px;
    border: 1px ${getColor('grey', 40)} solid;
    grid-area: logo;
`;

const TextInformation = styled.div`
    grid-area: text;
    max-width: 100%;
`;

const Name = styled.h1`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('big')};
    font-weight: bold;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
`;

const Author = styled.h3`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('big')};
    font-weight: normal;
    margin: 0;
    margin-bottom: 5px;

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

const Description = styled.p`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('default')};
    font-weight: normal;
    margin-top: 10px;
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
    extension: Extension;
};

const MarketplaceCard: FC<Props> = ({extension}) => {
    const translate = useTranslate();

    const normalizedDescription =
        null !== extension.description && extension.description.length > 150 ? (
            <>
                {extension.description.substring(0, 139)}&hellip;&nbsp;
                <Link decorated href={extension.url} target='_blank'>
                    {translate('akeneo_connectivity.connection.connect.marketplace.card.read_more')}
                </Link>
            </>
        ) : (
            extension.description
        );

    return (
        <CardContainer>
            <Logo src={extension.logo} alt={extension.name} />
            <TextInformation>
                <Name>{extension.name}</Name>
                <Author>
                    {translate('akeneo_connectivity.connection.connect.marketplace.card.developed_by')}
                    &nbsp;
                    {extension.author}
                </Author>
                {extension.partner && <PartnerTag>{extension.partner}</PartnerTag>}
                {extension.categories.length > 0 && <Tag>{extension.categories[0]}</Tag>}
                <Description>{normalizedDescription}</Description>
            </TextInformation>
            {extension.certified && <CertifiedIcon />}
            <Actions>
                <Button ghost level='tertiary' href={extension.url} target='_blank'>
                    {translate('akeneo_connectivity.connection.connect.marketplace.card.more_info')}
                </Button>
            </Actions>
        </CardContainer>
    );
};

export {MarketplaceCard, Grid};
