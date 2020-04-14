import React from "react";
import {PimCondition} from "../../models/PimCondition";
import {getByIdentifier} from "../../fetch/AttributeFetcher";
import {useBackboneRouter, useTranslate, useUserContext} from "../../dependenciesTools/hooks";

type Props = {
  condition: PimCondition
}

const SystemFields = [
  'family'
];

const PimConditionLine: React.FC<Props> = ({ condition }) => {
  const userContext = useUserContext();
  const translate = useTranslate();

  const [fieldLabel, setFieldLabel] = React.useState<string>();
  const [isError, setIsError] = React.useState<boolean>(false);
  const currentCatalogLocale = userContext.get('catalogLocale');

  const router = useBackboneRouter();

  React.useEffect(() => {
    if (SystemFields.includes(condition.field)) {
      setFieldLabel(translate(`pimee_catalog_rule.form.edit.conditions.system_fields.${condition.field}`));
      return;
    }
    getByIdentifier(condition.field, router).then((attribute) => {
      setFieldLabel(attribute.labels[currentCatalogLocale]);
    }).catch((exception) => {
      setIsError(true);
      console.error(exception);
    });
  }, []);

  const displayValue = (value: any): string => {
    if (null === value || undefined === value) {
      return '';
    }
    if (Array.isArray(value)) {
      return (value.map((value) => { return displayValue(value) }).join(', '));
    }
    if (typeof(value) === 'boolean') {
      return value ? translate('pim_common.yes') : translate('pim_common.no');
    }
    if (typeof(value) === 'object') {
      return JSON.stringify(value);
    }

    return value;
  };

  const displayScope = (scope: string | null) : string | null => {
    if (null === scope) {
      return null;
    }

    return scope;
  };

  const displayLocale = (locale: string | null) : string | null => {
    if (null === locale) {
      return null;
    }

    return locale;
  };

  return (
    <div>
      {isError ? 'There was an error (TODO: better display)' :
        !fieldLabel ? 'Loading...' :
          <div className="AknRule">
            <span className="AknRule-attribute">{fieldLabel}</span>
            {translate(`pimee_catalog_rule.form.edit.operators.${condition.operator}`)}
            <span className="AknRule-attribute">{displayValue(condition.value)}</span>
            {condition.scope || condition.locale ?
              <span className="AknRule-attribute">[
                {[displayScope(condition.scope), displayLocale(condition.locale)]
                  .filter((value) => { return null !== value })
                  .join(' | ')}
              ]</span>
              : ''
            }
          </div>
      }
    </div>
  );
};

export { PimConditionLine }
