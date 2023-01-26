import React, {FC, useState} from 'react';
import {Badge, Collapse, Helper} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Target} from '../models/Target';

type Props = {
    target: Target;
};

export const RequirementsCollapse: FC<Props> = ({target}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState(true);
    const constraintKeys: string[] = ['minLength', 'maxLength'];
    const targetKeys = Object.keys(target);
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
                {
                    <Helper inline level='warning'>
                        <ul>
                            {targetKeys.map((targetKey, i) => {
                                if (constraintKeys.includes(targetKey)) {
                                    return (
                                        <li key={i}>
                                            {translate(
                            `akeneo_catalogs.product_mapping.source.requirements_contraints.${targetKey}`,
                                                {value: target[targetKey] || 0}
                                            )}
                                        </li>
                                    );
                                }
                            })}
                        </ul>
                    </Helper>
                }
                {target.description && (
                    <Helper inline level='info'>
                        {target.description}
                    </Helper>
                )}
            </Collapse>
        </>
    );
};
