const Row = require('./row.decorator')

const Grid = async (nodeElement, createElementDecorator, parent) => {
  const children = {
    'Rows':  {
      selector: '.AknGrid-bodyRow.row-click-action',
      decorator: Row,
      multiple: true
    },
  };

  const getChildren = createElementDecorator(children);
  const getRowCount = async () =>  {
    const rows = await getChildren(parent, 'Rows')
    return rows.length;
  }

  return { getRowCount };
};

module.exports = Grid;
