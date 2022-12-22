import {Checkbox, getColor, getFontSize} from 'akeneo-design-system';
import React, {FC} from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../../../../shared/translate';

const Container = styled.div`
    display: flex;
`;

const CheckboxLabel = styled.p`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('default')};
    font-weight: normal;
    margin-bottom: 8px;
    width: 300px;
`;

const LabelContainer = styled.div`
    margin-left: 10px;
`;

const CheckboxSubText = styled.p`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('default')};
    font-weight: normal;
    font-style: italic;
    width: 340px;
`;

type Props = {
    isChecked: boolean;
    onChange: (newValue: boolean) => void;
    appUrl: string | null;
    displayCheckbox: boolean;
};

export const ConsentCheckbox: FC<Props> = ({isChecked, onChange, appUrl, displayCheckbox}) => {
    const translate = useTranslate();

    const label = translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.consent.label', {
        app_marketplace_page: `<a href='${
            appUrl ?? '#'
        }' target='_blank' class="AknConnectivityConnection-link">${translate(
            'akeneo_connectivity.connection.connect.apps.wizard.authentication.consent.app_marketplace_page'
        )}</a>`,
    });

    const subtext = translate('akeneo_connectivity.connection.connect.apps.wizard.authentication.consent.subtext', {
        // eslint-disable-next-line max-len
        contact_us: `<a href='https://www.akeneo.com/contact-us/' target='_blank' class="AknConnectivityConnection-link">${translate(
            'akeneo_connectivity.connection.connect.apps.wizard.authentication.consent.contact_us'
        )}</a>`,
    });

    return (
        <Container>
            {displayCheckbox && <Checkbox checked={isChecked} onChange={onChange} />}
            <LabelContainer>
                {displayCheckbox && <CheckboxLabel dangerouslySetInnerHTML={{__html: label}} />}
                <CheckboxSubText dangerouslySetInnerHTML={{__html: subtext}} />
            </LabelContainer>
        </Container>
    );
};
