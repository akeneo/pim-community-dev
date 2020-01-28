import * as React from 'react';

export default ({data, value, multiple, readOnly, configuration, onChange, ...props}: any) => {
  return (
    <select
      value={value}
      multiple={multiple}
      disabled={readOnly}
      onChange={event => onChange(event.currentTarget.value)}
      {...props}
    >
      {Object.keys(data).map(value => (
        <option key={value} value={value}>
          {data[value]}
        </option>
      ))}
    </select>
  );
};
