import React, {FC, useState} from 'react';
import {Badge, Collapse, Helper} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Target} from '../models/Target';
import styled from 'styled-components';

type Props = {
    target: Target;
};

type Constraint = {
    key: string;
    value: string | number;
};

const WarningHelper = styled(Helper)`
    margin-bottom: 10px;
`;

export const RequirementsCollapse: FC<Props> = ({target}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState(true);
    const constraintKeys: string[] = ['minLength', 'maxLength'];
    const translationKey = 'akeneo_catalogs.product_mapping.source.requirements.constraints';

    const constraints: Constraint[] = [];

    (Object.keys(target) as Array<keyof Target>).map(targetKey => {
        if (constraintKeys.includes(targetKey) && undefined !== target[targetKey]) {
            constraints.push({
                key: targetKey,
                value: target[targetKey],
            } as Constraint);
        }
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
                                    parseInt(constraint.value.toString())
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
