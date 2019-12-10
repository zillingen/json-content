<?php


namespace Bolt\Extension\Zillingen\JsonContent\Controller;


use Bolt\Controller\Base;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public function get(Request $request) {
        $contentType = $request->request->get('contentType');
        $id = $request->request->get('id');

        return new JsonResponse([
            'contentType' => $contentType,
            'id' => $id
        ]);
    }

    public function create(Request $request)
    {

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