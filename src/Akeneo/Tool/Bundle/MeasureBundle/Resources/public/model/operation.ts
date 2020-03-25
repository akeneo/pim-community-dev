const MAX_OPERATION_COUNT = 5;

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

export {MAX_OPERATION_COUNT, Operation, Operator, emptyOperation};
