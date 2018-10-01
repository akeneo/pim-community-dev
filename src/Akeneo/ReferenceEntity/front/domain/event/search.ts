export const dataReceived = <Item>(items: Item[], total: number, append: boolean) => {
  return {type: 'DATA_RECEIVED', data: {items}, total, append};
};
