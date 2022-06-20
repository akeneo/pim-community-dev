import React, {FC, useMemo, useState} from 'react';
import {Button, Dropdown, GroupsIllustration, Search} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import StatusCriterion from '../criteria/StatusCriterion';
import {Criterion} from '../models/Criterion';
import {CriterionStates} from '../models/Criteria';

type Factory = {
    label: string;
    factory: () => Criterion<any>;
};

type SectionProps = {
    label: string;
    factories: Factory[];
    onClick: (criterion: Criterion<CriterionStates>) => void;
};

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const Section = React.forwardRef<HTMLDivElement, SectionProps>(({label, factories, onClick}, _ref) => {
    if (0 === factories.length) {
        return null;
    }

    return (
        <>
            <Dropdown.Section>{label}</Dropdown.Section>
            {factories.map((factory, i) => (
                <Dropdown.Item key={i} onClick={() => onClick(factory.factory())}>
                    {factory.label}
                </Dropdown.Item>
            ))}
        </>
    );
});

type Props = {
    onNewCriterion: (criterion: Criterion<CriterionStates>) => void;
};

const AddCriterionDropdown: FC<Props> = ({onNewCriterion}) => {
    const translate = useTranslate();
    const [isOpen, setIsOpen] = useState<boolean>(false);
    const [search, setSearch] = useState<string>('');

    const systemCriterionFactories: Factory[] = useMemo(() => {
        const regex = new RegExp(search, 'i');

        return [
            {
                label: translate('akeneo_catalogs.product_selection.criteria.status.label'),
                factory: StatusCriterion,
            },
        ].filter(factory => factory.label.match(regex));
    }, [translate, search]);

    return (
        <Dropdown>
            <Button onClick={() => setIsOpen(true)} ghost={true} level='tertiary'>
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
                    >
                        <Section
                            label={translate('akeneo_catalogs.product_selection.add_criteria.section_system')}
                            factories={systemCriterionFactories}
                            onClick={onNewCriterion}
                        />
                    </Dropdown.ItemCollection>
                </Dropdown.Overlay>
            )}
        </Dropdown>
    );
};

export {AddCriterionDropdown};
