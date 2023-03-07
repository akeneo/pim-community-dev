import React, {FC, useCallback, useState} from 'react';
import {Dropdown, Field, GroupsIllustration, Helper, Search, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useInfiniteAssetAttributes} from '../../../hooks/useInfiniteAssetAttributes';
import {AssetAttribute} from '../../../models/AssetAttribute';
import styled from 'styled-components';
import {Target} from '../../../models/Target';
import {useAssetAttribute} from '../../../hooks/useAssetAttribute';

const SelectAssetAttributeDropdownField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    selectedIdentifier: string;
    target: Target;
    assetFamilyCode: string;
    onChange: (value: AssetAttribute) => void;
    error: string | undefined;
};

export const SelectSourceAssetAttributeDropdown: FC<Props> = ({selectedIdentifier, target, assetFamilyCode, onChange, error}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState<boolean>(false);
    const [search, setSearch] = useState<string>('');
    const {data: assetAttributes, fetchNextPage} = useInfiniteAssetAttributes({target: target, assetFamilyCode, search});
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

    return (
        <>
            <SelectAssetAttributeDropdownField
                label={translate('akeneo_catalogs.product_mapping.source.select_asset_attribute.label')}
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
                                onNextPage={fetchNextPage}
                            >
                                {assetAttributes?.map(assetAttribute => (
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
