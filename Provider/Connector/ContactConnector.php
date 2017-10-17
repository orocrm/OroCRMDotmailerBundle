<?php

namespace Oro\Bundle\DotmailerBundle\Provider\Connector;

use Oro\Bundle\DotmailerBundle\Entity\AddressBook;

class ContactConnector extends AbstractDotmailerConnector implements ParallelizableInterface
{
    const TYPE = 'contact';
    const IMPORT_JOB = 'dotmailer_new_contacts';
    const PROCESSED_ADDRESS_BOOK_IDS = 'processed_address_book_ids';

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        $addressBooksToSynchronize = $this->managerRegistry
            ->getRepository('OroDotmailerBundle:AddressBook')
            ->getConnectedAddressBooks($this->getChannel(), $this->getAddressBookId());

        $this->getContext()
            ->setValue(self::PROCESSED_ADDRESS_BOOK_IDS, array_map(function (AddressBook $addressBook) {
                return $addressBook->getId();
            }, $addressBooksToSynchronize));

        return $this->transport->getAddressBookContacts($addressBooksToSynchronize);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.dotmailer.connector.contact.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return self::IMPORT_JOB;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
