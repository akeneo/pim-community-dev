import { useFormContext } from 'react-hook-form';
import get from 'lodash/get';
import { LocaleCode, ScopeCode } from '../../../models';
import { Operator } from '../../../models/Operator';

const useControlledFormInputCondition = <T>(lineNumber: number) => {
  const { watch, getValues, setValue, errors } = useFormContext();
  const formName = (name: string) => `content.conditions[${lineNumber}].${name}`;
  const fieldFormName = formName('field');
  const operatorFormName = formName('operator');
  const valueFormName = formName('value');
  const amountValueFormName = formName('value.amount');
  const currencyValueFormName = formName('value.currency');
  const scopeFormName = formName('scope');
  const localeFormName = formName('locale');
  const getFormValue = (name: string) => get(getValues(), formName(name));
  const getOperatorFormValue = (): Operator =>
    get(getValues(), operatorFormName);
  const getValueFormValue = (): T => get(getValues(), valueFormName);
  const getAmountValueFormValue = (): T =>
    get(getValues(), amountValueFormName);
  const getCurrencyValueFormValue = (): T =>
    get(getValues(), currencyValueFormName);
  const getScopeFormValue = (): ScopeCode => get(getValues(), scopeFormName);
  const getLocaleFormValue = (): LocaleCode => get(getValues(), localeFormName);
  const setOperatorFormValue = (data: Operator) =>
    setValue(operatorFormName, data);
  const setValueFormValue = (data?: T) => setValue(valueFormName, data);
  const setAmountValueFormValue = (data?: T) =>
    setValue(amountValueFormName, data);
  const setCurrencyValueFormValue = (data?: T) =>
    setValue(currencyValueFormName, data);
  const setScopeFormValue = (data: ScopeCode) => setValue(scopeFormName, data);
  const setLocaleFormValue = (data: LocaleCode) =>
    setValue(localeFormName, data);
  const isFormFieldInError = (formName: string): boolean => {
    const conditionErrors = errors?.content?.conditions?.[lineNumber] || {};
    return typeof get(conditionErrors, formName) === 'object';
  };
  watch(operatorFormName);
  watch(valueFormName);
  watch(scopeFormName);
  watch(localeFormName);

  return {
    formName,
    fieldFormName,
    getFormValue,
    getLocaleFormValue,
    getOperatorFormValue,
    getScopeFormValue,
    getValueFormValue,
    getAmountValueFormValue,
    getCurrencyValueFormValue,
    localeFormName,
    operatorFormName,
    scopeFormName,
    amountValueFormName,
    currencyValueFormName,
    setLocaleFormValue,
    setOperatorFormValue,
    setScopeFormValue,
    setValueFormValue,
    setAmountValueFormValue,
    setCurrencyValueFormValue,
    valueFormName,
    isFormFieldInError,
  };
};

const useControlledFormInputAction = <T>(lineNumber: number) => {
  const { getValues, setValue, errors } = useFormContext();
  const isFormFieldInError = (formName: string): boolean => {
    const actionErrors = errors?.content?.actions?.[lineNumber] || {};
    return typeof get(actionErrors, formName) === 'object';
  };

  const formName = (name: string) => `content.actions[${lineNumber}].${name}`;
  const fieldFormName = formName('field');
  const typeFormName = formName('type');
  const itemsFormName = formName('items');
  const valueFormName = formName('value');
  const scopeFormName = formName('scope');
  const localeFormName = formName('locale');
  const includeChildrenFormName = formName('include_children');
  const getFormValue = (name: string) => get(getValues(), formName(name));
  const getValueFormValue = (): T => get(getValues(), valueFormName);
  const getItemsFormValue = (): T => get(getValues(), itemsFormName);
  const getFieldFormValue = (): string => get(getValues(), fieldFormName);
  const getScopeFormValue = (): ScopeCode => get(getValues(), scopeFormName);
  const getLocaleFormValue = (): LocaleCode => get(getValues(), localeFormName);
  const setFieldFormValue = (data?: string) => setValue(fieldFormName, data);
  const setItemsFormValue = (data?: T) => setValue(itemsFormName, data);
  const setValueFormValue = (data?: T) => setValue(valueFormName, data);
  const getIncludeChildrenFormValue = (): boolean =>
    get(getValues(), includeChildrenFormName);
  const setIncludeChildrenFormValue = (data?: boolean) =>
    setValue(includeChildrenFormName, data);

  return {
    isFormFieldInError,
    fieldFormName,
    getFieldFormValue,
    getItemsFormValue,
    getLocaleFormValue,
    getScopeFormValue,
    getValueFormValue,
    itemsFormName,
    localeFormName,
    scopeFormName,
    setFieldFormValue,
    setItemsFormValue,
    setValueFormValue,
    typeFormName,
    valueFormName,
    includeChildrenFormName,
    getIncludeChildrenFormValue,
    setIncludeChildrenFormValue,
    formName,
    getFormValue,
  };
};

export { useControlledFormInputCondition, useControlledFormInputAction };
