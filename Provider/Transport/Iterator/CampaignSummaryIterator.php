<?php

namespace OroCRM\Bundle\DotmailerBundle\Provider\Transport\Iterator;

use Doctrine\Common\Collections\Collection;

use DotMailer\Api\Resources\IResources;

use OroCRM\Bundle\DotmailerBundle\Entity\Campaign;

class CampaignSummaryIterator implements \Iterator
{
    const CAMPAIGN_KEY = 'related_campaign';

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var int
     */
    protected $currentItemIndex = 0;

    /**
     * @var bool
     */
    protected $isValid = true;

    /**
     * @var bool
     */
    protected $lastPage = false;

    /**
     * @var IResources
     */
    protected $dotmailerResources;

    /**
     * @var Collection|Campaign[]
     */
    protected $campaigns;

    /**
     * @param IResources $dotmailerResources
     * @param mixed      $campaigns
     */
    public function __construct(IResources $dotmailerResources, $campaigns)
    {
        $this->dotmailerResources = $dotmailerResources;
        $this->campaigns = $campaigns;
    }

    /**
     * @return array
     */
    protected function getItems()
    {
        $items = [];
        foreach ($this->campaigns as $campaign) {
            $item = $this->dotmailerResources->GetCampaignSummary($campaign->getOriginId());
            if ($item) {
                $item = $item->toArray();
                $item[self::CAMPAIGN_KEY] = $campaign->getId();
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (next($this->items) !== false || $this->tryToLoadItems()) {
            $this->currentItemIndex++;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->currentItemIndex;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $isValid = $this->isValid && current($this->items) !== false;
        return $isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->lastPage = false;
        $this->items = [];
        reset($this->items);
        $this->currentItemIndex = 0;

        $this->isValid = $this->tryToLoadItems();
    }

    /**
     * @return bool
     */
    protected function tryToLoadItems()
    {
        /** Requests count optimization */
        if ($this->lastPage) {
            return false;
        }

        $this->items = $this->getItems();
        reset($this->items);

        if (count($this->items) == 0) {
            return false;
        }

        $this->lastPage = true;

        return true;
    }
}
