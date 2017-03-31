<?php

namespace SmartCore\Bundle\CMSBundle\Controller;

use SmartCore\Bundle\CMSBundle\Entity\Node;
use SmartCore\Bundle\CMSBundle\Twig\RegionRenderHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class EngineController extends Controller
{
    /**
     * Коллекция фронтальных элементов управления.
     *
     * @var array
     */
    protected $front_controls = [];

    /**
     * @param Request    $request
     * @param string     $slug
     * @param array|null $options
     *
     * @return Response
     */
    public function runAction(Request $request, $slug, array $options = null)
    {
        $twig       = $this->get('twig');
        $cmsContext = $this->get('cms.context');

        // Кеширование роутера.
        $cache_key = md5('cms_router'.$request->getBaseUrl().$slug);
        if (false == $router_data = $this->get('tagcache')->get($cache_key)) {
            $router_data = $this->get('cms.router')->match($request->getBaseUrl(), $slug, HttpKernelInterface::MASTER_REQUEST, $options);
            $this->get('tagcache')->set($cache_key, $router_data, ['folder', 'node']);
        }

        if ($router_data['status'] == 301 and $router_data['redirect_to']) {
            return new RedirectResponse($router_data['redirect_to'], $router_data['status']);
        }

        $cmsContext->setTemplate($router_data['template']);
        if (empty($router_data['folders'])) { // Случай пустой инсталляции, когда еще ни одна папка не создана.
            $this->get('cms.toolbar')->prepare();

            return $twig->render('CMSBundle::welcome.html.twig');
        }

        if ($router_data['status'] == 404) {
            throw new NotFoundHttpException('Page not found.');
        } elseif ($router_data['status'] == 403) {
            throw new AccessDeniedHttpException('Access Denied.');
        }

        $this->get('html')->setMetas($router_data['meta']);

        foreach ($router_data['folders'] as $folder) {
            $this->get('cms.breadcrumbs')->add($this->get('cms.folder')->getUri($folder), $folder->getTitle(), $folder->getDescription());
        }

        $cmsContext->setCurrentFolderId($router_data['current_folder_id']);
        $cmsContext->setCurrentFolderPath($router_data['current_folder_path']);

        // Список нод кешируется только при GET запросах.
        $router_data['http_method'] = $request->getMethod();

        $nodes = $this->get('cms.node')->buildList($router_data);

        \Profiler::start('buildModulesData');
        $nodesResponses = $this->buildModulesData($request, $nodes);
        \Profiler::end('buildModulesData');

        if ($nodesResponses instanceof Response) {
            return $nodesResponses;
        }

        $this->get('cms.toolbar')->prepare(isset($this->front_controls['node']) ? $this->front_controls['node'] : null);

        try {
            return $twig->render("SiteBundle::{$cmsContext->getTemplate()}.html.twig", $nodesResponses);
        } catch (\Twig_Error_Loader $e) {
            if ($this->get('kernel')->isDebug()) {
                return $twig->render('CMSBundle::error.html.twig', ['errors' => [$e->getMessage()]]);
            }
        }

        return $twig->render('CMSBundle::welcome.html.twig');
    }

    /**
     * Сборка "областей" из подготовленного списка нод.
     * По мере прохождения, подключаются и запускаются нужные модули с нужными параметрами.
     *
     * @param Request $request
     * @param Node[]  $nodes
     *
     * @return array|Response|RedirectResponse
     */
    protected function buildModulesData(Request $request, array $nodes)
    {
        $prioritySorted = [];
        $nodesResponses = [];

        foreach ($nodes as $node) {
            if (!isset($nodesResponses[$node->getRegionName()])) {
                $nodesResponses[$node->getRegionName()] = new RegionRenderHelper();
            }

            $prioritySorted[$node->getPriority()][$node->getId()] = $node;
            $nodesResponses[$node->getRegionName()]->{$node->getId()} = new Response();
        }

        krsort($prioritySorted);

        foreach ($prioritySorted as $nodes) {
            /** @var \SmartCore\Bundle\CMSBundle\Entity\Node $node */
            foreach ($nodes as $node) {
                if ($this->isGranted('ROLE_ADMIN') and $node->getIsUseEip()) {
                    $node->setEip(true);
                }

                // Выполняется модуль, все параметры ноды берутся в \SmartCore\Bundle\CMSBundle\Listener\ModuleControllerModifierListener
                \Profiler::start($node->getId().' '.$node->getModule(), 'node');

                if ($this->get('cms.module')->has($node->getModule())) {
                    $moduleResponse = $this->forward($node->getId(), [
                        '_route' => 'cms_getprocessor',
                        '_route_params' => $request->attributes->get('_route_params'),
                    ], $request->query->all());

                    // Обрамление ноды пользовательским кодом.
                    $moduleResponse->setContent($node->getCodeBefore().$moduleResponse->getContent().$node->getCodeAfter());
                } else {
                    $moduleResponse = new Response('Module "'.$node->getModule().'" is unavailable.');
                }

                \Profiler::end($node->getId().' '.$node->getModule(), 'node');

                if ($moduleResponse instanceof RedirectResponse
                    or ($moduleResponse instanceof Response and $moduleResponse->isNotFound())
                    or 0 === strpos($moduleResponse->getContent(), '<!DOCTYPE ') // @todo Пока так определяются ошибки от симфони.
                ) {
                    return $moduleResponse;
                }

                // @todo сделать отправку front_controls в ответе метода.
                if ($this->isGranted('ROLE_ADMIN')) {
                    $this->front_controls['node']['__node_'.$node->getId()] = $node->getFrontControls();
                    $this->front_controls['node']['__node_'.$node->getId()]['cms_node_properties'] = [
                        'title' => 'Параметры модуля '.$node->getModule(),
                        'uri'   => $this->generateUrl('cms_admin_structure_node_properties', ['id' => $node->getId()]),
                    ];
                }

                if ($this->isGranted('ROLE_ADMIN') and $node->getIsUseEip()) {
                    $moduleResponse->setContent(
                        "\n<div class=\"cms-frontadmin-node\" id=\"__node_{$node->getId()}\" data-module=\"{$node->getModule()}\">\n".$moduleResponse->getContent()."\n</div>\n"
                    );
                }

                $nodesResponses[$node->getRegionName()]->{$node->getId()} = $moduleResponse;
            }
        }

        return $nodesResponses;
    }

    /**
     * Обработчик POST запросов.
     *
     * @param Request $request
     * @param string $slug
     *
     * @return RedirectResponse|Response
     *
     * @todo продумать!
     */
    public function postAction(Request $request, $slug)
    {
        // Получение $node_id
        $data = $request->request->all();
        $node_id = null;
        foreach ($data as $key => $value) {
            if ($key == '_node_id') {
                $node_id = $data['_node_id'];
                unset($data['_node_id']);
                break;
            }

            if (is_array($value) and array_key_exists('_node_id', $value)) {
                $node_id = $data[$key]['_node_id'];
                unset($data[$key]['_node_id']);
                break;
            }
        }

        foreach ($data as $key => $value) {
            $request->request->set($key, $value);
        }

        $node = $this->get('cms.node')->get($node_id);

        if (!$node instanceof Node or !$node->isActive()) {
            throw new AccessDeniedHttpException('Node is not active.');
        }

        // @todo сделать здесь проверку на права доступа, а также доступность ноды в запрошенной папке.

        // @todo сделать роутинги для POST запросов к нодам.
        return $this->forward("{$node->getId()}:{$node->getModule()}:post", ['slug' => $slug]);
    }

    /**
     * @param Node $_node
     *
     * @return Response
     */
    public function moduleNotConfiguredAction(Node $_node)
    {
        return new Response('Module "'.$_node->getModule().'" not yet configured. Node: '.$_node->getId().'<br />');
    }
}
