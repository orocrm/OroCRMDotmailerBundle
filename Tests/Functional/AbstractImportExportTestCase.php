<?php

namespace OroCRM\Bundle\DotmailerBundle\Tests\Functional;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\DotmailerBundle\Provider\Transport\DotmailerResourcesFactory;

abstract class AbstractImportExportTestCase extends WebTestCase
{
    const RESOURCES_FACTORY_ID = 'orocrm_dotmailer.transport.resources_factory';
    const SYNC_PROCESSOR = 'oro_integration.sync.processor';

    /**
     * @var DotmailerResourcesFactory
     */
    protected $oldResourceFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    protected function setUp()
    {
        $this->initClient();
        $this->stubResources();
        $this->managerRegistry = $this->getContainer()
            ->get('doctrine');
    }

    public function tearDown()
    {
        $this->getContainer()->set(self::RESOURCES_FACTORY_ID, $this->oldResourceFactory);

        $jobRepository = $this->getContainer()->get('akeneo_batch.job_repository');

        $reflection = new \ReflectionObject($jobRepository);
        $property = $reflection->getProperty('jobManager');
        $property->setAccessible(true);
        /** @var EntityManager $entityManager */
        $entityManager = $property->getValue($jobRepository);
        $entityManager
            ->getConnection()
            ->close();
        $entityManager->close();

        parent::tearDown();
    }

    protected function stubResources()
    {
        $this->resource = $this->getMock('DotMailer\Api\Resources\IResources');
        $resourceFactory = $this->getMock('OroCRM\Bundle\DotmailerBundle\Provider\Transport\DotmailerResourcesFactory');
        $resourceFactory->expects($this->any())
            ->method('createResources')
            ->will($this->returnValue($this->resource));

        $this->oldResourceFactory = $this->getContainer()
            ->get(self::RESOURCES_FACTORY_ID);
        $this->getContainer()
            ->set(self::RESOURCES_FACTORY_ID, $resourceFactory);
    }
}
