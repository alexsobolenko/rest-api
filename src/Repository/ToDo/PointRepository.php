<?php declare(strict_types=1);

namespace App\Repository\ToDo;

use App\Entity\ToDo\Point;
use App\Entity\ToDo\Task;
use App\Exception\RestApiException;
use App\Repository\LoggerRepositoryTrait;
use App\Utils\DateTimeUtils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

class PointRepository extends ServiceEntityRepository
{
    use LoggerRepositoryTrait;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Point::class);
    }

    /**
     * @return array
     */
    public function findAllUncompleted(): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->eq('p.completed', ':completed'));
        $qb->setParameter('completed', false);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $id
     * @return Point
     * @throws RestApiException
     */
    public function getById(string $id): Point
    {
        $point = $this->find($id);
        if (!$point instanceof Point) {
            throw RestApiException::string('Point not found by id', Response::HTTP_NOT_FOUND);
        }

        return $point;
    }

    /**
     * @param Task $task
     * @param string $title
     * @return Point
     */
    public function create(Task $task, string $title): Point
    {
        $point = new Point($task, $title);
        $task->setUpdatedAt(DateTimeUtils::now());
        $this->_em->persist($point);
        $this->_em->flush();

        $this->logger->info('Point created', ['title' => $title]);

        return $point;
    }

    /**
     * @param Task $task
     * @param string $id
     * @param string $title
     * @return Point
     * @throws RestApiException
     */
    public function editTitle(Task $task, string $id, string $title): Point
    {
        $point = $this->getById($id);
        if ($point->getTask()->getId() !== $task->getId()) {
            throw RestApiException::string('Point do not relate with this task');
        }

        $point->setTitle($title);
        $task->setUpdatedAt(DateTimeUtils::now());
        $this->_em->flush();

        $this->logger->info('Point changed', ['id' => $id, 'title' => $title]);

        return $point;
    }

    /**
     * @param Task $task
     * @param string $id
     * @param bool $completed
     * @return Point
     * @throws RestApiException
     */
    public function editComplete(Task $task, string $id, bool $completed): Point
    {
        $point = $this->getById($id);
        if ($point->getTask()->getId() !== $task->getId()) {
            throw RestApiException::string('Point do not relate with this task');
        }

        $point->setCompleted($completed);
        $task->setUpdatedAt(DateTimeUtils::now());
        $this->_em->flush();

        $this->logger->info('Point changed', ['id' => $id, 'completed' => $completed]);

        return $point;
    }

    /**
     * @param Task $task
     * @param string $id
     * @throws RestApiException
     */
    public function delete(Task $task, string $id): void
    {
        $point = $this->getById($id);
        if ($point->getTask()->getId() !== $task->getId()) {
            throw RestApiException::string('Point do not relate with this task');
        }

        $task->setUpdatedAt(DateTimeUtils::now());
        $this->_em->remove($point);
        $this->_em->flush();

        $this->logger->info('Point deleted', ['id' => $id]);
    }
}
