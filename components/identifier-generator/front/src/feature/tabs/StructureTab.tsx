import React, {useState, useMemo} from 'react';
import {
  Button,
  Dropdown,
  GroupsIllustration,
  Search,
  SectionTitle,
  useBooleanState,
  useDebounce,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Styled} from '../components';

type StructureProps = {};

type StructureType = {
  code: string;
  label: string;
  items: {
    code: string;
    label: string;
  }[];
};

const StructureTab: React.FC<StructureProps> = () => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);
  const [searchValue, setSearchValue] = useState('');
  const debouncedSearchValue = useDebounce(searchValue);

  const addElement = () => {
    open();
  };

  const onSearchClose = () => {
    close();
    setSearchValue('');
  };

  const items = useMemo(
    (): StructureType[] => [
      {
        code: 'system',
        label: translate('pim_identifier_generator.structure.property_type.sections.system'),
        items: [{code: 'freetext', label: translate('pim_identifier_generator.structure.property_type.freetext')}],
      },
    ],
    [translate]
  );

  const filterElements = useMemo((): StructureType[] => {
    if ('' !== debouncedSearchValue) {
      return items
        .map(item => {
          const filteredItems = item.items.filter(subItem =>
            subItem.label.toLowerCase().includes(debouncedSearchValue.toLowerCase())
          );
          return {...item, items: filteredItems};
        })
        .filter(item => item.items.length > 0);
    } else {
      return items;
    }
  }, [debouncedSearchValue, items]);

  return (
    <>
      <Styled.StructureSectionTitle>
        <SectionTitle.Title>{translate('pim_identifier_generator.structure.title')}</SectionTitle.Title>

        <div>
          <Button active ghost level="secondary" onClick={addElement}>
            {translate('pim_identifier_generator.structure.add_element')}
          </Button>
          <Dropdown>
            {isOpen && (
              <Dropdown.Overlay verticalPosition="down" onClose={onSearchClose}>
                <Dropdown.Header>
                  <Search
                    onSearchChange={setSearchValue}
                    placeholder={translate('pim_identifier_generator.structure.search_placeholder')}
                    searchValue={searchValue}
                    title={translate('pim_identifier_generator.structure.search')}
                  />
                </Dropdown.Header>
                <Dropdown.ItemCollection
                  noResultIllustration={React.createElement(GroupsIllustration)}
                  noResultTitle={translate('pim_identifier_generator.structure.no_result')}
                >
                  {filterElements.map(item => (
                    <React.Fragment key={item.code}>
                      <Dropdown.Section>{item.label}</Dropdown.Section>
                      {item.items.map(subItem => (
                        <Dropdown.Item key={subItem.code}>{subItem.label}</Dropdown.Item>
                      ))}
                    </React.Fragment>
                  ))}
                </Dropdown.ItemCollection>
              </Dropdown.Overlay>
            )}
          </Dropdown>
        </div>
      </Styled.StructureSectionTitle>
      <Styled.FormContainer></Styled.FormContainer>
    </>
  );
};

export {StructureTab};
