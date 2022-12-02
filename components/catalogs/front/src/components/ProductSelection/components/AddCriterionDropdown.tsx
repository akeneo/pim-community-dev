import React, {FC, useCallback, useEffect, useMemo, useState} from 'react';
import {Button, Dropdown, GroupsIllustration, Search} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {AnyCriterionState} from '../models/Criterion';
import {useProductSelectionContext} from '../contexts/ProductSelectionContext';
import {ProductSelectionActions} from '../reducers/ProductSelectionReducer';
import {useSystemCriterionFactories} from '../hooks/useSystemCriterionFactories';
import {generateRandomId} from '../utils/generateRandomId';
import {useInfiniteAttributeCriterionFactories} from '../hooks/useInfiniteAttributeCriterionFactories';
import {CriterionFactory} from '../models/CriterionFactory';
import {usePartitionAttributesByGroup} from '../hooks/usePartitionAttributesByGroup';

type Props = {
    isDisabled: boolean;
};

const AddCriterionDropdown: FC<Props> = ({isDisabled}) => {
    const translate = useTranslate();
    const dispatch = useProductSelectionContext();
    const [isOpen, setIsOpen] = useState<boolean>(false);
    const [search, setSearch] = useState<string>('');
    const systemCriterionFactories = useSystemCriterionFactories();
    const {data: attributesCriterionFactories, fetchNextPage} = useInfiniteAttributeCriterionFactories({search});
    const attributesCriterionFactoriesByAttributeGroup = usePartitionAttributesByGroup(attributesCriterionFactories);

    const filteredSystemCriterionFactories: CriterionFactory[] = useMemo(() => {
        const regex = new RegExp(search, 'i');

        return systemCriterionFactories.filter(factory => factory.label.match(regex));
    }, [systemCriterionFactories, search]);

    const handleNewCriterion = useCallback(
        (state: AnyCriterionState) => {
            dispatch({
                type: ProductSelectionActions.ADD_CRITERION,
                id: generateRandomId(),
                state: state,
            });
        },
        [dispatch]
    );
    useEffect(() => {
        if (isDisabled) {
            setIsOpen(false);
        }
    }, [isDisabled]);

    return (
        <Dropdown>
            <Button onClick={() => setIsOpen(true)} ghost={true} level='tertiary' disabled={isDisabled}>
                {translate('akeneo_catalogs.product_selection.add_criteria.label')}
            </Button>
            {isOpen && (
                <Dropdown.Overlay onClose={() => setIsOpen(false)} verticalPosition='down'>
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
                        {/* system */}
                        {filteredSystemCriterionFactories.length > 0 && (
                            <Dropdown.Section>
                                {translate('akeneo_catalogs.product_selection.add_criteria.section_system')}
                            </Dropdown.Section>
                        )}
                        {filteredSystemCriterionFactories?.map(factory => (
                            <Dropdown.Item key={factory.id} onClick={() => handleNewCriterion(factory.factory())}>
                                {factory.label}
                            </Dropdown.Item>
                        ))}
                        {/* attributes */}
                        {attributesCriterionFactoriesByAttributeGroup.map(groupedAttributes => (
                            <div key={groupedAttributes.code}>
                                <Dropdown.Section>{groupedAttributes.label}</Dropdown.Section>
                                {groupedAttributes.attributesWithFactories.map(factory => (
                                    <Dropdown.Item
                                        key={factory.id}
                                        onClick={() => handleNewCriterion(factory.factory())}
                                    >
                                        {factory.label}
                                    </Dropdown.Item>
                                ))}
                            </div>
                        ))}
                    </Dropdown.ItemCollection>
                </Dropdown.Overlay>
            )}
        </Dropdown>
    );
};

export {AddCriterionDropdown};
