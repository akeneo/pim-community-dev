import React, {FC} from 'react';
import {Source} from '../../models/Source';
import {Target} from '../../models/Target';
import {SourceErrors} from '../../models/SourceErrors';
import {useAttribute} from '../../../../hooks/useAttribute';
import {Attribute} from '../../../../models/Attribute';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SourceSettings} from './SourceSettings';
import {createSourceFromAttribute} from '../../utils/createSourceFromAttribute';
import {SelectSourceAttributeDropdown} from './SelectSourceAttributeDropdown';
import {SourceSectionTitle} from '../SourceSectionTitle';

type Props = {
    source: Source | null;
    target: Target;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
};

export const SourceSelection: FC<Props> = ({source, target, errors, onChange}) => {
    const translate = useTranslate();
    const {data: attribute} = useAttribute(source?.source ?? '');

    const handleSourceAttributeSelection = (selectedAttribute: Attribute) => {
        let source = createSourceFromAttribute(selectedAttribute);

        if ('string' === target.type && (null === target.format || !['uri', 'date-time'].includes(target.format))) {
            source = {...source, parameters: {...source.parameters, default: null}};
        }

        onChange(source);
    };

    return (
        <>
            <SourceSectionTitle order={1}>
                {translate('akeneo_catalogs.product_mapping.source.title')}
            </SourceSectionTitle>
            <SelectSourceAttributeDropdown
                selectedCode={source?.source ?? ''}
                target={target}
                onChange={handleSourceAttributeSelection}
                error={errors?.source}
            />
            {undefined !== attribute && null !== source && (
                <SourceSettings source={source} attribute={attribute} errors={errors} onChange={onChange} />
            )}
        </>
    );
};
