<?php declare(strict_types=1);

namespace App\Controller;

use App\DataProvider\EntityPatch;
use App\Entity\ToDo\Task;
use App\Exception\RestApiException;
use App\Model\PaginatedDataModel;
use App\Model\ToDo\TaskPatchApiSchema;
use App\Model\ToDo\TaskPostApiSchema;
use App\Normalizer\ToDo\PointNormalizer;
use App\Normalizer\ToDo\TaskNormalizer;
use App\Repository\ToDo\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/todo/tasks')]
class TodoController extends AbstractController
{
    /**
     * @OA\Get(
     *     tags={"ToDo App"},
     *     summary="Task list",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Items on page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="Search value",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="total", type="integer", example="20"),
     *             @OA\Property(property="pages", type="integer", example="2"),
     *             @OA\Property(property="limit", type="integer", example="10"),
     *             @OA\Property(property="page", type="integer", example="1"),
     *             @OA\Property(property="prev", type="integer", example="1"),
     *             @OA\Property(property="next", type="integer", example="2"),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="guid"),
     *                 @OA\Property(property="title", type="string", example="string"),
     *                 @OA\Property(property="priority", type="integer", example="1"),
     *                 @OA\Property(property="completed", type="string", example="55.5%"),
     *                 @OA\Property(property="created", type="string", example="2010-10-10 10:10:10"),
     *                 @OA\Property(property="updated", type="string", example="2010-10-10 10:10:10")
     *             )),
     *             @OA\Property(property="pageItems", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="timestamp", type="integer", example="150000"),
     *             @OA\Property(property="status", type="integer", example="400"),
     *             @OA\Property(property="path", type="string", example="/query/path"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route(path: '', methods: ['GET'], name: 'app.todo.tasks.list')]
    public function listAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var TaskRepository $taskRepo */
        $taskRepo = $entityManager->getRepository(Task::class);

        $filter = $request->query->all();
        $page = $filter['page'] ?? 1;
        $limit = $filter['limit'] ?? 10;

        $items = $taskRepo->searchBy($filter);
        $total = $taskRepo->countBy($filter);
        $payload = new PaginatedDataModel($total, $limit, $page, $items);

