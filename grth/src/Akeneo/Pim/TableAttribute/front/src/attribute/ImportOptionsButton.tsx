import React from 'react';
import {ArrowDownIcon, Button, Dropdown, getColor, LoaderIcon, useBooleanState} from 'akeneo-design-system';
import {useAttributeWithOptions} from './useAttributeWithOptions';
import styled from 'styled-components';
import {AttributeCode, AttributeOption} from '../models';
import {useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {AttributeOptionFetcher} from '../fetchers';

type ImportOptionsButtonProps = {
  onClick: (attributeOptions: AttributeOption[]) => void;
  batchSize?: number;
};

const DropdownItem = styled(Dropdown.Item)`
  flex-direction: column;
  gap: 0;
  height: auto;
  align-items: flex-start;
  line-height: initial;
  margin: 10px 20px;
`;

const AttributeLabel = styled.div`
  font-size: ${({theme}) => theme.fontSize.bigger};
  color: ${getColor('grey', 140)};
`;

const OptionsCount = styled.div`
  font-size: ${({theme}) => theme.fontSize.small};
`;

const ImportOptionsButton: React.FC<ImportOptionsButtonProps> = ({onClick, batchSize = 25}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();
  const [isImporting, setIsImporting] = React.useState<boolean>(false);
  const {attributes, onNextPage} = useAttributeWithOptions(isOpen, batchSize);
  const router = useRouter();

  const handleClick = (selectAttributeCode: AttributeCode) => {
    close();
    setIsImporting(true);
    AttributeOptionFetcher.byAttributeCode(router, selectAttributeCode).then(attributeOptions => {
      onClick(attributeOptions);
      setIsImporting(false);
    });
  };

  return (
    <Dropdown>
      <Button
        level='tertiary'
        ghost={true}
        onClick={() => {
          !isImporting && open();
        }}
        disabled={isImporting}
      >
        {translate('pim_table_attribute.form.attribute.import_from_existing_attribute')}{' '}
        {isImporting ? <LoaderIcon data-testid={'isLoading'} /> : <ArrowDownIcon />}
      </Button>
      {isOpen && !isImporting && (
        <Dropdown.Overlay onClose={close} dropdownOpenerVisible={true}>
          <Dropdown.ItemCollection onNextPage={onNextPage}>
            {attributes.map(attribute => {
              return (
                <DropdownItem onClick={() => handleClick(attribute.code)} key={attribute.code}>
                  <AttributeLabel>{attribute.label || `[${attribute.code}]`}</AttributeLabel>
                  <OptionsCount>
                    {translate(
                      'pim_table_attribute.form.attribute.option_count',
                      {count: attribute.options_count},
                      attribute.options_count
                    )}
                  </OptionsCount>
                </DropdownItem>
              );
            })}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {ImportOptionsButton};
