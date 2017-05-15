<?php

namespace EMS\CoreBundle\Repository;

use EMS\CoreBundle\Entity\Environment;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * EnvironmentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EnvironmentRepository extends \Doctrine\ORM\EntityRepository
{
	
	public function getEnvironmentsStats() {
		/** @var QueryBuilder $qb */
		$qb = $this->createQueryBuilder('e')
			->select('e as environment', 'count(r) as counter', 'count(r.deleted) as deleted')
			->leftJoin('e.revisions', 'r')
			->groupBy('e.id');
		
		return $qb->getQuery()->getResult();
	}
	
	public function findAvailableEnvironements(Environment $defaultEnv) {
		/** @var QueryBuilder $qb */
		$qb = $this->createQueryBuilder('e');
		$qb->where($qb->expr()->neq('e.id', ':defaultEnvId'));
		$qb->andWhere($qb->expr()->neq('e.managed', ':false'));
		$qb->orderBy('e.name', 'ASC');
		$qb->setParameters([
				'false' => false,
				'defaultEnvId' => $defaultEnv->getId()
		]);
	
		return $qb->getQuery()->getResult();
	}
	
	
	public function findManagedIndexes() {
		$qb = $this->createQueryBuilder('e');
		$qb->select('e.alias alias');
		$qb->where($qb->expr()->eq('e.managed', ':true'));
		$qb->setParameters([':true' => true]);
		return $qb->getQuery()->getResult();
	}
	
	
	public function findByName($name) {
		return $this->findOneBy([
				'deleted' => false,
				'name' => $name,
		]);
	}
	


	public function findAllAsAssociativeArray($field){
		$qb = $this->createQueryBuilder('e');
		$qb->select('e.'.$field.' key, e.name name, e.color color, e.alias alias, e.managed managed, e.baseUrl baseUrl, e.circles circles');
	
		$out = [];
		$result = $qb->getQuery()->getResult();
		foreach ($result as $record){
			$out[$record['key']] = [
					'color' => $record['color'],
					'name' => $record['name'],
					'alias' => $record['alias'],
					'managed' => $record['managed'],
					'baseUrl' => $record['baseUrl'],
					'circles' => $record['circles'],
			];
		}
	
		return $out;
	}
	
}
