import React, {FC, useState} from 'react';
import {Badge, Collapse, Helper} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Target} from '../models/Target';
import styled from 'styled-components';

type Props = {
    selectedTarget: Target;
};

type Constraint = {
    key: string;
    value: string | number;
};

const WarningHelper = styled(Helper)`
    margin-bottom: 10px;
`;

export const RequirementsCollapse: FC<Props> = ({selectedTarget}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState(true);
    const acceptedConstraints: string[] = ['minLength', 'maxLength'];
    const translationKey = 'akeneo_catalogs.product_mapping.source.requirements.constraints';

    const constraintsToShow: Constraint[] = [];

    for (const acceptedConstraint of acceptedConstraints) {
        if (
            acceptedConstraint in selectedTarget &&
            undefined !== selectedTarget[acceptedConstraint] &&
            null !== selectedTarget[acceptedConstraint]
        ) {
            constraintsToShow.push({
                key: acceptedConstraint,
                value: selectedTarget[acceptedConstraint],
            } as Constraint);
        }
    }

    const shouldDisplayWarning = constraintsToShow.length > 0;

    if ((undefined === selectedTarget.description || null === selectedTarget.description) && !shouldDisplayWarning) {
        return null;
    }

    return (
        <>
            <Collapse
                collapseButtonLabel='Collapse'
                label={
                    <>
                        {translate('akeneo_catalogs.product_mapping.source.requirements.title')}{' '}
                        <Badge level='secondary'>
                            {translate('akeneo_catalogs.product_mapping.source.requirements.help')}
                        </Badge>
                    </>
                }
                isOpen={isOpen}
                onCollapse={setIsOpen}
            >
                {shouldDisplayWarning && (
                    <WarningHelper inline level='warning'>
                        {constraintsToShow.map((constraint, i) => (
                            <p key={i}>
                                {translate(
                                    `${translationKey}.${constraint.key}`,
                                    {
                                        value: constraint.value,
                                    },
                                    parseInt(constraint.value.toString())
                                )}
                            </p>
                        ))}
                    </WarningHelper>
                )}
                {selectedTarget.description && (
                    <Helper inline level='info'>
                        {selectedTarget.description}
                    </Helper>
                )}
            </Collapse>
        </>
    );
};
