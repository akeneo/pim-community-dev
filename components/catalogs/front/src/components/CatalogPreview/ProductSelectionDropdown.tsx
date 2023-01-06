import React, {FC, useCallback, useEffect, useMemo, useState} from 'react';
import {Button, Field, Dropdown, GroupsIllustration, Search, SelectInput} from 'akeneo-design-system';
import {Product, useAffectedProductsQuery} from './useAffectedProducts';
import {ProductSelectionValues} from '../ProductSelection';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
    catalogId: string,
    productSelectionCriteria: ProductSelectionValues,
    selectedProduct: Product | undefined,
    onChange: (product: Product) => void,
};
export const ProductSelectionDropdown: FC<Props> = ({
    catalogId,
    productSelectionCriteria,
    selectedProduct,
    onChange
}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState<boolean>(false);
    const [search, setSearch] = useState<string>('');
    const {data: products} = useAffectedProductsQuery(catalogId, productSelectionCriteria, search);

    return (
        <Field label={'Select a product to preview'}>
            <Dropdown>
                <SelectInput
                    onMouseDown={() => setIsOpen(true)}
                    emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                    openLabel={translate('akeneo_catalogs.common.select.open')}
                    value={selectedProduct?.name ?? null}
                    onChange={() => null}
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
                            noResultIllustration={<GroupsIllustration/>}
                            noResultTitle={translate(
                                'akeneo_catalogs.product_mapping.source.select_source.no_results'
                            )}
                            // onNextPage={fetchNextPage}
                        >
                            {(products?.length ?? 0) > 0 && (
                                <Dropdown.Section>
                                    {translate(
                                        'akeneo_catalogs.product_mapping.source.select_source.section_attributes'
                                    )}
                                </Dropdown.Section>
                            )}
                            {products?.map(product => (
                                <Dropdown.Item
                                    key={product.uuid}
                                    onClick={() => {
                                        onChange(product);
                                        setIsOpen(false);
                                    }
                                    }
                                    isActive={product.uuid === selectedProduct?.uuid}
                                >
                                    {product.name}
                                </Dropdown.Item>
                            ))}
                        </Dropdown.ItemCollection>
                    </Dropdown.Overlay>
                )}
            </Dropdown>
        </Field>
    )
};
