type InputProps<T> = {
  id?: string;
  value: T;
  onChange?: (newValue: T) => void;
  'aria-labelledby'?: string;
};

export type {InputProps};
