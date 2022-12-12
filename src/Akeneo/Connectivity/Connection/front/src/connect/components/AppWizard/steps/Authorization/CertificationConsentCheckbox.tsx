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
};

export const CertificationConsentCheckbox: FC<Props> = ({isChecked, onChange}) => {
    const translate = useTranslate();

    return (
        <Container>
            <Checkbox checked={isChecked} onChange={onChange} />
            <LabelContainer>
                <CheckboxLabel>
                    {translate(
                        'akeneo_connectivity.connection.connect.apps.wizard.authorize.certification_consent.label'
                    )}
                </CheckboxLabel>
                <CheckboxSubText>
                    {translate(
                        'akeneo_connectivity.connection.connect.apps.wizard.authorize.certification_consent.subtext'
                    )}
                </CheckboxSubText>
            </LabelContainer>
        </Container>
    );
};
