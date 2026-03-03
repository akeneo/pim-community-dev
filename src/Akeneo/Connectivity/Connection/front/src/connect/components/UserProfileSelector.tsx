import React, {FC, useEffect, useState} from 'react';
import {useTranslate} from '../../shared/translate';
import {AkeneoThemedProps, Field, getColor, getFontSize, Helper, Link, SelectInput} from 'akeneo-design-system';
import styled from 'styled-components';
import {useFetchUserProfiles} from '../hooks/use-fetch-user-profiles';

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

const LinkButton = styled.button<AkeneoThemedProps>`
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
    &:disabled {
        background-color: ${getColor('grey', 80)};
        cursor: not-allowed;
    }
`;

type Props = {
    selectedProfile: string | null;
    handleOnSelectChange: (selectedValue: string | null) => void;
    handleClick: () => void;
};

type UserProfile = {
    code: string;
    label: string;
};

export const UserProfileSelector: FC<Props> = ({selectedProfile, handleOnSelectChange, handleClick}) => {
    const translate = useTranslate();
    const [userProfiles, setUserProfiles] = useState<UserProfile[]>([]);
    const fetchUserProfiles = useFetchUserProfiles();

    useEffect(() => {
        fetchUserProfiles().then(setUserProfiles);
    }, [fetchUserProfiles]);

    return (
        <>
            <Caption>{translate('pim_user.profile.caption')}</Caption>

            <UserProfileField label={translate('pim_user_management.entity.user.properties.profile')}>
                <ProfileSelect
                    value={selectedProfile}
                    emptyResultLabel={translate('pim_user.profile.selector.not_found')}
                    openLabel={translate('pim_common.open')}
                    placeholder={translate('pim_user.profile.selector.placeholder')}
                    onChange={handleOnSelectChange}
                    clearable={true}
                >
                    {userProfiles.map((profile: UserProfile) => (
                        <SelectInput.Option key={profile.code} title={translate(profile.label)} value={profile.code}>
                            {translate(profile.label)}
                        </SelectInput.Option>
                    ))}
                </ProfileSelect>
                <Helper level='info'>
                    <Link
                        href='https://help.akeneo.com/pim/serenity/articles/manage-your-account.html#your-general-information'
                        target='_blank'
                    >
                        {translate('pim_user.profile.why_is_it_needed')}
                    </Link>
                </Helper>
            </UserProfileField>

            <LinkButton role='link' tabIndex='0' onClick={handleClick} disabled={null === selectedProfile}>
                {translate('pim_user.profile.save_button')}
            </LinkButton>
        </>
    );
};
