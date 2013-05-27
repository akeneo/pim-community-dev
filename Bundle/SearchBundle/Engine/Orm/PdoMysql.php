<?php
namespace Oro\Bundle\SearchBundle\Engine\Orm;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\SearchBundle\Engine\Orm\BaseDriver;
use Oro\Bundle\SearchBundle\Query\Query;

class PdoMysql extends BaseDriver
{
    public $columns = array();
    public $needle;

    /**
     * Init additional doctrine functions
     *
     * @param \Doctrine\ORM\EntityManager         $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function initRepo(EntityManager $em, ClassMetadata $class)
    {
        $ormConfig = $em->getConfiguration();
        $ormConfig->addCustomStringFunction('MATCH_AGAINST', 'Oro\Bundle\SearchBundle\Engine\Orm\PdoMysql\MatchAgainst');

        parent::initRepo($em, $class);
    }

    /**
     * Sql plain query to create fulltext index for mySql.
     *
     * @return string
     */
    public static function getPlainSql()
    {
        return "ALTER TABLE `oro_search_index_text` ADD FULLTEXT `value` ( `value`)";
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
        $qb->setParameter('value' . $index, $fieldValue);
    }

    /**
     * Add text search to qb
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param integer                    $index
     * @param array                      $searchCondition
     * @param boolean                    $setOrderBy
     *
     * @return string
     */
    protected function addTextField(QueryBuilder $qb, $index, $searchCondition, $setOrderBy = true)
    {
        $useFieldName = $searchCondition['fieldName'] == '*' ? false : true;

        $stringQuery = '';
        if ($useFieldName) {
            $stringQuery = ' AND textField.field = :field' .$index;
        }

        if ($searchCondition['condition'] == Query::OPERATOR_CONTAINS) {
            $whereExpr = $searchCondition['type'] . ' (' .
                ( 'MATCH_AGAINST(textField.value, :value' .$index. ' \'IN BOOLEAN MODE\')' . ' >0' .
                    $stringQuery . ')');

            if (strpos($searchCondition['fieldValue'], ' ') !== false) {
                $stingArray = explode(' ', $searchCondition['fieldValue']);
                foreach ($stingArray as $stringIndex => $string) {
                    $stingArray[$stringIndex] = $string . '*';
                    $value = implode(' ', $stingArray);
                }
            } else {
                $value = $searchCondition['fieldValue'] . '*';
            }
            $qb->setParameter('value' . $index, $value);

            if ($setOrderBy) {
                $qb->select(
                    array(
                         'search as item',
                         'text',
                         'MATCH_AGAINST(textField.value, \'' . $searchCondition['fieldValue'] . '\') AS rankField'
                    )
                );
                $qb->orderBy('rankField', 'DESC');
            }

        } else {
            $value = '%' . str_replace(' ', '%', trim($searchCondition['fieldValue'])) . '%';

            $whereExpr = $searchCondition['type'] . ' ('
                .('textField.value NOT LIKE :value' . $index . $stringQuery)
                . ')';
            $qb->setParameter('value' . $index, $value);
        }

        if ($useFieldName) {
            $qb->setParameter('field' . $index, $searchCondition['fieldName']);
        }

        return $whereExpr;
    }
}
