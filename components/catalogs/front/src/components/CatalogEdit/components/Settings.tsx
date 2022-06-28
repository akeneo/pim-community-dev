import React, {FC, useState} from 'react';
import {BooleanInput, Field} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const EnableField = styled(Field)`
    margin: 20px 0;
`;

const Settings: FC = () => {
    const translate = useTranslate();
    const [enabled, setEnabled] = useState(false);

    return (
        <>
            <EnableField label={translate('akeneo_catalogs.settings.fields.enabled')}>
                <BooleanInput
                    noLabel={translate('akeneo_catalogs.settings.inputs.no')}
                    value={enabled}
                    yesLabel={translate('akeneo_catalogs.settings.inputs.yes')}
                    readOnly={false}
                    onChange={() => setEnabled(!enabled)}
                />
            </EnableField>
        </>
    );
};

export {Settings};
