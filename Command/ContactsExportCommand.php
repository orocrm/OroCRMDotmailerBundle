<?php

namespace OroCRM\Bundle\DotmailerBundle\Command;

use Doctrine\Common\Persistence\ManagerRegistry;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Command\ReverseSyncCommand;
use Oro\Bundle\IntegrationBundle\Command\AbstractSyncCronCommand;
use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
use Oro\Component\Log\OutputLogger;

use OroCRM\Bundle\DotmailerBundle\Model\ExportManager;
use OroCRM\Bundle\DotmailerBundle\Provider\ChannelType;
use OroCRM\Bundle\DotmailerBundle\Provider\Connector\ContactConnector;

class ContactsExportCommand extends AbstractSyncCronCommand
{
    const NAME = 'oro:cron:dotmailer:export';
    const EXPORT_MANAGER = 'orocrm_dotmailer.export_manager';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '*/5 * * * *';
    }

    /**
     * @var ReverseSyncProcessor
     */
    protected $reverseSyncProcessor;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Export contacts to Dotmailer');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new OutputLogger($output);
        $this->getContainer()
            ->get('oro_integration.logger.strategy')
            ->setLogger($logger);

        if ($this->isJobRunning(null)) {
            $logger->warning('Job already running. Terminating....');

            return;
        }

        $this->registry = $this->getService('doctrine');

        /** @var ExportManager $exportManager */
        $exportManager = $this->getService(self::EXPORT_MANAGER);

        $channels = $this->registry
            ->getRepository('OroIntegrationBundle:Channel')
            ->findBy(['type' => ChannelType::TYPE, 'enabled' => true]);
        foreach ($channels as $channel) {
            /**
             * If previous export not finished we need to update export results from Dotmailer
             * If after update export results all export batches is complete,
             * import updated contacts from Dotmailer will be started.
             * Else we need to start new export
             */
            if (!$exportManager->isExportFinished($channel)) {
                $exportManager->updateExportResults($channel);
            } else {
                $this->removePreviousAddressBookContactsExport($channel);
                $this->getReverseSyncProcessor()
                    ->process($channel, ContactConnector::TYPE, []);
            }
        }
    }

    /**
     * @return ReverseSyncProcessor
     */
    protected function getReverseSyncProcessor()
    {
        if (!$this->reverseSyncProcessor) {
            $this->reverseSyncProcessor = $this->getContainer()->get(ReverseSyncCommand::SYNC_PROCESSOR);
        }

        return $this->reverseSyncProcessor;
    }

    /**
     * @param Channel $channel
     */
    protected function removePreviousAddressBookContactsExport(Channel $channel)
    {
        $this->registry
            ->getRepository('OroCRMDotmailerBundle:AddressBookContactsExport')
            ->createQueryBuilder('abContactsExport')
            ->delete()
            ->where('abContactsExport.channel =:channel')
            ->getQuery()
            ->execute(['channel' => $channel]);
    }
}