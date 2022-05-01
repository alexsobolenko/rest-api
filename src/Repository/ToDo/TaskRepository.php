<?php declare(strict_types=1);

namespace App\Repository\ToDo;

use App\Entity\ToDo\Point;
use App\Entity\ToDo\Task;
use App\Exception\RestApiException;
use App\Repository\LoggerRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

class TaskRepository extends ServiceEntityRepository
{
    use LoggerRepositoryTrait;

    private PointRepository $pointRepo;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);

        /** @var PointRepository $repo */
        $repo = $this->_em->getRepository(Point::class);
        $this->pointRepo = $repo;
    }

    /**
     * @param string $id
     * @return Task
     * @throws RestApiException
     */
    public function getById(string $id): Task
    {
        $task = $this->find($id);
        if (!$task instanceof Task) {
            throw RestApiException::string('Task not found by id', Response::HTTP_NOT_FOUND);
        }

        return $task;
    }

    /**
     * @param array $filter
     * @return array
     * @throws RestApiException
     */
    public function searchBy(array $filter): array
    {
        try {
            $qb = $this->buildFilterQuery($filter);

            $page = $filter['page'] ?? 1;
            $limit = $filter['limit'] ?? 10;
            $qb->setMaxResults($limit);
            $qb->setFirstResult(($page - 1) * $limit);

            return $qb->getQuery()->getResult();
        } catch (\Throwable $e) {
            throw RestApiException::string($e->getMessage());
        }
    }

    /**
     * @param array $filter
     * @return int
     * @throws RestApiException
     */
    public function countBy(array $filter): int
    {
        try {
            $qb = $this->buildFilterQuery($filter);
            $qb->select('COUNT(t.id)');

            return intval($qb->getQuery()->getSingleScalarResult());
        } catch (\Throwable $e) {
            throw RestApiException::string($e->getMessage());
        }
    }

    /**
     * @param string $title
     * @param int $priority
     * @return Task
     */
    public function create(string $title, int $priority): Task
    {
        $task = new Task($title, $priority);
        $this->_em->persist($task);
        $this->_em->flush();

        $this->logger->info('Task created', ['title' => $title, 'priority' => $priority]);

        return $task;
    }

    /**
     * @param string $id
     * @param string $title
     * @param int $priority
     * @return Task
     * @throws RestApiException
     */
    public function edit(string $id, string $title, int $priority): Task
    {
        $task = $this->getById($id);
        $task->setTitle($title);
        $task->setPriority($priority);
        $this->_em->flush();

        $this->logger->info('Task changed', ['id' => $id, 'title' => $title, 'priority' => $priority]);

        return $task;
    }

    /**
     * @param string $id
     * @throws RestApiException
     */
    public function delete(string $id): void
    {
        $task = $this->getById($id);
        $this->_em->remove($task);
        $this->_em->flush();

        $this->logger->info('Task deleted', ['id' => $id]);
    }

    /**
     * @param string $id
     * @param string $title
     * @return Task
     * @throws RestApiException
     */
    public function addPoint(string $id, string $title): Task
    {
        $task = $this->getById($id);
        $this->pointRepo->create($task, $title);

        return $task;
    }

    /**
     * @param string $id
     * @param string $pointId
     * @param string $title
     * @return Task
     * @throws RestApiException
     */
    public function editPoint(string $id, string $pointId, string $title): Task
    {
        $task = $this->getById($id);
        $this->pointRepo->editTitle($task, $pointId, $title);

        return $task;
    }

    /**
     * @param string $id
     * @param string $pointId
     * @return Task
     * @throws RestApiException
     */
    public function deletePoint(string $id, string $pointId): Task
    {
        $task = $this->getById($id);
        $this->pointRepo->delete($task, $pointId);

        return $task;
    }

    /**
     * @param string $id
     * @param string $pointId
     * @return Task
     * @throws RestApiException
     */
    public function donePoint(string $id, string $pointId): Task
    {
        $task = $this->getById($id);
        $this->pointRepo->editComplete($task, $pointId, true);

        return $task;
    }

    /**
     * @param string $id
     * @param string $pointId
     * @return Task
     * @throws RestApiException
     */
    public function undonePoint(string $id, string $pointId): Task
    {
        $task = $this->getById($id);
        $this->pointRepo->editComplete($task, $pointId, false);

        return $task;
    }

    /**
     * @param array $filter
     * @return QueryBuilder
     */
    private function buildFilterQuery(array $filter): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t');

        $content = $filter['content'] ?? null;
        if ($content !== '' && $content !== null) {
            $qb->andWhere($qb->expr()->like('t.title', ':content'));
            $qb->setParameter('content', '%' . $content . '%');
        }

        $uncompleted = filter_var($filter['uncompleted'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        if ($uncompleted === true) {
            $ids = [];
            foreach ($this->pointRepo->findAllUncompleted() as $point) {
                /** @var Point $point */
                if (!in_array($point->getTask()->getId(), $ids)) {
                    $ids[] = $point->getTask()->getId();
                }
            }
            $qb->andWhere($qb->expr()->in('t.id', ':ids'));
            $qb->setParameter('ids', $ids);
        }

        $qb->addOrderBy('t.priority', 'DESC');
        $qb->addOrderBy('t.title', 'ASC');

        return $qb;
    }
}
