<?php

namespace OroCRM\Bundle\DotmailerBundle\ImportExport\Strategy;

use OroCRM\Bundle\DotmailerBundle\Entity\Contact;
use OroCRM\Bundle\DotmailerBundle\Exception\RuntimeException;
use OroCRM\Bundle\DotmailerBundle\Provider\Transport\Iterator\UnsubscribedContactsIterator;

class UnsubscribedContactsStrategy extends AbstractImportStrategy
{
    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        if (!$entity instanceof Contact) {
            throw new \RuntimeException(
                sprintf(
                    'Argument must be an instance of "%s", but "%s" is given',
                    'OroCRM\Bundle\DotmailerBundle\Entity\Contact',
                    is_object($entity) ? get_class($entity) : gettype($entity)
                )
            );
        }

        if (!$this->getChannel()) {
            throw new RuntimeException('Channel not found');
        }

        $contact = $this->registry->getRepository('OroCRMDotmailerBundle:Contact')
            ->findOneBy(['originId' => $entity->getOriginId(), 'channel' => $this->getChannel()]);
        if (!$contact) {
            $this->context->addError("Contact {$entity->getOriginId()} not found.");

            return null;
        }

        $originalValue = $this->context->getValue('itemData');
        if (empty($originalValue[UnsubscribedContactsIterator::ADDRESS_BOOK_KEY])) {
            throw new RuntimeException('Address book id required');
        }
        $addressBookOriginId = $originalValue[UnsubscribedContactsIterator::ADDRESS_BOOK_KEY];
        $this->updateAddressBookContact($entity, $contact, $addressBookOriginId);

        return $contact;
    }

    /**
     * @param Contact $contact
     * @param Contact $existingContact
     * @param int     $addressBookOriginId
     */
    protected function updateAddressBookContact(Contact $contact, Contact $existingContact, $addressBookOriginId)
    {
        foreach ($existingContact->getAddressBookContacts() as $addressBookContact) {
            $addressBook = $addressBookContact->getAddressBook();
            if ($addressBook && $addressBook->getOriginId() == $addressBookOriginId && $contact->getStatus()) {
                $reason = $this->getEnumValue('dm_cnt_status', $contact->getStatus()->getId());
                $addressBookContact->setStatus($reason);
                $addressBookContact->setUnsubscribedDate($contact->getUnsubscribedDate());

                return;
            }
        }
    }
}