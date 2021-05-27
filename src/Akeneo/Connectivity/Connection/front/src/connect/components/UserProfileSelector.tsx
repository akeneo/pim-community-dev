import React, {FC, useContext, useEffect, useState} from 'react';
import {useTranslate} from '../../shared/translate';
import {AkeneoThemedProps, Field, getColor, getFontSize, Helper, Link, SelectInput} from 'akeneo-design-system';
import styled from 'styled-components';
import {useFetchUserProfiles} from '../hooks/use-fetch-user-profiles';

type UserProfileEntry = {
    code: string;
    label: string;
};

const ProfileSelect = styled(SelectInput)`
    height: 40px;
`;

const UserProfileField = styled(Field)`
    width: 400px;
    margin: 0 auto;
    text-align: left;
`;

const Caption = styled.p`
    font-size: 23px;
    line-height: 1.2em;
`;

//TODO refactor ?
const LinkButton = styled.a<AkeneoThemedProps>`
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border-width: 1px;
    font-size: ${getFontSize('default')};
    font-weight: 400;
    text-transform: uppercase;
    border-radius: 16px;
    border-style: none;
    padding: 0 15px;
    height: 32px;
    cursor: pointer;
    font-family: inherit;
    transition: background-color 0.1s ease;
    outline-style: none;
    text-decoration: none;
    white-space: nowrap;
    margin-top: 10px;

    color: ${getColor('white')};
    background-color: ${getColor('purple', 100)};

    &:hover {
        background-color: ${getColor('purple', 120)};
    }

    &:active {
        background-color: ${getColor('purple', 140)};
    }
    &:focus {
        box-shadow: 0 0 0 2px ${getColor('blue', 40)};
    }
`;

export const UserProfileSelector: FC = () => {
    const translate = useTranslate();
    // const [userProfileEntries, setUserProfileEntries] = useState<UserProfileEntry[]>([]);
    const userProfileEntries = useFetchUserProfiles();

    const marketplaceUrl = '#';

    return (
        <>
            <Caption>{translate('pim_user.profile.caption')}</Caption>

            <UserProfileField label={translate('pim_user_management.entity.user.properties.profile')}>
                <ProfileSelect value={null}
                               emptyResultLabel={translate('pim_user.profile.selector.not_found')}
                               placeholder={translate('pim_user.profile.selector.placeholder')}
                               onChange={() => null}>
                    {userProfileEntries.map((profileEntry: UserProfileEntry) =>
                        <SelectInput.Option
                            key={profileEntry.code}
                            title={translate(profileEntry.label)}
                            value={profileEntry.code}
                        >{translate(profileEntry.label)}
                        </SelectInput.Option>
                    )}
                </ProfileSelect>
                <Helper level="info">
                    {/*TODO update link*/}
                    <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
                        {translate('pim_user.profile.why_is_it_needed')}
                    </Link>
                </Helper>
            </UserProfileField>

            {/* TODO save and fetch marketplaceUrl*/}
            <LinkButton href={marketplaceUrl} target='_blank' role='link' tabIndex='0'>
                {translate('pim_user.profile.save_button')}
            </LinkButton>
        </>

    );
};


