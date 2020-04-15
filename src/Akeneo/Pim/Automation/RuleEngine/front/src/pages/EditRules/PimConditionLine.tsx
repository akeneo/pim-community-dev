import React, {ReactElement} from "react";
import {PimCondition} from "../../models/PimCondition";
import {getByIdentifier} from "../../fetch/AttributeFetcher";
import {useBackboneRouter, useTranslate, useUserContext} from "../../dependenciesTools/hooks";
import {getAll} from "../../fetch/ChannelFetcher";
import {Flag} from "../../components/Flag/Flag";

type Props = {
  condition: PimCondition
}

const SystemFieldsTranslationKeys: {[systemField: string]: string} = {
  'family': 'pim_enrich.entity.family.uppercase_label',
  'categories': 'pim_enrich.entity.category.uppercase_label',
  'completeness': 'pimee_catalog_rule.form.edit.conditions.system_fields.completeness',
  'identifier': 'pimee_catalog_rule.form.edit.conditions.system_fields.identifier',
  'created': 'pim_common.created',
  'updated': 'pim_common.updated',
  'enabled': 'pimee_catalog_rule.form.edit.conditions.system_fields.enabled',
  'groups': 'pim_enrich.entity.group.label',
};

const PimConditionLine: React.FC<Props> = ({ condition }) => {
  const userContext = useUserContext();
  const translate = useTranslate();

  const [fieldLabel, setFieldLabel] = React.useState<string>();
  const [isError, setIsError] = React.useState<boolean>(false);
  const currentCatalogLocale = userContext.get('catalogLocale');
  const [scopeLabel, setScopeLabel] = React.useState<string>();

  const router = useBackboneRouter();

  React.useEffect(() => {
    if (SystemFieldsTranslationKeys.hasOwnProperty(condition.field)) {
      const key = SystemFieldsTranslationKeys[condition.field];
      setFieldLabel(translate(key));
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
      if (value.hasOwnProperty('amount') && value.hasOwnProperty('unit')) {
        // Metric
        return `${value.amount} ${value.unit}`;
      }
      if (value.hasOwnProperty('amount') && value.hasOwnProperty('currency')) {
        // Price
        return `${value.amount} ${value.currency}`;
      }
      return JSON.stringify(value);
    }

    return value;
  };

  const displayLocale = (locale: string | null) : ReactElement | null => {
    if (null === locale) {
      return null;
    }

    return <>
      <Flag locale={locale} flagDescription={locale}/>{' '}
      {locale}
    </>
  };

  return (
    <div>
      {isError ? 'There was an error (TODO: better display)' :
        !fieldLabel ? 'Loading...' :
          <div className="AknRule">
            <span className="AknRule-attribute">{fieldLabel}</span>
            {' '}{translate(`pimee_catalog_rule.form.edit.conditions.operators.${condition.operator}`)}
            {' '}<span className="AknRule-attribute">{displayValue(condition.value)}</span>
            {(scopeLabel || condition.locale) ?
              (scopeLabel && condition.locale) ?
                <span className="AknRule-attribute">{'[ '}{displayLocale(condition.locale)}{' | '}{scopeLabel}{' ]'}</span> :
                <span className="AknRule-attribute">{'[ '}{displayLocale(condition.locale)}{scopeLabel}{' ]'}</span>
            : ''
            }
          </div>
      }
    </div>
  );
};

export { PimConditionLine }
