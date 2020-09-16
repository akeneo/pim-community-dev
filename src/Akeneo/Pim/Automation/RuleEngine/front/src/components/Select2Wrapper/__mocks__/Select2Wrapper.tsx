import React, { ChangeEvent, useEffect } from 'react';
import {
  Select2Option,
  Select2OptionGroup,
  Select2Props,
  Select2Value,
  Select2Wrapper as BaseWrapper,
} from '../Select2Wrapper';
import { Label } from '../../Labels';
import { httpGet } from '../../../fetch';

const Select2Wrapper: typeof BaseWrapper = ({
  data,
  hiddenLabel = false,
  id,
  label,
  multiple = false,
  placeholder,
  ajax,
  onChange,
  value,
  onSelecting,
  ...remainingProps
}: Select2Props) => {
  const [stateOptions, setOptions] = React.useState<
    (Select2Option | Select2OptionGroup)[]
  >(data || []);

  useEffect(() => {
    if (data) {
      setOptions(data);
    }
  }, [data]);

  const options = stateOptions;
  if (value) {
    const valuesArray = Array.isArray(value) ? value : [value];
    valuesArray.forEach((singleValue: Select2Value) => {
      if (!stateOptions.map(option => option.id).includes(singleValue)) {
        options.push({
          id: singleValue,
          text: `__mock__${singleValue}`,
        });
      }
    });
  }

  const handleClick = () => {
    if (ajax) {
      const url = ajax.url;
      const result = httpGet(url);
      if (undefined === result) {
        throw new Error(`You did not mock the result of ${url}!`);
      }
      result.then(response => {
        response
          .json()
          .then(
            (
              fetchedOptions:
                | (Select2Option | Select2OptionGroup)[]
                | { results: (Select2Option | Select2OptionGroup)[] }
            ) => {
              if (fetchedOptions.hasOwnProperty('results')) {
                setOptions(
                  (fetchedOptions as {
                    results: (Select2Option | Select2OptionGroup)[];
                  }).results
                );
              } else {
                setOptions(
                  fetchedOptions as (Select2Option | Select2OptionGroup)[]
                );
              }
            }
          );
      });
    }
  };

  const getSelect2ValueFromOptions = (
    options: Select2Option[],
    value: number | string | null
  ): Select2Option | undefined => {
    return options.find(option => option.id === value);
  };

  const getSelect2Value = (value: any) => {
    if (options.length && options[0].hasOwnProperty('children')) {
      const optionGroup:
        | Select2OptionGroup
        | undefined = (options as Select2OptionGroup[]).find(optionGroup => {
        return getSelect2ValueFromOptions(optionGroup.children, value);
      });
      return optionGroup
        ? getSelect2ValueFromOptions(optionGroup.children, value)
        : undefined;
    } else {
      return getSelect2ValueFromOptions(options as Select2Option[], value);
    }
  };

  const handleChange = (event: ChangeEvent<HTMLSelectElement>) => {
    if (onChange) {
      const newValue = event.target.multiple
        ? Array.from(event.target.selectedOptions).map(option => option.value)
        : event.target.value;
      onChange(newValue);
    }
    if (onSelecting) {
      onSelecting({
        preventDefault: event.preventDefault.bind(event),
        val: event.target.value,
        object: getSelect2Value(event.target.value),
      });
    }
  };

  return (
    <>
      <Label label={label} hiddenLabel={hiddenLabel} htmlFor={id} />
      <select
        id={id}
        data-testid={(remainingProps as any)['data-testid']}
        onChange={handleChange}
        onClick={handleClick}
        multiple={multiple}
        value={value === null ? (multiple ? [] : '') : (value as string)}>
        {placeholder ? (
          <option disabled value={''}>
            {placeholder}
          </option>
        ) : (
          ''
        )}
        {options.map((option: Select2Option | Select2OptionGroup, i) => {
          return option.hasOwnProperty('children') ? (
            <optgroup key={option.id || i} label={option.text}>
              {(option as Select2OptionGroup).children.map((subOption, j) => (
                <option key={subOption.id || j} value={subOption.id || j}>
                  {subOption.text}
                </option>
              ))}
            </optgroup>
          ) : (
            <option key={option.id || i} value={option.id || i}>
              {option.text}
            </option>
          );
        })}
      </select>
    </>
  );
};

export { Select2Wrapper };
