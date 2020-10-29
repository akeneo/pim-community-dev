import React from 'react';
import {useTranslate} from '../../../../../dependenciesTools/hooks';
import {IdentifiersSelector} from '../../../../../components/Selectors/IdentifierSelector';
import {ProductIdentifier, ProductModelCode} from '../../../../../models';

type Identifier = ProductIdentifier | ProductModelCode;

type Props = {
  identifiers: Identifier[];
  onChange: (identifiers: Identifier[]) => void;
  entityType: 'product' | 'product_model';
};

const AssociationsIdentifiersSelector: React.FC<Props> = ({
  identifiers,
  onChange,
  entityType,
}) => {
  const translate = useTranslate();
  const [closeTick, setCloseTick] = React.useState<boolean>(false);

  const handleChange = (identifier: Identifier, index?: number) => {
    if (!identifier && typeof index !== 'undefined') {
      identifiers.splice(index, 1);
    } else if (!identifiers.includes(identifier)) {
      if (typeof index !== 'undefined') {
        identifiers[index] = identifier;
      } else {
        identifiers.push(identifier);
      }
    }
    onChange(identifiers);
  };

  return (
    <ul>
      {identifiers.map((identifier, i) => {
        return (
          <li key={identifier} className={'AknBadgedSelector-item'}>
            <IdentifiersSelector
              entityType={entityType}
              value={identifier}
              id={`product-or-product-model-selector-${identifier}`}
              allowClear={true}
              hiddenLabel
              onChange={productIdentifier => handleChange(productIdentifier, i)}
              placeholder={' '} // A placeholder is needed for allowClear
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
            handleChange(event.val);
          }}
          closeTick={closeTick}
        />
      </li>
    </ul>
  );
};

export {AssociationsIdentifiersSelector};
