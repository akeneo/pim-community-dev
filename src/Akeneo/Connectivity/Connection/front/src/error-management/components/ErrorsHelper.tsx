import React, {FC} from 'react';
import {Helper, HelperLink, HelperTitle} from '../../common';
import illustrationUrl from '../../common/assets/illustrations/AddingValues.svg';
import {Translate, useTranslate} from '../../shared/translate';

type Props = {
    errorCount: number;
};

const ErrorsHelper: FC<Props> = ({errorCount}) => {
    const translate = useTranslate();

    return (
        <Helper illustrationUrl={illustrationUrl}>
            <HelperTitle>
                <div
                    dangerouslySetInnerHTML={{
                        __html: translate(
                            'akeneo_connectivity.connection.error_management.connection_monitoring.helper.title',
                            {count: `<span class='AknConnectivityConnection-helper--highlight'>${errorCount}</span>`},
                            errorCount
                        ),
                    }}
                />
            </HelperTitle>

            <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.helper.description' />
            <br />

            <HelperLink href='https://api.akeneo.com/documentation/responses.html#422-error'>
                <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.helper.link' />
            </HelperLink>
        </Helper>
    );
};

export {ErrorsHelper};