        return $this->json($payload, Response::HTTP_OK, [], [
            TaskNormalizer::CONTEXT_TYPE_KEY => TaskNormalizer::TYPE_LIST,
        ]);
    }

    /**
     * @OA\Get(
     *     tags={"ToDo App"},
     *     summary="Task details",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="task id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="guid"),
     *             @OA\Property(property="title", type="string", example="string"),
     *             @OA\Property(property="priority", type="integer", example="1"),
     *             @OA\Property(property="points", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="guid"),
     *                 @OA\Property(property="title", type="string", example="string"),
     *                 @OA\Property(property="completed", type="boolean", example="false"),
     *                 @OA\Property(property="created", type="string", example="2010-10-10 10:10:10"),
     *                 @OA\Property(property="updated", type="string", example="2010-10-10 10:10:10")
     *             )),
     *             @OA\Property(property="created", type="string", example="2010-10-10 10:10:10"),
     *             @OA\Property(property="updated", type="string", example="2010-10-10 10:10:10")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="timestamp", type="integer", example="150000"),
     *             @OA\Property(property="status", type="integer", example="400"),
     *             @OA\Property(property="path", type="string", example="/query/path"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param string $id
     * @return Response
     */
    #[Route(path: '/{id}', methods: ['GET'], name: 'app.todo.tasks.details')]
    public function detailsAction(Request $request, EntityManagerInterface $entityManager, string $id): Response
    {
        /** @var TaskRepository $taskRepo */
        $taskRepo = $entityManager->getRepository(Task::class);
        $payload = $taskRepo->getById($id);

        return $this->json($payload, Response::HTTP_OK, [], [
            TaskNormalizer::CONTEXT_TYPE_KEY => TaskNormalizer::TYPE_DETAILS,
            PointNormalizer::CONTEXT_TYPE_KEY => PointNormalizer::TYPE_DETAILS,
        ]);
    }

    /**
     * @OA\Post(
     *     tags={"ToDo App"},
     *     summary="Create task",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="title", description="Title", type="string", example="to do title"),
     *                 @OA\Property(property="priority", description="Priority", type="integer", example="0")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="guid"),
     *             @OA\Property(property="title", type="string", example="string"),
     *             @OA\Property(property="priority", type="integer", example="1"),
     *             @OA\Property(property="points", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="guid"),
     *                 @OA\Property(property="title", type="string", example="string"),
     *                 @OA\Property(property="completed", type="boolean", example="false"),
     *                 @OA\Property(property="created", type="string", example="2010-10-10 10:10:10"),
     *                 @OA\Property(property="updated", type="string", example="2010-10-10 10:10:10")
     *             )),
     *             @OA\Property(property="created", type="string", example="2010-10-10 10:10:10"),
     *             @OA\Property(property="updated", type="string", example="2010-10-10 10:10:10")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="timestamp", type="integer", example="150000"),
     *             @OA\Property(property="status", type="integer", example="400"),
     *             @OA\Property(property="path", type="string", example="/query/path"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param TaskPostApiSchema $schema
     * @return Response
     */
    #[Route(path: '', methods: ['POST'], name: 'app.todo.tasks.create')]
    public function createAction(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        TaskPostApiSchema $schema
    ): Response {
        $errors = $validator->validate($schema);
        if (count($errors) > 0) {
            throw RestApiException::constraints($errors);
        }

        /** @var TaskRepository $taskRepo */
        $taskRepo = $entityManager->getRepository(Task::class);
        $payload = $taskRepo->create($schema->title, $schema->priority);

        return $this->json($payload, Response::HTTP_OK, [], [
            TaskNormalizer::CONTEXT_TYPE_KEY => TaskNormalizer::TYPE_DETAILS,
        ]);
    }

    /**
     * @OA\Put(
     *     tags={"ToDo App"},
     *     summary="Edit task",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="task id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="title", description="Title", type="string", example="to do title"),
     *                 @OA\Property(property="priority", description="Priority", type="integer", example="0")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="guid"),
     *             @OA\Property(property="title", type="string", example="string"),
     *             @OA\Property(property="priority", type="integer", example="1"),
     *             @OA\Property(property="points", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="guid"),
     *                 @OA\Property(property="title", type="string", example="string"),
     *                 @OA\Property(property="completed", type="boolean", example="false"),
     *                 @OA\Property(property="created", type="string", example="2010-10-10 10:10:10"),
     *                 @OA\Property(property="updated", type="string", example="2010-10-10 10:10:10")
     *             )),
     *             @OA\Property(property="created", type="string", example="2010-10-10 10:10:10"),
     *             @OA\Property(property="updated", type="string", example="2010-10-10 10:10:10")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="timestamp", type="integer", example="150000"),
     *             @OA\Property(property="status", type="integer", example="400"),
     *             @OA\Property(property="path", type="string", example="/query/path"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param TaskPostApiSchema $schema
     * @param string $id
     * @return Response
     */
    #[Route(path: '/{id}', methods: ['PUT'], name: 'app.todo.tasks.edit')]
    public function editAction(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        TaskPostApiSchema $schema,
        string $id
    ): Response {
        $errors = $validator->validate($schema);
        if (count($errors) > 0) {
            throw RestApiException::constraints($errors);
        }

        /** @var TaskRepository $taskRepo */
        $taskRepo = $entityManager->getRepository(Task::class);
        $payload = $taskRepo->edit($id, $schema->title, $schema->priority);

        return $this->json($payload, Response::HTTP_OK, [], [
            TaskNormalizer::CONTEXT_TYPE_KEY => TaskNormalizer::TYPE_DETAILS,
            PointNormalizer::CONTEXT_TYPE_KEY => PointNormalizer::TYPE_DETAILS,
        ]);
    }

    /**
     * @OA\Patch(
     *     tags={"ToDo App"},
     *     summary="Patch task",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="task id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         description="Patch action",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="pointId", description="Priority", type="string", example="guid"),
     *                 @OA\Property(property="title", description="Title", type="string", example="point title")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="guid"),
     *             @OA\Property(property="title", type="string", example="string"),
     *             @OA\Property(property="priority", type="integer", example="1"),
     *             @OA\Property(property="points", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="guid"),
     *                 @OA\Property(property="title", type="string", example="string"),
     *                 @OA\Property(property="completed", type="boolean", example="false"),
     *                 @OA\Property(property="created", type="string", example="2010-10-10 10:10:10"),
     *                 @OA\Property(property="updated", type="string", example="2010-10-10 10:10:10")
     *             )),
     *             @OA\Property(property="created", type="string", example="2010-10-10 10:10:10"),
     *             @OA\Property(property="updated", type="string", example="2010-10-10 10:10:10")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="timestamp", type="integer", example="150000"),
     *             @OA\Property(property="status", type="integer", example="400"),
     *             @OA\Property(property="path", type="string", example="/query/path"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param TaskPatchApiSchema $schema
     * @param string $id
     * @return Response
     * @throws RestApiException
     */
    #[Route(path: '/{id}', methods: ['PATCH'], name: 'app.todo.tasks.patch')]
    public function patchAction(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        TaskPatchApiSchema $schema,
        string $id
    ): Response {
        $action = $request->query->get('action');
        if ($action === null) {
            throw RestApiException::string('Action is undefined');
        }

        $errors = $validator->validate($schema);
        if (count($errors) > 0) {
            throw RestApiException::constraints($errors);
        }

        /** @var TaskRepository $taskRepo */
        $taskRepo = $entityManager->getRepository(Task::class);
        switch ($action) {
            case EntityPatch::ADD_POINT:
                $payload = $taskRepo->addPoint($id, $schema->title);
                break;
            case EntityPatch::EDIT_POINT:
                $payload = $taskRepo->editPoint($id, $schema->pointId, $schema->title);
                break;
            case EntityPatch::DELETE_POINT:
                $payload = $taskRepo->deletePoint($id, $schema->pointId);
                break;
            case EntityPatch::DONE_POINT:
                $payload = $taskRepo->donePoint($id, $schema->pointId);
                break;
            case EntityPatch::UNDONE_POINT:
                $payload = $taskRepo->undonePoint($id, $schema->pointId);
                break;
            default:
                throw RestApiException::string('Unknown action value');
        }

        return $this->json($payload, Response::HTTP_OK, [], [
            TaskNormalizer::CONTEXT_TYPE_KEY => TaskNormalizer::TYPE_DETAILS,
            PointNormalizer::CONTEXT_TYPE_KEY => PointNormalizer::TYPE_DETAILS,
        ]);
    }

    /**
     * @OA\Delete(
     *     tags={"ToDo App"},
     *     summary="Delete task",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="task id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="timestamp", type="integer", example="150000"),
     *             @OA\Property(property="status", type="integer", example="400"),
     *             @OA\Property(property="path", type="string", example="/query/path"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param string $id
     * @return Response
     */
    #[Route(path: '/{id}', methods: ['DELETE'], name: 'app.todo.tasks.delete')]
    public function deleteAction(Request $request, EntityManagerInterface $entityManager, string $id): Response
    {
        /** @var TaskRepository $taskRepo */
        $taskRepo = $entityManager->getRepository(Task::class);
        $taskRepo->delete($id);

        return $this->json(null);
    }
}
