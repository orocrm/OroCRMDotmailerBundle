<?php

namespace OroCRM\Bundle\DotmailerBundle\Provider\Transport\Iterator;

use Doctrine\ORM\QueryBuilder;

use OroCRM\Bundle\DotmailerBundle\Entity\AddressBook;
use OroCRM\Bundle\DotmailerBundle\ImportExport\Processor\RemovedExportProcessor;

class RemovedContactsExportIterator extends AbstractMarketingListItemIterator
{
    /**
     * @param AddressBook $addressBook
     *
     * @return QueryBuilder
     */
    protected function getIteratorQueryBuilder(AddressBook $addressBook)
    {
        $currentItemsInBatch = $this->importExportContext
            ->getValue(RemovedExportProcessor::CURRENT_BATCH_READ_ITEMS) ?: [];

        return $this->marketingListItemsQueryBuilderProvider
            ->getRemovedMarketingListItemsQB($addressBook, $currentItemsInBatch);
    }
}
