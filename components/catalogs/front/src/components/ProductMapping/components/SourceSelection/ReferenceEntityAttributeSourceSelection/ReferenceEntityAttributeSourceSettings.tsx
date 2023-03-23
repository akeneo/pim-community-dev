import React, {FC} from 'react';
import {Source} from '../../../models/Source';
import {SourceErrors} from '../../../models/SourceErrors';
import {ReferenceEntityAttribute} from '../../../models/ReferenceEntityAttribute';
import {SelectReferenceEntityAttributeChannelDropdown} from './SelectReferenceEntityAttributeChannelDropdown';
import {SelectReferenceEntityAttributeLocaleDropdown} from './SelectReferenceEntityAttributeLocaleDropdown';
import {SelectReferenceEntityAttributeChannelLocaleDropdown} from './SelectReferenceEntityAttributeChannelLocaleDropdown';
import {ArrowIcon, getColor} from 'akeneo-design-system';
import styled from 'styled-components';

const Bullet = styled(ArrowIcon)`
    color: ${getColor('grey', 100)};
`;

const BulletLine = styled.div`
    display: flex;
    flex-direction: row;
    flex: auto;
    gap: 8px;
    margin-top: 5px;
    padding-left: 10px;
    max-width: 460px;
    align-items: center;
`;

type Props = {
    source: Source;
    referenceEntityAttribute: ReferenceEntityAttribute;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
};

export const ReferenceEntityAttributeSourceSettings: FC<Props> = ({
    source,
    referenceEntityAttribute,
    errors,
    onChange,
}) => {
    return (
        <>
            {referenceEntityAttribute.scopable && (
                <BulletLine>
                    <Bullet />
                    <SelectReferenceEntityAttributeChannelDropdown
                        source={source}
                        onChange={onChange}
                        error={errors?.parameters?.sub_scope}
                    />
                </BulletLine>
            )}
            {referenceEntityAttribute.localizable && !referenceEntityAttribute.scopable && (
                <BulletLine>
                    <Bullet />
                    <SelectReferenceEntityAttributeLocaleDropdown
                        source={source}
                        onChange={onChange}
                        error={errors?.parameters?.sub_locale}
                    />
                </BulletLine>
            )}
            {referenceEntityAttribute.localizable && referenceEntityAttribute.scopable && (
                <BulletLine>
                    <Bullet />
                    <SelectReferenceEntityAttributeChannelLocaleDropdown
                        source={source}
                        onChange={onChange}
                        error={errors?.parameters?.sub_locale}
                        disabled={source.parameters?.sub_scope === null}
                    />
                </BulletLine>
            )}
        </>
    );
};
