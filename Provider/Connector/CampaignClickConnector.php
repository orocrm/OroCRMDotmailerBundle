<?php

namespace Oro\Bundle\DotmailerBundle\Provider\Connector;

class CampaignClickConnector extends AbstractActivityConnector implements ParallelizableInterface
{
    const TYPE = 'campaign_click';
    const JOB_IMPORT = 'dotmailer_campaign_click_import';

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        if (!$this->isFeaturesEnabled()) {
            return new \EmptyIterator();
        }

        $campaignsToSynchronize = $this->getCampaignToSyncrhonize();

        return $this->transport->getCampaignClicks(
            $this->managerRegistry,
            $campaignsToSynchronize,
            $this->getLastSyncDate()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.dotmailer.connector.campaign_click.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return self::JOB_IMPORT;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
