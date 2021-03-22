<?php

namespace App\Repository;

use App\Entity\District;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DistrictRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, District::class);
    }

    public function getCollection($sort = null, $filters = null, $perPage = null, $page = 1)
    {
        $qb = $this->createQueryBuilder('d')->select('d');
        $countPages = 1;

        if ($filters != null) {
            $this->qbFilters($qb, $filters);
        }
        if ($sort != null) {
            $this->qbSort($qb, $sort);
        }

        if ($perPage != null) {
            $countQb = $this->createQueryBuilder('d')->select('d.id');
            if ($filters != null) {
                $this->qbFilters($countQb, $filters);
            }
            $count = count($countQb->getQuery()->execute());
            $countPages = ceil($count / $perPage);
            $firstResult = ($page * $perPage) - $perPage;

            if ($page <= 0 || $page > $countPages) {
                //throw new \Exception("Invalid page number. Enter the page number between 1 and " . $countPages);
            }

            $qb->setMaxResults($perPage);
            $qb->setFirstResult($firstResult);
        }

        $query = $qb->getQuery();
        $results = $query->execute();

        if ($results) {
            return [
                "items" => $results,
                "countPages" => $countPages
            ];
        }

        return false;
    }

    private function qbFilters($qb, $filters)
    {
        foreach ($filters as $filterKey => $filterValue) {
            switch($filterKey)
            {
                case 'areaFrom':
                    $qb->andWhere("d.area >= :$filterKey");
                    $qb->setParameter($filterKey, $filterValue);
                    break;
                case 'areaTo':
                    $qb->andWhere("d.area <= :$filterKey");
                    $qb->setParameter($filterKey, $filterValue);
                    break;
                case 'populationFrom':
                    $qb->andWhere("d.population >= :$filterKey");
                    $qb->setParameter($filterKey, $filterValue);
                    break;
                case 'populationTo':
                    $qb->andWhere("d.population <= :$filterKey");
                    $qb->setParameter($filterKey, $filterValue);
                    break;
                case 'name':
                    $qb->andWhere("UPPER(d.$filterKey) LIKE :$filterKey");
                    $qb->setParameter($filterKey, '%'.strtoupper($filterValue).'%');
                    break;
                default:
                    $qb->andWhere("d.$filterKey = :$filterKey");
                    $qb->setParameter($filterKey, $filterValue);
                    break;
            }
        }
    }

    private function qbSort($qb, $sorts)
    {
        foreach ($sorts as $sortKey => $sortValue) {
            switch($sortKey)
            {
                case 'nameSort':
                    $qb->orderBy("d.name", $sortValue);
                    break;
                case 'areaSort':
                    $qb->orderBy("d.area", $sortValue);
                    break;
                case 'populationSort':
                    $qb->orderBy("d.population", $sortValue);
                    break;
                default:
                    break;
            }
        }
    }
}