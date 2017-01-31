<?php

namespace OroCRM\Bundle\DotmailerBundle\Tests\Functional\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\DotmailerBundle\Entity\ChangedFieldLog;

class LoadUpdatesLogData extends AbstractFixture implements DependentFixtureInterface
{
    protected $data = [
        [
            'parentEntity'     => Contact::class,
            'relatedFieldPath' => 'firstName',
            'channel'          => 'orocrm_dotmailer.channel.first',
            'contact'          => 'orocrm_dotmailer.orocrm_contact.john.doe',
            'reference'        => 'orocrm_dotmailer.changed_field_log.first',
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            $log = new ChangedFieldLog();
            $data['channelId'] = $this->getReference($data['channel'])->getId();
            $data['relatedId'] = $this->getReference($data['contact'])->getId();
            $this->resolveReferenceIfExist($data, 'organization');
            $this->setEntityPropertyValues($log, $data, ['reference', 'contact', 'channel']);

            $this->addReference($data['reference'], $log);
            $manager->persist($log);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DotmailerBundle\Tests\Functional\Fixtures\LoadDotmailerContactData',
        ];
    }
}
