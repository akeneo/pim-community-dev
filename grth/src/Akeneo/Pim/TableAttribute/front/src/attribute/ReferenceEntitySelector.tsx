import React, {useEffect, useState} from 'react';

import {SelectInput} from 'akeneo-design-system';
import {ReferenceEntityRepository} from '../repositories';
import {getLabel, useRouter, useUserContext} from '@akeneo-pim-community/shared';
import {ReferenceEntity, ReferenceEntityIdentifierOrCode} from '../models';

type ReferenceEntitySelectorProps =
  | {
      placeholder?: string;
      emptyResultLabel: string;
      value?: ReferenceEntityIdentifierOrCode;
      onChange: (value: ReferenceEntityIdentifierOrCode | undefined) => void;
      openLabel: string;
      clearLabel: string;
      readOnly?: boolean;
    }
  | {
      placeholder?: string;
      emptyResultLabel?: string;
      value?: ReferenceEntityIdentifierOrCode;
      onChange?: (value: ReferenceEntityIdentifierOrCode | undefined) => void;
      openLabel?: string;
      clearLabel?: string;
      readOnly: true;
    };

const ReferenceEntitySelector = ({
  emptyResultLabel = '',
  placeholder,
  value,
  onChange,
  openLabel = '',
  clearLabel,
  readOnly = false,
}: ReferenceEntitySelectorProps) => {
  const userContext = useUserContext();
  const router = useRouter();
  const [referenceEntities, setReferenceEntities] = useState<ReferenceEntity[]>([]);
  const catalogLocale = userContext.get('catalogLocale');

  useEffect(() => {
    ReferenceEntityRepository.all(router).then(referenceEntities => {
      setReferenceEntities(referenceEntities);
    });
  }, []);

  const handleChange = (newValue: string | null) => onChange?.(newValue ?? undefined);

  return (
    <SelectInput
      readOnly={readOnly}
      emptyResultLabel={emptyResultLabel}
      onChange={handleChange}
      placeholder={placeholder}
      value={value || null}
      openLabel={openLabel}
      clearLabel={clearLabel}
      clearable={true}>
      {(referenceEntities || []).map((referenceEntity: ReferenceEntity) => {
        const label = getLabel(referenceEntity.labels, catalogLocale, referenceEntity.identifier);
        return (
          <SelectInput.Option key={referenceEntity.identifier} title={label} value={referenceEntity.identifier}>
            {label}
          </SelectInput.Option>
        );
      })}
    </SelectInput>
  );
};

export {ReferenceEntitySelector};
