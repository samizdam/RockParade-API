<?php

namespace AppBundle\Fixture;

use AppBundle\Entity\Band;
use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author Vehsamrak
 */
class BandFixture implements FixtureInterface
{

    /** {@inheritDoc} */
    public function load(ObjectManager $manager)
    {
        $user = new User('bander', 'Bander');

        $entities = [
            $user,
            new User('derban', 'Derban'),
            new Band('Banders', [$user], 'Band description.'),
        ];

        foreach ($entities as $entity) {
            $manager->persist($entity);
        }

        $manager->flush();
    }
}