<?php
namespace Oro\Bundle\SearchBundle\Engine\Orm;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\SearchBundle\Engine\Orm\BaseDriver;
use Oro\Bundle\SearchBundle\Query\Query;

class PdoPgsql extends BaseDriver
{
    public $columns = array();
    public $needle;
    public $mode;

    /**
     * Init additional doctrine functions
     *
     * @param \Doctrine\ORM\EntityManager         $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function initRepo(EntityManager $em, ClassMetadata $class)
    {
        $ormConfig = $em->getConfiguration();
        $ormConfig->addCustomStringFunction(
            'TsvectorTsquery',
            'Oro\Bundle\SearchBundle\Engine\Orm\PdoPgsql\TsvectorTsquery'
        );
        $ormConfig->addCustomStringFunction('TsRank', 'Oro\Bundle\SearchBundle\Engine\Orm\PdoPgsql\TsRank');

        parent::initRepo($em, $class);
    }

    /**
     * Sql plain query to create fulltext index for Postgresql.
     *
     * @return string
     */
    public static function getPlainSql()
    {
        return "CREATE INDEX string_fts ON oro_search_index_text USING gin(to_tsvector('english', 'value'))";
    }

    /**
     * Create fulltext search string for string parameters (contains)
     *
     * @param integer $index
     * @param bool    $useFieldName
     *
     * @return string
     */
    protected function createContainsStringQuery($index, $useFieldName = true)
    {
        $stringQuery = '';
        if ($useFieldName) {
            $stringQuery = ' AND textField.field = :field' .$index;
        }

        return '(TsvectorTsquery(textField.value, :value' .$index. ')) = TRUE' . $stringQuery;
    }

    /**
     * Create search string for string parameters (not contains)
     *
     * @param integer $index
     * @param bool    $useFieldName
     *
     * @return string
     */
    protected function createNotContainsStringQuery($index, $useFieldName = true)
    {
        return $this->createContainsStringQuery($index, $useFieldName);
    }

    /**
     * Set string parameter for qb
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param integer                    $index
     * @param string                     $fieldValue
     * @param string                     $searchCondition
     */
    protected function setFieldValueStringParameter(QueryBuilder $qb, $index, $fieldValue, $searchCondition)
    {
        $searchArray = explode(' ', $fieldValue);
        foreach ($searchArray as $index => $string) {
            $searchArray[$index] = $string . ':*';
        }

        if ($searchCondition != Query::OPERATOR_CONTAINS) {
            foreach ($searchArray as $index => $string) {
                $searchArray[$index] = '!' . $string;
            }
        }

        $qb->setParameter('value' . $index, implode(' & ', $searchArray));
    }

    /**
     * Set fulltext range order by
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param int                        $index
     */
    protected function setTextOrderBy(QueryBuilder $qb, $index)
    {
        $qb->select(
            array(
                 'search as item',
                 'text',
                 'TsRank(textField.value, :value' .$index. ') AS rankField'
            )
        );
        $qb->orderBy('rankField', 'DESC');
    }
}
