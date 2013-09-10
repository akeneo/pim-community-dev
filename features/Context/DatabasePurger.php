<?php

namespace Context;

use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\CommitOrderCalculator;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Database purger based on doctrine ORMPurger that allows excluding specific tables from purge
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatabasePurger implements PurgerInterface
{
    private $excludedTables = array(
        'oro_user_acl_role',
        'oro_user_access_group_role',
        'oro_user_access_role',
        'oro_user_access_group',
        'oro_user_value_option',
        'oro_user_value',
        'oro_user_status',
        'oro_extend_orobundleuserbundleentityuser',
        'oro_user',
        'oro_extend_orobundleuserbundleentitygroup',
        'oro_access_group',
        'oro_user_acl',
        'oro_access_role',
        'oro_session',
        'oro_navigation_title',
        'oro_config_value',
        'oro_config',
        'oro_entity_config_value',
        'oro_entity_config_field',
        'oro_entity_config',
        'oro_user_business_unit',
        'oro_notification_recipient_user',
        'oro_notification_recipient_group',
        'oro_flexibleentity_attribute_option_value',
        'oro_flexibleentity_attribute_option',
        'oro_flexibleentity_attribute',
        'oro_flexibleentity_media',
        'oro_flexibleentity_price',
        'oro_flexibleentity_metric',
        'oro_flexibleentity_attribute_translation',
        'oro_flexibleentity_collection',
        'oro_search_index_decimal',
        'oro_search_index_text',
        'oro_search_index_integer',
        'oro_search_index_datetime',
        'oro_search_item',
        'oro_search_query',
        'oro_user_api',
        'oro_user_email',
        'oro_navigation_pagestate',
        'oro_navigation_history',
        'oro_navigation_item_pinbar',
        'oro_navigation_item',
        'oro_windows_state',
        'oro_audit',
        'oro_tag_tagging',
        'oro_tag_tag',
        'oro_notification_emailnotification',
        'oro_notification_recipient_list',
        'oro_entity_config_log_diff',
        'oro_entity_config_log',
        'oro_email_recipient',
        'oro_email_attachment_content',
        'oro_email_attachment',
        'oro_email_body',
        'oro_email',
        'oro_email_address',
        'oro_business_unit',
        'oro_organization',
        'oro_notification_email_spool',
        'oro_notification_event',
        'oro_email_template_translation',
        'oro_email_template',
        'oro_email_folder',
        'oro_email_origin',
    );

    const PURGE_MODE_DELETE = 1;
    const PURGE_MODE_TRUNCATE = 2;

    /** EntityManager instance used for persistence. */
    private $em;

    /**
     * If the purge should be done through DELETE or TRUNCATE statements
     *
     * @var integer
     */
    private $purgeMode;

    /**
     * Construct new purger instance.
     *
     * @param EntityManager $em
     * @param integer       $purgeMode
     */
    public function __construct(EntityManager $em, $purgeMode = self::PURGE_MODE_DELETE)
    {
        $this->em = $em;
        $this->purgeMode = $purgeMode;
    }

    /**
     * {@inheritdoc}
     */
    public function purge($entire = false)
    {
        $classes = array();
        $metadatas = $this->em->getMetadataFactory()->getAllMetadata();

        foreach ($metadatas as $metadata) {
            if (! $metadata->isMappedSuperclass) {
                $classes[] = $metadata;
            }
        }

        $commitOrder = $this->getCommitOrder($this->em, $classes);

        // Drop association tables first
        $orderedTables = $this->getAssociationTables($commitOrder);

        // Get platform parameters
        $platform = $this->em->getConnection()->getDatabasePlatform();

        // Drop tables in reverse commit order
        for ($i = count($commitOrder) - 1; $i >= 0; --$i) {
            $class = $commitOrder[$i];

            if (($class->isInheritanceTypeSingleTable() && $class->name != $class->rootEntityName)
                || $class->isMappedSuperclass) {
                continue;
            }

            $orderedTables[] = $class->getQuotedTableName($platform);
        }

        foreach ($orderedTables as $tbl) {
            if (in_array($tbl, $this->excludedTables)) {
                continue;
            }
            if ($this->purgeMode === self::PURGE_MODE_DELETE) {
                $this->em->getConnection()->executeUpdate("DELETE FROM " . $tbl);
            } else {
                $this->em->getConnection()->executeUpdate($platform->getTruncateTableSQL($tbl, true));
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param array         $classes
     *
     * @return array
     */
    private function getCommitOrder(EntityManager $em, array $classes)
    {
        $calc = new CommitOrderCalculator;

        foreach ($classes as $class) {
            $calc->addClass($class);

            // $class before its parents
            foreach ($class->parentClasses as $parentClass) {
                $parentClass = $em->getClassMetadata($parentClass);

                if ( ! $calc->hasClass($parentClass->name)) {
                    $calc->addClass($parentClass);
                }

                $calc->addDependency($class, $parentClass);
            }

            foreach ($class->associationMappings as $assoc) {
                if ($assoc['isOwningSide']) {
                    $targetClass = $em->getClassMetadata($assoc['targetEntity']);

                    if ( ! $calc->hasClass($targetClass->name)) {
                        $calc->addClass($targetClass);
                    }

                    // add dependency ($targetClass before $class)
                    $calc->addDependency($targetClass, $class);

                    // parents of $targetClass before $class, too
                    foreach ($targetClass->parentClasses as $parentClass) {
                        $parentClass = $em->getClassMetadata($parentClass);

                        if ( ! $calc->hasClass($parentClass->name)) {
                            $calc->addClass($parentClass);
                        }

                        $calc->addDependency($parentClass, $class);
                    }
                }
            }
        }

        return $calc->getCommitOrder();
    }

    /**
     * @param array $classes
     *
     * @return array
     */
    private function getAssociationTables(array $classes)
    {
        $associationTables = array();

        foreach ($classes as $class) {
            foreach ($class->associationMappings as $assoc) {
                if ($assoc['isOwningSide'] && $assoc['type'] == ClassMetadata::MANY_TO_MANY) {
                    $associationTables[] = $assoc['joinTable']['name'];
                }
            }
        }

        return $associationTables;
    }
}
