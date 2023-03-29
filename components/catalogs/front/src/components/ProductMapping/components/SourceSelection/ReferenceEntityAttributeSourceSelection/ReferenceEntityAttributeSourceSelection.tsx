import React, {FC} from 'react';
import {Source} from '../../../models/Source';
import {Target} from '../../../models/Target';
import {SourceErrors} from '../../../models/SourceErrors';
import {useReferenceEntityAttribute} from '../../../hooks/useReferenceEntityAttribute';
import {ReferenceEntityAttributeSourceSettings} from './ReferenceEntityAttributeSourceSettings';
import {SelectReferenceEntityAttributeSourceDropdown} from './SelectReferenceEntityAttributeSourceDropdown';
import {ReferenceEntityAttribute} from '../../../models/ReferenceEntityAttribute';

type Props = {
    source: Source;
    target: Target;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
    referenceEntityIdentifier: string;
};

export const ReferenceEntityAttributeSourceSelection: FC<Props> = ({
    source,
    target,
    errors,
    onChange,
    referenceEntityIdentifier,
}) => {
    const {data: referenceEntityAttribute} = useReferenceEntityAttribute(source?.parameters?.sub_source ?? '');

    const handleReferenceEntityAttributeSourceSelection = (
        selectedReferenceEntityAttribute: ReferenceEntityAttribute
    ) =>
        onChange({
            ...source,
            parameters: {
                ...source.parameters,
                sub_source: selectedReferenceEntityAttribute.identifier,
                sub_scope: null,
                sub_locale: null,
            },
        });

    return (
        <>
            <SelectReferenceEntityAttributeSourceDropdown
                selectedIdentifier={source?.parameters?.sub_source ?? ''}
                target={target}
                referenceEntityIdentifier={referenceEntityIdentifier}
                onChange={handleReferenceEntityAttributeSourceSelection}
                error={errors?.parameters?.sub_source}
            />
            {undefined !== referenceEntityAttribute && null !== source && (
                <ReferenceEntityAttributeSourceSettings
                    source={source}
                    referenceEntityAttribute={referenceEntityAttribute}
                    errors={errors}
                    onChange={onChange}
                />
            )}
        </>
    );
};
