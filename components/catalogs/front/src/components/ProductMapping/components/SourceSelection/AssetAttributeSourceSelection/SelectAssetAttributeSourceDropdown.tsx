import React, {FC, useCallback, useMemo, useState} from 'react';
import {Dropdown, Field, GroupsIllustration, Helper, Search, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {AssetAttribute} from '../../../models/AssetAttribute';
import styled from 'styled-components';
import {Target} from '../../../models/Target';
import {useAssetAttribute} from '../../../hooks/useAssetAttribute';
import {useAssetAttributes} from '../../../hooks/useAssetAttributes';

const SelectAssetAttributeDropdownField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    selectedIdentifier: string;
    target: Target;
    assetFamilyIdentifier: string;
    onChange: (value: AssetAttribute) => void;
    error: string | undefined;
};

export const SelectAssetAttributeSourceDropdown: FC<Props> = ({selectedIdentifier, target, assetFamilyIdentifier, onChange, error}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState<boolean>(false);
    const [search, setSearch] = useState<string>('');
    const {data: assetAttributes} = useAssetAttributes(assetFamilyIdentifier, target);
    const {data: assetAttribute} = useAssetAttribute(selectedIdentifier);

    const handleAssetAttributeSelection = useCallback(
        (assetAttribute: AssetAttribute) => {
            onChange(assetAttribute);
            setIsOpen(false);
        },
        [onChange]
    );

    const openDropdown = useCallback(e => {
        e.preventDefault();
        setIsOpen(true);
    }, []);

    const filteredAssetAttributes: AssetAttribute[] = useMemo(() => {
        const regex = new RegExp(search, 'i');
        return assetAttributes?.filter(
            (assetAttribute: AssetAttribute) => assetAttribute.label.match(regex)
        ) ?? [];
    }, [assetAttributes, search]);

    return (
        <>
            <SelectAssetAttributeDropdownField
                label={translate('akeneo_catalogs.product_mapping.source.select_source_asset_attribute.label')}
            >
                <Dropdown>
                    <SelectInput
                        onMouseDown={openDropdown}
                        emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                        openLabel={translate('akeneo_catalogs.common.select.open')}
                        value={assetAttribute?.label ?? ''}
                        placeholder={translate('akeneo_catalogs.product_mapping.source.select_source_asset_attribute.placeholder')}
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
                                        'akeneo_catalogs.product_mapping.source.select_source_asset_attribute.search'
                                    )}
                                    searchValue={search}
                                    title={translate('akeneo_catalogs.product_mapping.source.select_source_asset_attribute.search')}
                                />
                            </Dropdown.Header>
                            <Dropdown.ItemCollection
                                noResultIllustration={<GroupsIllustration />}
                                noResultTitle={translate(
                                    'akeneo_catalogs.product_mapping.source.select_source_asset_attribute.no_results'
                                )}
                            >
                                {filteredAssetAttributes?.map(assetAttribute => (
                                    <Dropdown.Item
                                        key={assetAttribute.identifier}
                                        onClick={() => handleAssetAttributeSelection(assetAttribute)}
                                        isActive={assetAttribute.identifier === selectedIdentifier}
                                    >
                                        {assetAttribute.label}
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
            </SelectAssetAttributeDropdownField>
        </>
    );
};
