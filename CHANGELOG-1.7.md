# 1.7

## Functional improvements

- Change the loading message by a more humanized message to share our love.

## Technical improvements

- TIP-575: Rename FileIterator classes to FlatFileIterator and changes the reader/processor behavior to iterate over the item's position in the file instead of the item's line number in the file.

## BC breaks

- Add method `findDatagridViewBySearch` to the `Pim\Bundle\DataGridBundle\Repository\DatagridViewRepositoryInterface`
- Remove methods `listColumnsAction` and  `removeAction` of the `Pim\Bundle\DataGridBundle\Controller\DatagridViewController`
- Change the constructor of `Pim\Bundle\DataGridBundle\Controller\DatagridViewController` to keep `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface` as the only argument
