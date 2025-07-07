<?php

namespace App\Repository;

use App\Entity\User;
use App\Tools\NewPDO;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function search($query, User $user)
    {
        $pdo = new NewPDO();

        return $pdo->fetch("SELECT user.id, first_name, last_name, alias AS username, friend.id as request_id, 
                                    CASE WHEN friend.id IS NOT NULL AND is_waiting IS NOT TRUE THEN 'friend' 
                                         WHEN friend.applicant_id = user.id AND is_waiting IS TRUE THEN 'waiting' 
                                         WHEN friend.receiver_id = user.id AND is_waiting IS TRUE THEN 'need_response' 
                                         WHEN friend.id IS NULL THEN 'none'
                                         ELSE 'none' END AS friend_status
                                  FROM user 
                                  LEFT JOIN friend ON (user.id = friend.receiver_id OR user.id = friend.applicant_id) AND (friend.applicant_id = ? OR friend.receiver_id = ?)
                                  WHERE user.id != ? AND (LOWER(first_name) LIKE ? OR LOWER(last_name) LIKE ? OR LOWER(alias) LIKE ?)", [$user->getId(), $user->getId(), $user->getId(), '%'.mb_strtolower($query).'%','%'.mb_strtolower($query).'%', '%'.mb_strtolower($query).'%']);
    }

}
