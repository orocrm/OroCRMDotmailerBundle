<?php

namespace OroCRM\Bundle\DotmailerBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use OroCRM\Bundle\DotmailerBundle\Entity\DataField;
use OroCRM\Bundle\DotmailerBundle\Model\DataFieldManager;
use OroCRM\Bundle\DotmailerBundle\Exception\RestClientException;
use OroCRM\Bundle\DotmailerBundle\Exception\RuntimeException;

class DataFieldRemoveListener
{
     /** @var DataFieldManager */
    protected $dataFieldManager;

    /**
     * @param DataFieldManager $dataFieldManager
     */
    public function __construct(DataFieldManager $dataFieldManager)
    {
        $this->dataFieldManager = $dataFieldManager;
    }

    /**
     * Remove origin data field.
     * If origin data field can't be remove, throw exception and don't allow to remove record
     *
     * @param LifecycleEventArgs $args
     * @throws RuntimeException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof DataField || $entity->isForceRemove()) {
            return;
        }

        //try to delete the field in Dotmailer. Throwing exception if the field can't be removed there.
        try {
            $result = $this->dataFieldManager->removeOriginDataField($entity);
        } catch (RestClientException $e) {
            if ($e->getPrevious()) {
                //for system fields dotmailer returns 404 response with error message
                throw new RuntimeException($e->getPrevious()->getMessage());
            } else {
                //uknown reason
                throw new RuntimeException(
                    'The field cannot be removed.'
                );
            }
        }
        if (!isset($result['result']) || $result['result'] === 'false') {
            throw new RuntimeException('The field cannot be removed. It is in use elsewhere in the system.');
        }
    }
}
