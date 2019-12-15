<?php


namespace Bolt\Extension\Zillingen\JsonContent\Controller;

use Bolt\Controller\Base;
use Bolt\Storage\Collection\Taxonomy;
use Bolt\Storage;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContentController extends Base
{
    /** @var array $config */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get extensions config
     * @return array:
     */
    public function getConfig()
    {
        return $this->config;
    }

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

    public function get(Request $request, string $contentType, int $id) {
        $record = $this->app['storage']->find($contentType, $id);

        return new JsonResponse([
            'record' => $record,
            'contentType' => $contentType,
            'id' => $id,
            'taxonomy' => $record->getTaxonomy()->serialize(),
        ]);
    }

    public function create(Request $request, string $contentType)
    {
        $repo = $this->getRepository($contentType);
        $data = json_decode($request->getContent(), true);
        $record = $repo->create($data, $repo->getClassMetadata());

        $repo->save($record);

        // TODO: move to function handleTaxonomy(array $taxonomy, $record)
        if (isset($data['taxonomy'])) {
            $taxonomyCollection = new Storage\Collection\Taxonomy();

            foreach ($data['taxonomy'] as $taxonomyType => $items) {
                foreach ($items as $item) {
                    $item['taxonomytype'] = $taxonomyType;
                    $item['content_id'] = $record->getId();
                    $item['contenttype'] = $record->getContentType();
                    $tax = new Storage\Entity\Taxonomy($item);
                    $repo = $this->getRepository(Storage\Entity\Taxonomy::class);
                    $repo->save($tax);
                }
            }

            $record->setTaxonomy($taxonomyCollection);
        }

        return new JsonResponse([
            "record" => [
                "id" => $record->getId(),
                "link" => $record->link(UrlGeneratorInterface::ABSOLUTE_URL),
            ]
        ]);
    }

    public function patch(Request $request)
    {

    }

    /**
     * Middleware checks auth token in the X-Auth-Token HTTP header
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