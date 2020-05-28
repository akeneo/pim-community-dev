import React from 'react';
import {EmptyState} from '../../common';
import {ApplyButton} from '../../common/components';
import {Translate} from '../../shared/translate';

export const UserSurvey = () => {
    const handleClick = () => window.open('https://links.akeneo.com/surveys/connection-dashboard', '_blank')?.focus();

    return (
        <EmptyState.EmptyState>
            <EmptyState.Illustration illustration='survey' width={128} />
            <EmptyState.Heading>
                <Translate id='akeneo_connectivity.connection.dashboard.user_survey.title' />
            </EmptyState.Heading>
            <EmptyState.Caption fontSize='big'>
                <Translate id='akeneo_connectivity.connection.dashboard.user_survey.content' />
            </EmptyState.Caption>
            <br />
            <ApplyButton onClick={handleClick}>
                <Translate id='akeneo_connectivity.connection.dashboard.user_survey.button' />
            </ApplyButton>
        </EmptyState.EmptyState>
    );
};
