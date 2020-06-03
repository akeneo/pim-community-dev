import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const ToggleButton = () => {
    const translate = useTranslate();

    return (
        <div>
            <input type="checkbox" name="auto-sort"/> {translate('Yes')}
        </div>
    );
};

export default ToggleButton;
