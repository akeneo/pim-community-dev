import React, {FC, useState} from 'react';
import {Badge, Collapse, Helper} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Target} from '../models/Target';
import styled from 'styled-components';

type Props = {
    target: Target;
};

const WarningHelper = styled(Helper)`
    margin-bottom: 10px;
`;

export const RequirementsCollapse: FC<Props> = ({target}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState(true);
    const constraintKeys: string[] = ['minLength', 'maxLength'];
    const targetKeys = Object.keys(target) as Array<keyof Target>;
    const translationKey = 'akeneo_catalogs.product_mapping.source.requirements.constraints';
    const hasWarning = constraintKeys.filter(value => (targetKeys as string[]).includes(value));

    if (undefined === target.description || null === target.description) {
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
                {hasWarning && (
                    <WarningHelper inline level='warning'>
                        {targetKeys.map((targetKey, i) => {
                            if (constraintKeys.includes(targetKey)) {
                                return (
                                    <p key={i}>
                                        {translate(`${translationKey}.${targetKey}`, {
                                            value: target[targetKey] || '',
                                        })}
                                    </p>
                                );
                            }
                        })}
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
