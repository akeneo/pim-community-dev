import React from "react";
import {PimCondition} from "../../models/PimCondition";
import {getByIdentifier} from "../../fetch/AttributeFetcher";
import {useBackboneRouter, useTranslate, useUserContext} from "../../dependenciesTools/hooks";
import {getAll} from "../../fetch/ChannelFetcher";

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
  const [scopeLabel, setScopeLabel] = React.useState<string>();

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

    if (condition.scope) {
      getAll(router).then((channels) => {
        const channel = channels.find((scope) => {
          return scope.code === condition.scope;
        });
        setScopeLabel(channel ? channel.labels[currentCatalogLocale] : condition.scope as string)
      }).catch((exception) => {
        setIsError(true);
        console.error(exception);
      });
    }
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
            {scopeLabel || condition.locale ?
              <span className="AknRule-attribute">[
                {[scopeLabel, displayLocale(condition.locale)]
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
