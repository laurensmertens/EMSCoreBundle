<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Model;

/**
 * Interface to be implemented by group managers. This adds an additional level
 * of abstraction between your application, and the actual repository.
 *
 * All changes to groups should happen through this interface.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
interface GroupManagerInterface
{
    /**
     * Returns an empty group instance.
     *
     * @param string $name
     *
     * @return GroupInterface
     */
    public function createGroup($name);

    /**
     * Deletes a group.
     */
    public function deleteGroup(GroupInterface $group);

    /**
     * Finds one group by the given criteria.
     *
     * @return array<array>
     */
    public function findGroupBy(array $criteria): array;

    /**
     * Finds a group by name.
     *
     * @param string $name
     *
     * @return array<array>
     */
    public function findGroupByName($name): array;

    /**
     * Returns a collection with all group instances.
     *
     * @return \Traversable
     */
    public function findGroups();

    /**
     * Returns the group's fully qualified class name.
     *
     * @return string
     */
    public function getClass();

    /**
     * Updates a group.
     */
    public function updateGroup(GroupInterface $group);
}
