<?php

namespace Oro\Bundle\DotmailerBundle\Tests\Functional\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;

class LoadContactData extends AbstractFixture implements DependentFixtureInterface
{
    protected $data = [
        [
            'firstName'    => 'Daniel',
            'lastName'     => 'Case',
            'email'        => 'daniel.case@example.com',
            'organization' => 'oro_dotmailer.organization.foo',
            'owner'        => 'oro_dotmailer.user.john.doe',
            'reference'    => 'oro_dotmailer.orocrm_contact.daniel.case',
        ],
        [
            'firstName'    => 'John',
            'lastName'     => 'Case',
            'email'        => 'john.case@example.com',
            'organization' => 'oro_dotmailer.organization.foo',
            'owner'        => 'oro_dotmailer.user.john.doe',
            'reference'    => 'oro_dotmailer.orocrm_contact.john.case',
        ],
        [
            'firstName'    => 'Jack',
            'lastName'     => 'Case',
            'email'        => 'jack.case@example.com',
            'organization' => 'oro_dotmailer.organization.foo',
            'owner'        => 'oro_dotmailer.user.john.doe',
            'reference'    => 'oro_dotmailer.orocrm_contact.jack.case',
        ],
        [
            'firstName'    => 'Alex',
            'lastName'     => 'Case',
            'email'        => 'alex.case@example.com',
            'organization' => 'oro_dotmailer.organization.foo',
            'owner'        => 'oro_dotmailer.user.john.doe',
            'reference'    => 'oro_dotmailer.orocrm_contact.alex.case',
        ],
        [
            'firstName'    => 'Allen',
            'lastName'     => 'Case',
            'email'        => 'allen.case@example.com',
            'organization' => 'oro_dotmailer.organization.foo',
            'owner'        => 'oro_dotmailer.user.john.doe',
            'reference'    => 'oro_dotmailer.orocrm_contact.allen.case',
        ],
        [
            'firstName'    => 'Without email',
            'lastName'     => 'Case',
            'organization' => 'oro_dotmailer.organization.foo',
            'owner'        => 'oro_dotmailer.user.john.doe',
            'reference'    => 'oro_dotmailer.orocrm_contact.without_email.case',
        ],
        [
            'firstName'    => 'John',
            'lastName'     => 'Smith',
            'email'        => 'john.smith@example.com',
            'organization' => 'oro_dotmailer.organization.foo',
            'owner'        => 'oro_dotmailer.user.john.doe',
            'reference'    => 'oro_dotmailer.orocrm_contact.john.smith',
        ],
        [
            'firstName'    => 'Nick',
            'lastName'     => 'Case',
            'email'        => 'nick.case@example.com',
            'organization' => 'oro_dotmailer.organization.foo',
            'owner'        => 'oro_dotmailer.user.john.doe',
            'reference'    => 'oro_dotmailer.orocrm_contact.nick.case',
        ],
        [
            'firstName'    => 'Mike',
            'lastName'     => 'Case',
            'email'        => 'mike.case@example.com',
            'organization' => 'oro_dotmailer.organization.foo',
            'owner'        => 'oro_dotmailer.user.john.doe',
            'reference'    => 'oro_dotmailer.orocrm_contact.mike.case',
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            $contact = new Contact();
            $this->resolveReferenceIfExist($data, 'owner');
            $this->resolveReferenceIfExist($data, 'organization');
            $this->setEntityPropertyValues($contact, $data, ['reference', 'email']);

            if (!empty($data['email'])) {
                $email = new ContactEmail();
                $email->setEmail($data['email']);
                $email->setPrimary(true);
                $contact->addEmail($email);
            }

            $this->addReference($data['reference'], $contact);
            $manager->persist($contact);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\DotmailerBundle\Tests\Functional\Fixtures\LoadUserData',
        ];
    }
}
