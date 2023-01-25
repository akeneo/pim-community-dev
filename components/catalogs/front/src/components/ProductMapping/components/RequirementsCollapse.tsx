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
                {true && (
                    <Helper inline level='warning'>
                        coucou
                    </Helper>
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
