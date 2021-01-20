enum Operator {
  MUL = 'mul',
  DIV = 'div',
  ADD = 'add',
  SUB = 'sub',
}

type Operation = {
  operator: string;
  value: string;
};

const emptyOperation = (): Operation => ({
  operator: Operator.MUL,
  value: '',
});

export type {Operation};
export {emptyOperation, Operator};
