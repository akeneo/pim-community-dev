import React from 'react';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { IdentifiersSelector } from '../../../../../components/Selectors/IdentifierSelector';
import { ProductIdentifier, ProductModelCode } from '../../../../../models';
import { InputNumber } from '../../../../../components/Inputs';

type Identifier = ProductIdentifier | ProductModelCode;

type Props = {
  value: { identifier: Identifier; quantity: number }[];
  onChange: (value: { identifier: Identifier; quantity: number }[]) => void;
  entityType: 'product' | 'product_model';
};

const QuantifiedAssociationsIdentifiersSelector: React.FC<Props> = ({
  value,
  onChange,
  entityType,
}) => {
  const translate = useTranslate();
  const [closeTick, setCloseTick] = React.useState<boolean>(false);

  const handleAdd = (identifier: Identifier) => {
    if (!value.map(v => v.identifier).includes(identifier)) {
      value.push({
        identifier,
        quantity: 1,
      });
      onChange(value);
    }
  };

  const onIdentifierChange = (identifier: Identifier, index: number) => {
    if (!identifier) {
      value.splice(index, 1);
    } else if (!value.map(v => v.identifier).includes(identifier)) {
      value[index].identifier = identifier;
    }
    onChange(value);
  };

  const onQuantityChange = (quantity: number, index: number) => {
    value[index].quantity = quantity;
    onChange(value);
  };

  return (
    <ul>
      {value.map(({ identifier, quantity }, i) => {
        return (
          <li
            key={identifier}
            className={
              'AknBadgedSelector-item AknBadgedSelector-item--quantified'
            }>
            <IdentifiersSelector
              entityType={entityType}
              value={identifier}
              id={`product-or-product-model-selector-${identifier}`}
              allowClear={true}
              hiddenLabel
              onChange={productIdentifier =>
                onIdentifierChange(productIdentifier, i)
              }
              placeholder={' '} // A placeholder is needed for allowClear
            />
            <InputNumber
              value={quantity}
              min={1}
              max={2147483647}
              onChange={e => onQuantityChange(Number(e.target.value), i)}
            />
          </li>
        );
      })}
      <li className={'AknBadgedSelector-item'}>
        <IdentifiersSelector
          entityType={entityType}
          value={''}
          id={'product-or-product-model-selector-new'}
          allowClear={false}
          hiddenLabel
          placeholder={translate(
            `pimee_catalog_rule.form.edit.actions.set_associations.add_${entityType}`
          )}
          onSelecting={(event: any) => {
            event.preventDefault();
            setCloseTick(!closeTick);
            handleAdd(event.val);
          }}
          closeTick={closeTick}
        />
      </li>
    </ul>
  );
};

export { QuantifiedAssociationsIdentifiersSelector };
