<?php


namespace Bolt\Extension\Zillingen\JsonContent\Controller;

use Bolt\Controller\Base;
use Bolt\Storage;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContentController extends Base
{
    /** @var array $config */
    protected $config;

    /**
     * ContentController constructor.
     * @param array $config Extension's config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get extension config
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     * @param ControllerCollection $c
     */
    protected function addRoutes(ControllerCollection $c)
    {
        $c
            ->get('/{contentType}/{id}', [$this, 'get'])
            ->assert('contentType', '\w+')
            ->assert('id', '\w+')
        ;
        $c
            ->post('/{contentType}', [$this, 'create'])
            ->assert('contentType', '\w+')
            ->before([$this, 'tokenAuthMiddleware'])
        ;
        $c
            ->patch('/{contentType}/{id}', [$this, 'patch'])
            ->assert('contentType', '\w+')
            ->assert('id', '\d+')
            ->before([$this, 'tokenAuthMiddleware'])
        ;
    }

    /**
     * Get record
     * @param string $contentType Content type
     * @param int $id Record id
     * @return JsonResponse
     */
    public function get(string $contentType, int $id) {
        $record = $this->app['storage']->find($contentType, $id);
        $taxonomy = $this->getRepository(Storage\Entity\Taxonomy::class)->findBy(['content_id' => $id]);
        $recordAsArray = $record->toArray();
        $recordAsArray['taxonomy'] = $taxonomy;

        return new JsonResponse($recordAsArray);
    }

    /**
     * Create new record
     * @param Request $request
     * @param string $contentType Content type
     * @return JsonResponse
     */
    public function create(Request $request, string $contentType)
    {
        $data = json_decode($request->getContent(), true);
        $repo = $this->getRepository($contentType);
        $record = $repo->create($data, $repo->getClassMetadata());

        $repo->save($record);

        if (isset($data['taxonomy'])) {
           $this->handleTaxonomy($data['taxonomy'], $record);
        }


        return new JsonResponse([
            "record" => [
                "id" => $record->getId(),
                "link" => $record->link(UrlGeneratorInterface::ABSOLUTE_URL),
            ]
        ],
        Response::HTTP_CREATED
        );
    }

    /**
     * Handle and save taxonomy data
     * @param array $taxonomy Taxonomy data
     * @param Storage\Entity\Entity $record Record instance
     */
    protected function handleTaxonomy(array $taxonomy, Storage\Entity\Entity $record)
    {
        $taxonomyCollection = new Storage\Collection\Taxonomy();

        foreach ($taxonomy as $taxonomyType => $items) {
            foreach ($items as $item) {
                $item['taxonomytype'] = $taxonomyType;
                $item['content_id'] = $record->getId();
                $item['contenttype'] = $record->getContentType();
                $tax = new Storage\Entity\Taxonomy($item);
                $repo = $this->getRepository(Storage\Entity\Taxonomy::class);
                $repo->insert($tax);
            }
        }

        $record->setTaxonomy($taxonomyCollection);
    }

    /**
     * Handle PATCH request
     * @param Request $request
     * @param string $contentType Content type
     * @param int $id Record id
     * @return JsonResponse|Response
     */
    public function patch(Request $request, string $contentType, int $id)
    {
        $data = json_decode($request->getContent(), true);
        $repo = $this->getRepository($contentType);
        $record = $repo->find($id);

        unset($data['id']);
        unset($data['taxonomy']);

        if (!$record) {
            return new Response(Response::HTTP_NOT_FOUND);
        }

        foreach ($data as $key => $value) {
            $record->set($key, $value);
        }

        $repo->update($record, ['id']);
        $updatedRecord = $repo->find($id);

        return new JsonResponse([
            'record' => $updatedRecord,
        ]);
    }

    /**
     * Middleware to authenticate request with X-Auth-Token HTTP header
     * @param Request $request
     * @return Response|void
     */
    public function tokenAuthMiddleware(Request $request)
    {
        $config = $this->getConfig();

        if (!$config['auth']['enabled']) {
            return;
        }

        $token = $request->headers->get('X-Auth-Token');

        if ($token === $config['auth']['access_token']) {
            return;
        }

        return new Response(
            Response::$statusTexts[
                Response::HTTP_UNAUTHORIZED
            ],
            Response::HTTP_UNAUTHORIZED
        );
    }
}