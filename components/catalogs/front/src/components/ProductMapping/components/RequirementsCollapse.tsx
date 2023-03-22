import React, {FC, useState} from 'react';
import {Badge, Collapse, Helper} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Target} from '../models/Target';
import styled from 'styled-components';

const ACCEPTED_CONSTRAINTS = ['minLength', 'maxLength', 'pattern', 'minimum', 'maximum', 'enum'] as const;

type Props = {
    target: Target;
};

type TargetWithOnlyConstraints = Pick<Target, typeof ACCEPTED_CONSTRAINTS[number]>;
type TargetWith<K extends keyof TargetWithOnlyConstraints> = Required<Pick<TargetWithOnlyConstraints, K>>;

type Constraint = {
    key: keyof TargetWithOnlyConstraints;
    value: string | number;
};

const WarningHelper = styled(Helper)`
    margin-bottom: 10px;
`;

export const RequirementsCollapse: FC<Props> = ({target}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState(true);
    const translationKey = 'akeneo_catalogs.product_mapping.source.requirements.constraints';

    const constraints: Constraint[] = ACCEPTED_CONSTRAINTS.filter(
        constraint => target[constraint] !== undefined && target[constraint] !== null
    ).map(constraint => {
        let value = (target as TargetWith<typeof constraint>)[constraint];
        if (Array.isArray(value)) {
            value = value.join(', ');
        }
        return {
            key: constraint,
            value: value,
        };
    });

    const shouldDisplayWarning = constraints.length > 0;

    if ((undefined === target.description || null === target.description) && !shouldDisplayWarning) {
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
                        {constraints.map((constraint, i) => (
                            <p key={i}>
                                {translate(
                                    `${translationKey}.${constraint.key}`,
                                    {
                                        value: constraint.value,
                                    },
                                    typeof constraint.value === 'string' ? 0 : constraint.value
                                )}
                            </p>
                        ))}
                    </WarningHelper>
                )}
                {target.description && (
                    <Helper inline level='info'>
                        {target.description}
                    </Helper>
                )}
            </Collapse>
        </>
    );
};
