import React, {FC, useCallback, useMemo, useState} from 'react';
import {Button, Dropdown, Field, GroupsIllustration, Search, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useInfiniteAttributes} from '../hooks/useInfiniteAttributes';
import {Attribute} from '../../models/Attribute';


export const SelectSourceDropdown: FC<{}> = () => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState<boolean>(false);
    const [search, setSearch] = useState<string>('');
    const [isInvalid, setIsInvalid] = useState<boolean>(false);
    const {data: attributes, fetchNextPage} = useInfiniteAttributes({search});
    const [value, setValue] = useState<string>('');

    const handleAttributeSelection = useCallback(
        (attribute: Attribute) => {
            console.log(attribute);
            setValue(attribute.label);
        },
        []
    );

    const handleOpenDropDown = useCallback(
        () => {
            setIsOpen(true);
        },
        []
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
            <Field label="My field label">
                <Dropdown>
                    <SelectInput onClick={handleOpenDropDown} onMouseDown={handlePreventSelect}
                        emptyResultLabel=''
                        openLabel=''
                        value={value}
                        onChange={() => null}
                        clearable={false}
                        invalid={isInvalid}
                        data-testid='value'
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
                                                   onClick={() => handleAttributeSelection(attribute)}>
                                        {attribute.label}
                                    </Dropdown.Item>
                                ))}
                            </Dropdown.ItemCollection>
                        </Dropdown.Overlay>
                    )}
                </Dropdown>
            </Field>
        </>
    );
};
