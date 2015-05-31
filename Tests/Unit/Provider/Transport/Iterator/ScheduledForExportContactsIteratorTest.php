<?php

namespace OroCRM\Bundle\DotmailerBundle\Tests\Unit\Provider\Transport\Iterator;

use OroCRM\Bundle\DotmailerBundle\Provider\Transport\Iterator\ScheduledForExportContactsIterator;

class ScheduledForExportContactsIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIterator()
    {
        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $addressBook = $this->getMock('OroCRM\Bundle\DotmailerBundle\Entity\AddressBook');
        $addressBook->expects($this->any())
            ->method('getOriginId')
            ->will($this->returnValue($addressBookOriginId = 234));

        $firstItem = ['id' => 23];
        $secondItem = ['id' => 44];
        $thirdItem = ['id' => 144];
        $expectedItems = [
            ['id' => 23, ScheduledForExportContactsIterator::ADDRESS_BOOK_KEY => $addressBookOriginId],
            ['id' => 44, ScheduledForExportContactsIterator::ADDRESS_BOOK_KEY => $addressBookOriginId],
            ['id' => 144, ScheduledForExportContactsIterator::ADDRESS_BOOK_KEY => $addressBookOriginId],
        ];

        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->exactly(2))
            ->method('setMaxResults')
            ->with($batchSize = 2)
            ->will($this->returnSelf());
        $qb->expects($this->exactly(2))
            ->method('setFirstResult')
            ->will($this->returnSelf());
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->setMethods(['getArrayResult'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $qb->expects($this->exactly(2))
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query->expects($this->at(0))
            ->method('getArrayResult')
            ->will($this->returnValue([$firstItem, $secondItem]));
        $query->expects($this->at(1))
            ->method('getArrayResult')
            ->will($this->returnValue([$thirdItem]));
        $repository = $this->getMockBuilder('OroCRM\Bundle\DotmailerBundle\Entity\Repository\ContactRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->exactly(2))
            ->method('getScheduledForExportByChannelQB')
            ->with($addressBook)
            ->will($this->returnValue($qb));

        $registry->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $iterator = new ScheduledForExportContactsIterator($addressBook, $registry);
        $iterator->setBatchSize($batchSize);

        foreach ($iterator as $item) {
            $this->assertEquals(current($expectedItems), $item);
            next($expectedItems);
        }
    }
}
