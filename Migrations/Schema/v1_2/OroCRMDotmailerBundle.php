<?php

namespace OroCRM\Bundle\DotmailerBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendDbIdentifierNameGenerator;
use Oro\Bundle\MigrationBundle\Migration\Extension\NameGeneratorAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Tools\DbIdentifierNameGenerator;

use OroCRM\Bundle\DotmailerBundle\Migration\AddContactExportConnectorToExistedIntegrationsQuery;

class OroCRMDotmailerBundle implements Migration, ExtendExtensionAwareInterface, NameGeneratorAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * @var ExtendDbIdentifierNameGenerator
     */
    protected $nameGenerator;

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_dm_ab_cnt_export');
        if (!$table->hasColumn('faults_processed')) {
            $table->addColumn('faults_processed', 'boolean', []);
            $table->addIndex(['faults_processed'], 'orocrm_dm_ab_cnt_exp_fault_idx', []);
        }

        $table = $schema->getTable('orocrm_dm_ab_contact');
        if (!$table->hasColumn('export_id')) {
            $table->addColumn('export_id', 'string', ['notnull' => false, 'length' => 36]);
            $table->addIndex(['export_id'], 'orocrm_dm_ab_cnt_export_id_idx', []);
        }

        $tableName = $this->nameGenerator->generateEnumTableName('dm_ab_cnt_exp_type');
        if (!$tableName || !$schema->hasTable($tableName)) {
            $this->extendExtension->addEnumField(
                $schema,
                $schema->getTable('orocrm_dm_ab_contact'),
                'exportOperationType',
                'dm_ab_cnt_exp_type',
                false,
                true,
                [
                    'extend' => ['owner' => ExtendScope::OWNER_CUSTOM]
                ]
            );

            $queries->addPostQuery(new AddContactExportConnectorToExistedIntegrationsQuery());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setNameGenerator(DbIdentifierNameGenerator $nameGenerator)
    {
        $this->nameGenerator = $nameGenerator;
    }
}
