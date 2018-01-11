<?php

namespace Oro\Bundle\DotmailerBundle\Tests\Functional\Model;

use DotMailer\Api\DataTypes\ApiContactImport;
use DotMailer\Api\DataTypes\ApiContactImportStatuses;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\DotmailerBundle\Entity\AddressBook;
use Oro\Bundle\DotmailerBundle\Entity\AddressBookContact;
use Oro\Bundle\DotmailerBundle\Entity\AddressBookContactsExport;
use Oro\Bundle\DotmailerBundle\Entity\Contact;
use Oro\Bundle\DotmailerBundle\Model\ExportManager;
use Oro\Bundle\DotmailerBundle\Tests\Functional\AbstractImportExportTestCase;
use Oro\Bundle\IntegrationBundle\Entity\State;

class ExportManagerRevertRejectedExportsTest extends AbstractImportExportTestCase
{
    /**
     * @var ExportManager
     */
    protected $target;

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures(
            [
                'Oro\Bundle\DotmailerBundle\Tests\Functional\Fixtures\LoadAddressBookContactsExportData',
            ]
        );
        $this->target = $this->getContainer()->get('oro_dotmailer.export_manager');
    }

    public function testUpdateExportResultsRevertRejectedExports()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('oro_dotmailer.channel.fourth');

        /** @var AddressBook $addressBook */
        $addressBook = $this->getReference('oro_dotmailer.address_book.six');
        $rejectedByWatchDogImportId = $this->getReference('oro_dotmailer.address_book_contacts_export.rejected')
            ->getImportId();

        $stateRepository = $this->managerRegistry->getRepository(State::class);
        $scheduledForExport = $stateRepository->findBy(
            [
                'entityClass' => AddressBookContact::class,
                'state'       => State::STATE_SCHEDULED_FOR_EXPORT,
            ]
        );
        $this->assertCount(2, $scheduledForExport);

        $this->stubResource();

        $this->target->updateExportResultsForAddressBook($channel, $addressBook);

        $this->assertExportStatusUpdated($channel, $rejectedByWatchDogImportId, $addressBook);
        $this->assertAddressBookContactsHandled($channel, $addressBook);
    }

    /**
     * @param $channel
     * @param $expectedAddressBook
     */
    protected function assertAddressBookContactsHandled($channel, $expectedAddressBook)
    {
        /**
         * Check New Address Book Contact removed
         * if it was rejected and operation == AddressBookContact::EXPORT_NEW_CONTACT
         */
        $this->assertAddressBookContactNotExist(
            $channel,
            $this->getReference('oro_dotmailer.contact.add_contact_rejected'),
            $expectedAddressBook
        );

        /**
         * Check New Address Book Contact removed
         * if it was rejected and operation == AddressBookContact::EXPORT_ADD_TO_ADDRESS_BOOK
         */
        $this->assertAddressBookContactNotExist(
            $channel,
            $this->getReference('oro_dotmailer.contact.update_2'),
            $expectedAddressBook
        );

        /**
         * Check New Address Book Contact was not removed
         * if it was rejected and operation == AddressBookContact::EXPORT_UPDATE_CONTACT
         */
        $this->assertAddressBookContact(
            $channel,
            $this->getReference('oro_dotmailer.contact.update_contact_rejected'),
            $expectedAddressBook,
            Contact::STATUS_SUBSCRIBED
        );

        /**
         * Check other Address Book AddressBookContact not removed
         */
        $contact = $this->getReference('oro_dotmailer.contact.allen_case');
        $this->assertAddressBookContact($channel, $contact, $expectedAddressBook, Contact::STATUS_SUBSCRIBED);
    }

    /**
     * @param Channel     $channel
     * @param Contact     $contact
     * @param AddressBook $addressBook
     * @param string      $status
     */
    protected function assertAddressBookContact(Channel $channel, Contact $contact, AddressBook $addressBook, $status)
    {
        $addressBookContact = $this->managerRegistry
            ->getRepository('OroDotmailerBundle:AddressBookContact')
            ->findBy(['contact' => $contact, 'channel' => $channel, 'addressBook' => $addressBook]);

        $this->assertCount(1, $addressBookContact);
        $addressBookContact = reset($addressBookContact);

        $this->assertEquals($status, $addressBookContact->getStatus()->getId());
    }

    /**
     * @param Channel $channel
     * @param Contact $contact
     * @param AddressBook $expectedAddressBook
     */
    protected function assertAddressBookContactNotExist(
        Channel $channel,
        Contact $contact,
        AddressBook $expectedAddressBook
    ) {
        $addressBookContact = $this->managerRegistry
            ->getRepository('OroDotmailerBundle:AddressBookContact')
            ->findOneBy(['contact' => $contact, 'channel' => $channel, 'addressBook' => $expectedAddressBook]);
        $this->assertNull($addressBookContact);
    }

    /**
     * @param Channel $channel
     * @param $importId
     * @param AddressBook $expectedAddressBook
     */
    protected function assertExportStatusUpdated(Channel $channel, $importId, AddressBook $expectedAddressBook)
    {
        $status = AddressBookContactsExport::STATUS_REJECTED_BY_WATCHDOG;
        $exportEntities = $this->managerRegistry->getRepository('OroDotmailerBundle:AddressBookContactsExport')
            ->findBy(['channel' => $channel, 'importId' => $importId]);
        $this->assertCount(1, $exportEntities);

        /** @var AddressBookContactsExport|bool $exportEntity */
        $exportEntity = reset($exportEntities);

        $exportStatus = $exportEntity->getStatus();
        $this->assertEquals($status, $exportStatus->getId());

        $addressBookStatus = $expectedAddressBook->getSyncStatus();
        $this->assertEquals($status, $addressBookStatus->getId());
    }

    protected function stubResource()
    {
        $apiContactImportStatus = new ApiContactImport();
        $apiContactImportStatus->status = ApiContactImportStatuses::FINISHED;

        $this->resource->expects($this->any())
            ->method('GetContactsImportByImportId')
            ->willReturn($apiContactImportStatus);
    }
}