<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Transform;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Interfaces\EnrichInterface;
use Pim\Bundle\ConnectorIcecatBundle\Exception\TransformException;
use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;

use \SimpleXMLElement;
/**
 * Aims to enrich datasheet with xml product values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductSetXmlToDataSheetTransformer implements EnrichInterface
{
    /**
     * Datasheet to enrich
     * @var IcecatProductDataSheet
     */
    protected $datasheet;

    /**
     * Xml element to parse
     * @var SimpleXMLElement
     */
    protected $simpleDoc;

    /**
     * Icecat locale id
     * @var integer
     */
    protected $localeId;

    /**
     * Constructor
     *
     * @param SimpleXMLElement       $simpleDoc      simple element
     * @param IcecatProductDataSheet $datasheet      the datasheet to enrich
     * @param integer                $icecatLocaleId icecat locale id
     */
    public function __construct(SimpleXMLElement $simpleDoc, IcecatProductDataSheet $datasheet, $icecatLocaleId)
    {
        $this->simpleDoc = $simpleDoc;
        $this->datasheet = $datasheet;
        $this->localeId  = $icecatLocaleId;
    }

    /**
     * {@inheritdoc}
     */
    public function enrich()
    {
        // verify if product id really exists
        if (isset($this->simpleDoc->Product['Code']) && $this->simpleDoc->Product['Code'] == -1) {
            file_put_contents('/tmp/productFailed.xml', $this->simpleDoc->asXML());

            throw new TransformException('unexistent product id');
        }
        // get existing data
        $existingData = json_decode($this->datasheet->getData(), true);
        // enrich data
        foreach ($this->simpleDoc->Product->ProductFeature as $featureTag) {
            $featureId = (integer) $featureTag['ID'];
            $existingData['productfeatures'][$featureId]['Value'][$this->localeId]= (string) $featureTag['Value'];
            $existingData['productfeatures'][$featureId]['Presentation_Value'][$this->localeId]= (string) $featureTag['Presentation_Value'];
        }
        // persist details
        $this->datasheet->setData(json_encode($existingData));
        $this->datasheet->setStatus(IcecatProductDataSheet::STATUS_IMPORT);
    }

}
