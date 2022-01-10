import React, {FC, ReactNode} from 'react';
import styled from 'styled-components';
import certifiedIcon from '../../common/assets/icons/certified.svg';
import {getColor, getFontSize, Button, Link} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {Extension} from '../../model/extension';
import {App} from '../../model/app';

const CardContainer = styled.div`
    padding: 20px;
    border: 1px ${getColor('grey', 40)} solid;
    display: grid;
    gap: 20px 20px;
    grid-template-columns: 50px 50px 1fr 1px; /* 1px column only for ellipsis working */
    grid-template-rows: 1fr 50px;
    grid-template-areas:
        'logo logo text text'
        'certified actions actions actions';
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
    justify-self: start;
    align-self: end;
`;

const Actions = styled.div`
    grid-area: actions;
    justify-self: end;
    align-self: end;
    text-align: right;

    & > * {
        margin-left: 10px;
    }
`;

type Props = {
    item: Extension | App;
    additionalActions?: ReactNode[];
};

export const MarketplaceCard: FC<Props> = ({item, additionalActions}) => {
    const translate = useTranslate();

    const normalizedDescription =
        null !== item.description && item.description.length > 150 ? (
            <>
                {item.description.substring(0, 139)}&hellip;&nbsp;
                <Link decorated href={item.url} target='_blank'>
                    {translate('akeneo_connectivity.connection.connect.marketplace.card.read_more')}
                </Link>
            </>
        ) : (
            item.description
        );

    return (
        <CardContainer>
            <LogoContainer>
                <Logo src={item.logo} alt={item.name} />
            </LogoContainer>
            <TextInformation>
                <Name>{item.name}</Name>
                <Author>
                    {translate('akeneo_connectivity.connection.connect.marketplace.card.developed_by', {
                        author: item.author,
                    })}
                </Author>
                {item.partner && <PartnerTag>{item.partner}</PartnerTag>}
                {item.categories.length > 0 && <Tag>{item.categories[0]}</Tag>}
                <Description>{normalizedDescription}</Description>
            </TextInformation>
            {item.certified && <CertifiedIcon />}
            <Actions>
                <Button ghost level='tertiary' href={item.url} target='_blank'>
                    {translate('akeneo_connectivity.connection.connect.marketplace.card.more_info')}
                </Button>
                {additionalActions}
            </Actions>
        </CardContainer>
    );
};
