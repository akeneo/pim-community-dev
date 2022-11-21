import React, {FC, useCallback, useMemo, useState} from 'react';
import {Dropdown, Field, GroupsIllustration, Search, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useInfiniteAttributes} from '../hooks/useInfiniteAttributes';
import {Attribute} from '../../../models/Attribute';
import {useAttribute} from '../../../hooks/useAttribute';
import styled from 'styled-components';

const SelectAttributeDropdownField = styled(Field)`
    margin-top: 15px;
`;

type Props = {
    code: string;
    onChange: (value : Attribute) => void;
};

export const SelectAttributeDropdown: FC<Props> = ({code, onChange}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState<boolean>(false);
    const [search, setSearch] = useState<string>('');
    const [isInvalid, setIsInvalid] = useState<boolean>(false);
    const {data: attributes, fetchNextPage} = useInfiniteAttributes({search});
    const {data: attribute} = useAttribute(code);

    const handleAttributeSelection = useCallback(
        (attribute: Attribute) => {
            onChange(attribute);
            setIsOpen(false);
        },
        [onChange]
    );

    const handlePreventSelect = useCallback(
        (e) => {
            e.preventDefault();
            setIsOpen(true);
        },
        []
    );

    return (
        <>
            <SelectAttributeDropdownField label="Pim source">
                <Dropdown>
                    <SelectInput onMouseDown={handlePreventSelect}
                        emptyResultLabel=''
                        openLabel=''
                        value={attribute?.label ?? (code.length > 0 ? `[${code}]` : '')}
                        onChange={() => null}
                        clearable={false}
                        invalid={isInvalid}
                        data-testid='product-mapping-select-attribute'
                    >
                    </SelectInput>
                    {isOpen && (
                        <Dropdown.Overlay onClose={() => setIsOpen(false)} verticalPosition='down' dropdownOpenerVisible={true} fullWidth={true}>
                            <Dropdown.Header>
                                <Search
                                    onSearchChange={setSearch}
                                    placeholder={translate('akeneo_catalogs.product_selection.add_criteria.search')}
                                    searchValue={search}
                                    title={translate('akeneo_catalogs.product_selection.add_criteria.search')}
                                />
                            </Dropdown.Header>
                            <Dropdown.ItemCollection
                                noResultIllustration={<GroupsIllustration />}
                                noResultTitle={translate('akeneo_catalogs.product_selection.add_criteria.no_results')}
                                onNextPage={fetchNextPage}
                            >
                                {/* attributes */}
                                {(attributes?.length ?? 0) > 0 && (
                                    <Dropdown.Section>
                                        {translate('akeneo_catalogs.product_selection.add_criteria.section_attributes')}
                                    </Dropdown.Section>
                                )}
                                {attributes?.map(attribute => (
                                    <Dropdown.Item key={attribute.code}
                                                   onClick={() => handleAttributeSelection(attribute)}
                                                   isActive={attribute.code === code}>
                                        {attribute.label}
                                    </Dropdown.Item>
                                ))}
                            </Dropdown.ItemCollection>
                        </Dropdown.Overlay>
                    )}
                </Dropdown>
            </SelectAttributeDropdownField>
        </>
    );
};
