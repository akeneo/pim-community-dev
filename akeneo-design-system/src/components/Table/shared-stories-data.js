export const rows = [
    {
        id: 1,
        image: 'https://picsum.photos/seed/akenea/200/140',
        name: 'Giant panda',
        family: 'Ursidae',
        order: 'Carnivora',
        genus: 'Ailuropoda',
        conservation_status: 'vu',
        conservation_status_level: 'warning'
    },
    {
        id: 2,
        image: 'https://picsum.photos/seed/akeneb/200/140',
        name: 'Red panda',
        family: 'Ailuridae',
        order: 'Carnivora',
        genus: 'Ailurus',
        conservation_status: 'en',
        conservation_status_level: 'danger'
    },
    {
        id: 3,
        image: 'https://picsum.photos/seed/akenec/200/140',
        name: 'American black bear, not a panda',
        family: 'Ursidae',
        order: 'Carnivora',
        genus: 'Ursus',
        conservation_status: 'lc',
        conservation_status_level: 'primary'
    },
];

export const sortRows = (rows, columnName, direction) => {
    if (columnName === null || columnName === undefined) {
        return rows;
    }

    return [...rows].sort((a, b) => {
        return direction === 'descending'
            ? a[columnName].localeCompare(b[columnName])
            : b[columnName].localeCompare(a[columnName]);
    });
};
