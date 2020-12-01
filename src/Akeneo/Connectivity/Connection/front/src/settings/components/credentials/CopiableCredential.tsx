import React, {FC, ReactNode, useContext, useRef} from 'react';
import {IconButton} from '../../../common';
import {NotificationLevel, useNotify} from '../../../shared/notify';
import {TranslateContext} from '../../../shared/translate';
import {copyTextToClipboard} from '../../copy-text-to-clipboard';
import {Credential} from './Credential';
import {CopyIcon} from 'akeneo-design-system';

interface Props {
    label: string;
    children: string;
    actions?: ReactNode;
    helper?: ReactNode;
}

export const CopiableCredential: FC<Props> = ({label, children: value, actions, helper}: Props) => {
    const notify = useNotify();
    const translate = useContext(TranslateContext);

    const ref = useRef<HTMLElement>(null);

    const handleCopy = () => {
        if (null === ref.current) {
            return;
        }
        copyTextToClipboard(ref.current);

        notify(
            NotificationLevel.INFO,
            translate('akeneo_connectivity.connection.edit_connection.credentials.flash.copied', {name: label})
        );
    };

    return (
        <Credential
            label={label}
            actions={
                <>
                    {actions}
                    <IconButton
                        type='button'
                        onClick={handleCopy}
                        title={translate('akeneo_connectivity.connection.edit_connection.credentials.action.copy')}
                    >
                        <CopyIcon size={18} />
                    </IconButton>
                </>
            }
            helper={helper}
        >
            <span ref={ref}>{value}</span>
        </Credential>
    );
};
