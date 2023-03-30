import React, {FC, useCallback, useMemo, useState} from 'react';
import {Dropdown, Field, GroupsIllustration, Helper, Search, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useInfiniteSourceAttributes} from '../../hooks/useInfiniteSourceAttributes';
import {Attribute} from '../../../../models/Attribute';
import {useAttribute} from '../../../../hooks/useAttribute';
import styled from 'styled-components';
import {useSystemAttributes} from '../../../../hooks/useSystemAttributes';
import {Target} from '../../models/Target';

const SelectAttributeDropdownField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    selectedCode: string;
    target: Target;
    onChange: (value: Attribute) => void;
    error: string | undefined;
};

export const SelectSourceAttributeDropdown: FC<Props> = ({selectedCode, target, onChange, error}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState<boolean>(false);
    const [search, setSearch] = useState<string>('');
    const {data: attributes, fetchNextPage} = useInfiniteSourceAttributes({target: target, search});
    const {data: attribute} = useAttribute(selectedCode);
    const systemAttributes = useSystemAttributes();
    const attributeLabel = attribute?.label ?? (selectedCode.length > 0 ? `[${selectedCode}]` : '');
    const filteredSystemAttributes: Attribute[] = useMemo(() => {
        if (target.type === 'boolean') {
            return systemAttributes.filter(attribute => attribute.code === 'status');
        }
        if (['string', 'array<string>'].includes(target.type) && target.format === null) {
            const regex = new RegExp(search, 'i');
            const correspondingSystemAttributes = systemAttributes.filter(attribute => attribute.label.match(regex));
            correspondingSystemAttributes.forEach((attribute, index) => {
                if (attribute.code === 'status') {
                    correspondingSystemAttributes.splice(index);
                }
            });
            return correspondingSystemAttributes;
        } else {
            return [];
        }
    }, [systemAttributes, search, target.type, target.format]);

    const handleAttributeSelection = useCallback(
        (attribute: Attribute) => {
            onChange(attribute);
            setIsOpen(false);
        },
        [onChange]
    );

    const openDropdown = useCallback(e => {
        e.preventDefault();
        setIsOpen(true);
    }, []);

    return (
        <>
            <SelectAttributeDropdownField
                label={translate('akeneo_catalogs.product_mapping.source.select_source.label')}
            >
                <Dropdown>
                    <SelectInput
                        onMouseDown={openDropdown}
                        emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                        openLabel={translate('akeneo_catalogs.common.select.open')}
                        value={attributeLabel}
                        placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.placeholder')}
                        onChange={() => null}
                        clearable={false}
                        data-testid='product-mapping-select-attribute'
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
                                        'akeneo_catalogs.product_mapping.source.select_source.search'
                                    )}
                                    searchValue={search}
                                    title={translate('akeneo_catalogs.product_mapping.source.select_source.search')}
                                />
                            </Dropdown.Header>
                            <Dropdown.ItemCollection
                                noResultIllustration={<GroupsIllustration />}
                                noResultTitle={translate(
                                    'akeneo_catalogs.product_mapping.source.select_source.no_results'
                                )}
                                onNextPage={fetchNextPage}
                            >
                                {/* system attributes */}
                                {filteredSystemAttributes.length > 0 && (
                                    <Dropdown.Section>
                                        {translate('akeneo_catalogs.product_selection.add_criteria.section_system')}
                                    </Dropdown.Section>
                                )}
                                {filteredSystemAttributes?.map(attribute => (
                                    <Dropdown.Item
                                        key={attribute.code}
                                        onClick={() => handleAttributeSelection(attribute)}
                                        isActive={attribute.code === selectedCode}
                                    >
                                        {attribute.label}
                                    </Dropdown.Item>
                                ))}
                                {/* attributes */}
                                {(attributes?.length ?? 0) > 0 && (
                                    <Dropdown.Section>
                                        {translate(
                                            'akeneo_catalogs.product_mapping.source.select_source.section_attributes'
                                        )}
                                    </Dropdown.Section>
                                )}
                                {attributes?.map(attribute => (
                                    <Dropdown.Item
                                        key={attribute.code}
                                        onClick={() => handleAttributeSelection(attribute)}
                                        isActive={attribute.code === selectedCode}
                                    >
                                        {attribute.label}
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
            </SelectAttributeDropdownField>
        </>
    );
};
