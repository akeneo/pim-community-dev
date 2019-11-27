import React, {FC, ReactNode, useContext, useRef} from 'react';
import {IconButton} from '../../../common';
import {DuplicateIcon} from '../../../common/icons';
import {NotificationLevel, useNotify} from '../../../shared/notify';
import {TranslateContext} from '../../../shared/translate';
import {copyTextToClipboard} from '../../copy-text-to-clipboard';
import {Credential} from './Credential';

interface Props {
    label: string;
    children: string;
    actions?: ReactNode;
}

export const CopiableCredential: FC<Props> = ({label, children: value, actions}: Props) => {
    const notify = useNotify();
    const translate = useContext(TranslateContext);

    const ref = useRef<HTMLElement>(null);

    const handleCopy = () => {
        if (null === ref.current) {
            return;
        }
        copyTextToClipboard(ref.current);

        notify(NotificationLevel.INFO, translate('akeneo_apps.edit_app.credentials.flash.copied', {name: label}));
    };

    return (
        <Credential
            label={label}
            actions={
                <>
                    <IconButton onClick={handleCopy} title={translate('akeneo_apps.edit_app.credentials.action.copy')}>
                        <DuplicateIcon />
                    </IconButton>
                    {actions}
                </>
            }
        >
            <span ref={ref}>{value}</span>
        </Credential>
    );
};
