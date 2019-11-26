import React, {FC, ReactNode} from 'react';
import {CredentialListItem} from './CredentialList';

interface Props {
    label: string;
    children: ReactNode;
    actions?: ReactNode;
}

export const Credential: FC<Props> = ({label, children: value, actions}: Props) => {
    return (
        <CredentialListItem label={label} actions={actions}>
            {value}
        </CredentialListItem>
    );
};
