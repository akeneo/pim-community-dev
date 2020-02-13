import React from 'react';
import {WrongCredentialsCombination} from '../../../model/wrong-credentials-combinations';
import {WrongCombinationWarningList} from './WrongCombinationWarningList';
import {SingleWrongCombinationWarning} from './SingleWrongCombinationWarning';

type Props = {
    username: string;
    wrongCombination: WrongCredentialsCombination;
};

export const WrongCombinationsWarning = ({username, wrongCombination}: Props) => {
    if (1 !== Object.values(wrongCombination.users).length) {
        return <WrongCombinationWarningList combinations={wrongCombination} goodUsername={username} />;
    }

    return (
        <SingleWrongCombinationWarning lastLogin={Object.values(wrongCombination.users)[0]} goodUsername={username} />
    );
};
