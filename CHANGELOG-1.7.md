# 1.7

## Bug Fixes

- #5062 Fixed unit conversion for ElectricCharge cheers @gplanchat!

## Functional improvements

- Change the loading message by a more humanized message to share our love.
- Add Energy measure family and conversions cheers @JulienDotDev!
- Complete Duration measure family with week, month, year and related conversions cheers @JulienDotDev!
- Add CaseBox measure family and conversions, cheers @gplanchat!

## Technical improvements

- #4696: Ping the server before updating job and step execution data to prevent "MySQL Server has gone away" issue cheers @qrz-io!
- TIP-575: Rename FileIterator classes to FlatFileIterator and changes the reader/processor behavior to iterate over the item's position in the file instead of the item's line number in the file.

## BC breaks

- Add method `findDatagridViewBySearch` to the `Pim\Bundle\DataGridBundle\Repository\DatagridViewRepositoryInterface`
- Remove methods `listColumnsAction` and  `removeAction` of the `Pim\Bundle\DataGridBundle\Controller\DatagridViewController`
- Change the constructor of `Pim\Bundle\DataGridBundle\Controller\DatagridViewController` to keep `Symfony\Bundle\FrameworkBundle\Templating\EngineInterface` as the only argument
- Change the constructor of `Pim\Bundle\DataGridBundle\Controller\Rest\DatagridViewController`add `Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface` and `Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface`
