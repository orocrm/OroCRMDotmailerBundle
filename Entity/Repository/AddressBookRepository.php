<?php

namespace OroCRM\Bundle\DotmailerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

class AddressBookRepository extends EntityRepository
{
    /**
     * Get address book ids which related with marketing lists
     *
     * @param Channel $channel
     *
     * @return array
     */
    public function getAddressBooksToSync(Channel $channel)
    {
        return $this->createQueryBuilder('addressBook')
            ->select('addressBook.id')
            ->where('addressBook.channel = :channel AND a.marketingList IS NOT NULL')
            ->setParameter('channel', $channel)
            ->getQuery()->getScalarResult();
    }
}
