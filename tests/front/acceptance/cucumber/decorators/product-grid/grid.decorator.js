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

  const getRowNames = async () => {
    const rows = await getChildren(parent, 'Rows');
    const resolvedRows = await Promise.all(rows);
    const names = []

    for (let i = 0; i < resolvedRows.length; i++) {
      const rowName = await resolvedRows[i].getTitle();
      names.push(rowName)
    }

    return names;
  }

  return { getRowCount, getRowNames };
};

module.exports = Grid;
