import React, {FC, useCallback, useMemo, useState} from 'react';
import {Dropdown, Field, GroupsIllustration, Helper, Search, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ReferenceEntityAttribute} from '../../../models/ReferenceEntityAttribute';
import styled from 'styled-components';
import {Target} from '../../../models/Target';
import {useReferenceEntityAttribute} from '../../../hooks/useReferenceEntityAttribute';
import {useReferenceEntityAttributes} from '../../../hooks/useReferenceEntityAttributes';

const SelectReferenceEntityAttributeDropdownField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    selectedIdentifier: string;
    target: Target;
    referenceEntityIdentifier: string;
    onChange: (value: ReferenceEntityAttribute) => void;
    error: string | undefined;
};

export const SelectReferenceEntityAttributeSourceDropdown: FC<Props> = ({
    selectedIdentifier,
    target,
    referenceEntityIdentifier,
    onChange,
    error,
}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState<boolean>(false);
    const [search, setSearch] = useState<string>('');
    const {data: referenceEntityAttributes} = useReferenceEntityAttributes(referenceEntityIdentifier, target);
    const {data: referenceEntityAttribute} = useReferenceEntityAttribute(selectedIdentifier);

    const handleReferenceEntityAttributeSelection = useCallback(
        (referenceEntityAttribute: ReferenceEntityAttribute) => {
            onChange(referenceEntityAttribute);
            setIsOpen(false);
        },
        [onChange]
    );

    const openDropdown = useCallback(e => {
        e.preventDefault();
        setIsOpen(true);
    }, []);

    const filteredReferenceEntityAttributes: ReferenceEntityAttribute[] = useMemo(() => {
        const regex = new RegExp(search, 'i');
        return (
            referenceEntityAttributes?.filter((referenceEntityAttribute: ReferenceEntityAttribute) =>
                referenceEntityAttribute.label.match(regex)
            ) ?? []
        );
    }, [referenceEntityAttributes, search]);

    return (
        <>
            <SelectReferenceEntityAttributeDropdownField
                label={translate(
                    'akeneo_catalogs.product_mapping.source.select_source_reference_entity_attribute.label'
                )}
            >
                <Dropdown>
                    <SelectInput
                        onMouseDown={openDropdown}
                        emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                        openLabel={translate('akeneo_catalogs.common.select.open')}
                        value={referenceEntityAttribute?.label ?? ''}
                        placeholder={translate(
                            'akeneo_catalogs.product_mapping.source.select_source_reference_entity_attribute.placeholder'
                        )}
                        onChange={() => null}
                        clearable={false}
                        invalid={error !== undefined}
                    ></SelectInput>
                    {isOpen && (
                        <Dropdown.Overlay
                            onClose={() => setIsOpen(false)}
                            verticalPosition='down'
                            dropdownOpenerVisible={true}
                            fullWidth={true}
                        >
                            <Dropdown.Header>
                                <Search
                                    onSearchChange={setSearch}
                                    placeholder={translate(
                                        'akeneo_catalogs.product_mapping.source.select_source_reference_entity_attribute.search'
                                    )}
                                    searchValue={search}
                                    title={translate(
                                        'akeneo_catalogs.product_mapping.source.select_source_reference_entity_attribute.search'
                                    )}
                                />
                            </Dropdown.Header>
                            <Dropdown.ItemCollection
                                noResultIllustration={<GroupsIllustration />}
                                noResultTitle={translate(
                                    'akeneo_catalogs.product_mapping.source.select_source_reference_entity_attribute.no_results'
                                )}
                            >
                                {filteredReferenceEntityAttributes?.map(referenceEntityAttribute => (
                                    <Dropdown.Item
                                        key={referenceEntityAttribute.identifier}
                                        onClick={() =>
                                            handleReferenceEntityAttributeSelection(referenceEntityAttribute)
                                        }
                                        isActive={referenceEntityAttribute.identifier === selectedIdentifier}
                                    >
                                        {referenceEntityAttribute.label}
                                    </Dropdown.Item>
                                ))}
                            </Dropdown.ItemCollection>
                        </Dropdown.Overlay>
                    )}
                </Dropdown>
                {undefined !== error && (
                    <Helper inline level='error'>
                        {error}
                    </Helper>
                )}
            </SelectReferenceEntityAttributeDropdownField>
        </>
    );
};
