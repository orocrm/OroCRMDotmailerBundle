<?php

namespace OroCRM\Bundle\DotmailerBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use OroCRM\Bundle\DotmailerBundle\Entity\Campaign;
use OroCRM\Bundle\DotmailerBundle\Entity\Contact;
use OroCRM\Bundle\DotmailerBundle\Entity\AddressBook;

class AddressBookTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressBook
     */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->entity = new AddressBook();
    }

    /**
     * @dataProvider flatPropertiesDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        call_user_func_array(array($this->entity, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($expected, call_user_func_array(array($this->entity, 'get' . ucfirst($property)), array()));
    }

    public function flatPropertiesDataProvider()
    {
        $now = new \DateTime('now');
        $channel = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel');
        $organization = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');

        return array(
            'channel' => array('channel', $channel, $channel),
            'name' => array('name', 'testName', 'testName'),
            'contactCount' => array('contactCount', 10, 10),
            'createdAt' => array('createdAt', $now, $now),
            'updatedAt' => array('updatedAt', $now, $now),
            'owner' => array('owner', $organization, $organization),
        );
    }

    public function testOriginIdWorks()
    {
        $this->entity->setOriginId(1);
        $this->assertEquals(1, $this->entity->getOriginId());
    }

    public function testIdWorks()
    {
        $this->assertEmpty($this->entity->getId());
    }

    public function testPrePersist()
    {
        $this->assertEmpty($this->entity->getCreatedAt());
        $this->assertEmpty($this->entity->getUpdatedAt());

        $this->entity->prePersist();

        $this->assertInstanceOf('DateTime', $this->entity->getCreatedAt());
        $this->assertInstanceOf('DateTime', $this->entity->getUpdatedAt());
    }

    public function testPreUpdate()
    {
        $this->assertEmpty($this->entity->getUpdatedAt());

        $this->entity->preUpdate();

        $this->assertInstanceOf('DateTime', $this->entity->getUpdatedAt());
    }

    public function testAddCampaign()
    {
        $this->assertEmpty($this->entity->getCampaigns()->toArray());

        $campaign = new Campaign();
        $this->entity->addCampaign($campaign);
        $campaigns = $this->entity->getCampaigns()->toArray();
        $this->assertCount(1, $campaigns);
        $this->assertEquals($campaign, current($campaigns));
    }

    public function testRemoveCampaign()
    {
        $this->assertEmpty($this->entity->getCampaigns()->toArray());

        $campaign = new Campaign();
        $this->entity->addCampaign($campaign);
        $campaigns = $this->entity->getCampaigns()->toArray();
        $this->assertCount(1, $campaigns);
        $this->assertEquals($campaign, current($campaigns));
        $this->entity->removeCampaign($campaign);
        $this->assertEmpty($this->entity->getCampaigns()->toArray());
    }

    public function testHasCampaign()
    {
        $this->assertEmpty($this->entity->getCampaigns()->toArray());

        $campaign = new Campaign();
        $this->assertFalse($this->entity->hasCampaign($campaign));
        $this->entity->addCampaign($campaign);
        $campaigns = $this->entity->getCampaigns()->toArray();
        $this->assertCount(1, $campaigns);
        $this->assertTrue($this->entity->hasCampaign($campaign));
    }

    public function testSetCampaigns()
    {
        $this->assertEmpty($this->entity->getCampaigns()->toArray());

        $campaign = new Campaign();
        $this->entity->addCampaign($campaign);
        $campaigns = $this->entity->getCampaigns()->toArray();
        $this->assertCount(1, $campaigns);
        $this->assertEquals($campaign, current($campaigns));
        $this->entity->setCampaigns(new ArrayCollection());
        $this->assertEmpty($this->entity->getCampaigns()->toArray());
    }

    public function testAddContact()
    {
        $this->assertEmpty($this->entity->getContacts()->toArray());

        $contact = new Contact();
        $this->entity->addContact($contact);
        $contacts = $this->entity->getContacts()->toArray();
        $this->assertCount(1, $contacts);
        $this->assertEquals($contact, current($contacts));
    }

    public function testRemoveContact()
    {
        $this->assertEmpty($this->entity->getContacts()->toArray());

        $contact = new Contact();
        $this->entity->addContact($contact);
        $contacts = $this->entity->getContacts()->toArray();
        $this->assertCount(1, $contacts);
        $this->assertEquals($contact, current($contacts));
        $this->entity->removeContact($contact);
        $this->assertEmpty($this->entity->getContacts()->toArray());
    }

    public function testHasContact()
    {
        $this->assertEmpty($this->entity->getContacts()->toArray());

        $contact = new Contact();
        $this->assertFalse($this->entity->hasContact($contact));
        $this->entity->addContact($contact);
        $contacts = $this->entity->getContacts()->toArray();
        $this->assertCount(1, $contacts);
        $this->assertTrue($this->entity->hasContact($contact));
    }

    public function testSetContacts()
    {
        $this->assertEmpty($this->entity->getContacts()->toArray());

        $contact = new Contact();
        $this->entity->addContact($contact);
        $contacts = $this->entity->getContacts()->toArray();
        $this->assertCount(1, $contacts);
        $this->assertEquals($contact, current($contacts));
        $this->entity->setContacts(new ArrayCollection());
        $this->assertEmpty($this->entity->getContacts()->toArray());
    }
}
